<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Estudio;
use App\Models\Paciente;
use App\Models\RitmoCardiaco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstudioController extends Controller
{
    public function history(Request $request)
    {
        $query = \App\Models\Imagen::with(['estudio.paciente', 'estudio.diagnostico', 'prediccion.ritmo']);

        // Filtros de búsqueda
        $filters = [
            'search' => $request->input('search', ''),
            'filter' => $request->input('filter', 'all'),
        ];

        if ($filters['search']) {
            $searchTerm = $filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('ruta', 'like', "%{$searchTerm}%")
                  ->orWhereHas('estudio.paciente', function($pq) use ($searchTerm) {
                      $pq->where('codigo_generado', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('prediccion.ritmo', function($pq) use ($searchTerm) {
                      $pq->where('nombre', 'like', "%{$searchTerm}%")
                         ->orWhere('label', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Aplicar filtros de estado
        if ($filters['filter'] === 'reviewed') {
            $query->whereHas('estudio.diagnostico');
        } elseif ($filters['filter'] === 'unreviewed') {
            $query->whereDoesntHave('estudio.diagnostico');
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Formatear items para Alpine.js
        $history->getCollection()->transform(function($img) {
            $prediccion = $img->prediccion;
            $diagnostico = $img->estudio->diagnostico;
            $isNormalRhythm = static fn ($label) => mb_strtoupper(trim((string) $label), 'UTF-8') === 'NORM';

            return [
                'id' => $img->imagen_id,
                'study_id' => $img->estudio_id,
                'report_url' => route('reportes.estudio.pdf', ['id' => $img->estudio_id]),
                'ecg_url' => route('imagenes.ecg.ver', ['imagen' => $img->imagen_id]),
                'ecg_download_url' => route('imagenes.ecg.download', ['imagen' => $img->imagen_id]),
                'filename' => basename($img->ruta),
                'patient' => $img->estudio->paciente->codigo_generado ?? 'N/A',
                'date' => $img->created_at->format('d/m/Y'),
                'time' => $img->created_at->format('H:i'),
                'rhythm' => $prediccion->ritmo->nombre ?? 'N/A',
                'probability' => $prediccion ? round($prediccion->probabilidad * 100, 1) : 0,
                'result' => $isNormalRhythm($prediccion->ritmo->label ?? '') ? 'normal' : 'arritmia',
                'doctor_result' => $diagnostico
                    ? ($isNormalRhythm($diagnostico->ritmoCardiaco?->label ?? '') ? 'normal' : 'arritmia')
                    : null,
                'doctor_ritmo_id' => $diagnostico?->ritmo_id,
                'doctor_label' => $diagnostico->ritmoCardiaco?->nombre ?? null,
                'doctor_notes' => $diagnostico->observacion ?? '',
            ];
        });

        // Estadísticas para las tarjetas superiores
        $stats = [
            'total' => \App\Models\Imagen::count(),
            'normales' => \App\Models\Prediccion::whereHas('ritmo', function($q){ $q->where('label', 'NORM'); })->count(),
            'arritmias' => \App\Models\Prediccion::whereHas('ritmo', function($q){ $q->where('label', '!=', 'NORM'); })->count(),
            'revisados' => \App\Models\Diagnostico::count(),
        ];

        $roleName = mb_strtoupper((string) session('user.role'), 'UTF-8');
        $canReview = in_array($roleName, ['ADMINISTRADOR', 'CARDIOLOGO'], true);
        $ritmos = RitmoCardiaco::where('estado', 1)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($ritmo) => [
                'ritmo_id' => $ritmo->ritmo_id,
                'nombre' => $ritmo->nombre,
                'label' => $ritmo->label,
            ]);

        return view('historial.index', compact('history', 'stats', 'filters', 'canReview', 'ritmos'));
    }

    public function index(Request $request)
    {
        $query = Estudio::with(['paciente', 'registradoPor']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('paciente', function ($q) use ($searchTerm) {
                $q->where('codigo_generado', 'like', '%' . $searchTerm . '%');
            });
        }

        $estudios = $query->orderBy('estudio_id', 'desc')->paginate(10);
        $pacientes = Paciente::where('estado', 1)->get();

        $estudios->appends(['search' => $request->input('search')]);

        return view('estudios.index', compact('estudios', 'pacientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id'  => 'required|exists:pacientes,paciente_id',
            'edad'         => 'nullable|integer|min:0|max:120',
            'observaciones' => 'nullable',
        ]);

        $estudio = new Estudio();
        $estudio->paciente_id    = $request->paciente_id;
        $estudio->registrado_por = Auth::id();
        $estudio->edad           = $request->edad ?? null;
        $estudio->observaciones  = $request->observaciones ?? null;
        $estudio->estado         = $request->has('estado') ? 1 : 0;
        $estudio->save();

        return redirect()->route('estudios.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de registrar correctamente el estudio del paciente',
            'alert'   => 'success',
            'data'    => $estudio->paciente->codigo_generado,
        ]);
    }

    public function update(Request $request, Estudio $estudio)
    {
        $request->validate([
            'edad'          => 'nullable|integer|min:0|max:120',
            'observaciones' => 'nullable',
        ]);

        $estudio->edad          = $request->edad ?? null;
        $estudio->observaciones = $request->observaciones ?? null;
        $estudio->estado        = $request->has('estado') ? 1 : 0;
        $estudio->save();

        return redirect()->route('estudios.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el estudio del paciente',
            'alert'   => 'success',
            'data'    => $estudio->paciente->codigo_generado,
        ]);
    }

    public function destroy(Estudio $estudio)
    {
        $estudio->estado = 0;
        $estudio->save();

        return redirect()->route('estudios.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar el estudio',
            'alert'   => 'danger',
            'data'    => $estudio->paciente->codigo_generado,
        ]);
    }

    public function activar(Estudio $estudio)
    {
        $estudio->estado = 1;
        $estudio->save();

        return redirect()->route('estudios.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar el estudio',
            'alert'   => 'primary',
            'data'    => $estudio->paciente->codigo_generado,
        ]);
    }

    public function showObservacion(Estudio $estudio)
    {
        return response()->json([
            'observaciones' => $estudio->observaciones ?? 'No hay observaciones registradas para este estudio.'
        ]);
    }
}

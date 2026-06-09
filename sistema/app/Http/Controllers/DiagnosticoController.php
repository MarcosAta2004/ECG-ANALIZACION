<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Diagnostico;
use App\Models\Estudio;
use App\Models\RitmoCardiaco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiagnosticoController extends Controller
{
    public function index(Request $request)
    {
        $query = Diagnostico::with([
            'estudio.paciente',
            'ritmoCardiaco.grupoCardiaco',
            'ritmoCardiaco.nivelGravedad',
            'ritmoCardiaco.clasificacionArritmia',
            'registrador',
        ]);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('estudio.paciente', function ($q) use ($searchTerm) {
                $q->where('codigo_generado', 'like', '%' . $searchTerm . '%');
            });
        }

        $diagnosticos = $query->orderBy('diagnostico_id', 'desc')->paginate(10);

        // Solo estudios activos sin diagnóstico registrado
        $estudios = Estudio::with('paciente')
            ->where('estado', 1)
            ->doesntHave('diagnostico')
            ->get();

        $ritmos = RitmoCardiaco::where('estado', 1)->get();

        $diagnosticos->appends(['search' => $request->input('search')]);

        return view('diagnosticos.index', compact('diagnosticos', 'estudios', 'ritmos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'estudio_id'   => 'required|exists:estudios,estudio_id|unique:diagnosticos,estudio_id',
            'ritmo_id'     => 'required|exists:ritmos_cardiacos,ritmo_id',
            'concordancia' => 'nullable|boolean',
            'observacion'  => 'nullable|string',
        ]);

        $diagnostico = new Diagnostico();
        $diagnostico->estudio_id      = $request->estudio_id;
        $diagnostico->ritmo_id        = $request->ritmo_id;
        $diagnostico->registrado_por  = Auth::id();
        $diagnostico->concordancia    = $request->concordancia ?? null;
        $diagnostico->observacion     = $request->observacion ?? null;
        $diagnostico->fecha_revision  = now();
        $diagnostico->save();

        return redirect()->route('diagnosticos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se registró correctamente el diagnóstico del estudio',
            'alert'   => 'success',
            'data'    => $diagnostico->estudio->paciente->codigo_generado,
        ]);
    }

    public function update(Request $request, Diagnostico $diagnostico)
    {
        $request->validate([
            'ritmo_id'     => 'required|exists:ritmos_cardiacos,ritmo_id',
            'concordancia' => 'nullable|boolean',
            'observacion'  => 'nullable|string',
        ]);

        $diagnostico->ritmo_id       = $request->ritmo_id;
        $diagnostico->registrado_por = Auth::id();
        $diagnostico->concordancia   = $request->concordancia ?? null;
        $diagnostico->observacion    = $request->observacion ?? null;
        $diagnostico->fecha_revision = now();
        $diagnostico->save();

        return redirect()->route('diagnosticos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se actualizó correctamente el diagnóstico del estudio',
            'alert'   => 'success',
            'data'    => $diagnostico->estudio->paciente->codigo_generado,
        ]);
    }

    public function destroy(Diagnostico $diagnostico)
    {
        $diagnostico->estado = 0;
        $diagnostico->save();

        return redirect()->route('diagnosticos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se deshabilitó el diagnóstico',
            'alert'   => 'danger',
            'data'    => $diagnostico->estudio->paciente->codigo_generado,
        ]);
    }

    public function activar(Diagnostico $diagnostico)
    {
        $diagnostico->estado = 1;
        $diagnostico->save();

        return redirect()->route('diagnosticos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se habilitó el diagnóstico',
            'alert'   => 'primary',
            'data'    => $diagnostico->estudio->paciente->codigo_generado,
        ]);
    }

    /**
     * verEcgEstudio — AJAX: devuelve datos del estudio + URL del ECG para abrir el visor en el modal.
     * GET /clinico/diagnosticos/estudio/{estudio}/info
     */
    public function verEcgEstudio(\App\Models\Estudio $estudio)
    {
        $estudio->loadMissing([
            'paciente',
            'imagen.prediccion.ritmoCardiaco',
        ]);

        $imagen     = $estudio->imagen;
        $prediccion = $imagen?->prediccion;
        $ritmoIa    = $prediccion?->ritmoCardiaco;

        // URL pública del ECG para verlo en el modal
        $ecgUrl = $imagen
            ? route('imagenes.ecg.ver', $imagen->imagen_id)
            : null;

        return response()->json([
            'estudio_id'     => $estudio->estudio_id,
            'paciente'       => $estudio->paciente?->codigo_generado,
            'tiene_ecg'      => (bool) $imagen,
            'ecg_url'        => $ecgUrl,
            'ecg_formato'    => $imagen?->formato,
            'tiene_ia'       => (bool) $prediccion,
            'ia_ritmo_id'    => $ritmoIa?->ritmo_id,
            'ia_ritmo_label' => $ritmoIa?->label,
            'ia_ritmo_nombre'=> $ritmoIa?->nombre,
            'ia_probabilidad'=> $prediccion ? round($prediccion->probabilidad * 100, 2) : null,
        ]);
    }

    /**
     * Guarda una valoración médica rápida desde el Historial (AJAX).
     * concordancia = true  si el ritmo del cardiólogo coincide con el de la IA
     * concordancia = false si discrepa
     */
    public function review(Request $request, \App\Models\Imagen $imagen)
    {
        $validated = $request->validate([
            'doctor_result'   => 'required|in:normal,arritmia',
            'doctor_ritmo_id' => 'nullable|exists:ritmos_cardiacos,ritmo_id',
            'doctor_notes'    => 'nullable|string',
        ]);

        $imagen->loadMissing('prediccion.ritmo');
        $estudio = $imagen->estudio;

        $ritmo = $validated['doctor_result'] === 'normal'
            ? (\App\Models\RitmoCardiaco::where('label', 'NORM')->first() ?? \App\Models\RitmoCardiaco::firstOrFail())
            : \App\Models\RitmoCardiaco::findOrFail($validated['doctor_ritmo_id']);

        $ritmoIa = $imagen->prediccion?->ritmo;

        // Concordancia: booleano estricto (1 / 0)
        $concordancia = ($ritmoIa && $ritmo)
            ? (mb_strtoupper(trim((string) $ritmoIa->label), 'UTF-8') === mb_strtoupper(trim((string) $ritmo->label), 'UTF-8'))
            : null;

        $diagnostico = \App\Models\Diagnostico::updateOrCreate(
            ['estudio_id' => $estudio->estudio_id],
            [
                'ritmo_id'       => $ritmo->ritmo_id,
                'registrado_por' => Auth::id(),
                'concordancia'   => $concordancia,
                'observacion'    => $validated['doctor_notes'],
                'fecha_revision' => now(),
            ]
        );

        return response()->json([
            'success'     => true,
            'message'     => 'Valoración guardada',
            'reviewed_at' => $diagnostico->fecha_revision->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Elimina una valoración médica desde el Historial (AJAX).
     */
    public function deleteReview(\App\Models\Imagen $imagen)
    {
        $estudio = $imagen->estudio;
        if ($estudio->diagnostico) {
            $estudio->diagnostico->delete();
        }

        return response()->json(['success' => true, 'message' => 'Valoración eliminada']);
    }
}

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
        $query = Diagnostico::with(['estudio.paciente', 'ritmoCardiaco', 'medico']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('estudio.paciente', function ($q) use ($searchTerm) {
                $q->where('codigo_generado', 'like', '%' . $searchTerm . '%');
            });
        }

        $diagnosticos = $query->orderBy('diagnostico_id', 'desc')->paginate(10);

        // Solo estudios activos que aún no tienen diagnóstico registrado
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
            'descripcion'  => 'nullable|max:200',
            'observacion'  => 'nullable',
        ]);

        $diagnostico = new Diagnostico();
        $diagnostico->estudio_id    = $request->estudio_id;
        $diagnostico->ritmo_id      = $request->ritmo_id;
        $diagnostico->medico_id     = Auth::id();
        $diagnostico->concordancia  = $request->concordancia ?? null;
        $diagnostico->descripcion   = $request->descripcion ?? null;
        $diagnostico->observacion   = $request->observacion ?? null;
        $diagnostico->fecha_revision = now();
        $diagnostico->save();

        return redirect()->route('diagnosticos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de registrar correctamente el diagnóstico del estudio',
            'alert'   => 'success',
            'data'    => $diagnostico->estudio->paciente->codigo_generado,
        ]);
    }

    public function update(Request $request, Diagnostico $diagnostico)
    {
        $request->validate([
            'ritmo_id'     => 'required|exists:ritmos_cardiacos,ritmo_id',
            'concordancia' => 'nullable|boolean',
            'descripcion'  => 'nullable|max:200',
            'observacion'  => 'nullable',
        ]);

        $diagnostico->ritmo_id      = $request->ritmo_id;
        $diagnostico->concordancia  = $request->concordancia ?? null;
        $diagnostico->descripcion   = $request->descripcion ?? null;
        $diagnostico->observacion   = $request->observacion ?? null;
        $diagnostico->fecha_revision = now();
        $diagnostico->save();

        return redirect()->route('diagnosticos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el diagnóstico del estudio',
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
            'message' => 'Se acaba de deshabilitar el diagnóstico',
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
            'message' => 'Se acaba de habilitar el diagnóstico',
            'alert'   => 'primary',
            'data'    => $diagnostico->estudio->paciente->codigo_generado,
        ]);
    }
}
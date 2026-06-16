<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\PrefijoPaciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Paciente::with(['prefijoPaciente', 'registradoPor'])
            ->withCount('estudios');

        if ($request->filled('search')) {
            $searchTerm = trim($request->input('search'));
            $sexoMap = ['masculino' => 'M', 'femenino' => 'F', 'm' => 'M', 'f' => 'F'];
            $sexoBusqueda = $sexoMap[strtolower($searchTerm)] ?? null;

            $query->where(function ($q) use ($searchTerm, $sexoBusqueda) {
                $q->where('codigo_generado', 'like', '%' . $searchTerm . '%');
                if ($sexoBusqueda) {
                    $q->orWhere('sexo', $sexoBusqueda);
                }
            });
        }

        $pacientes = $query->orderBy('paciente_id', 'desc')->paginate(10);
        $prefijos  = PrefijoPaciente::where('estado', 1)->get();

        $pacientes->appends(['search' => $request->input('search')]);

        return view('pacientes.index', compact('pacientes', 'prefijos'));
    }

    public function show(Paciente $paciente)
    {
        $paciente->loadCount('estudios');
        return view('pacientes.show', compact('paciente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prefijo_id'       => 'required|exists:prefijos_paciente,prefijo_id',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo'             => 'nullable|in:M,F',
            'peso'             => 'nullable|numeric|min:0|max:300',
        ]);

        $prefijo  = PrefijoPaciente::find($request->prefijo_id);
        $hoy      = date('Y-m-d');
        $ultimo   = Paciente::whereDate('created_at', $hoy)->count() + 1;
        $codigo   = strtoupper($prefijo->nombre) . '_' . date('Ymd') . '_' . str_pad($ultimo, 3, '0', STR_PAD_LEFT);

        $paciente = new Paciente();
        $paciente->prefijo_id       = $request->prefijo_id;
        $paciente->codigo_generado  = $codigo;
        $paciente->fecha_nacimiento = $request->fecha_nacimiento ?? null;
        $paciente->sexo             = $request->sexo ?? null;
        $paciente->peso             = $request->peso ?? null;
        $paciente->registrado_por   = Auth::id();
        $paciente->estado           = $request->has('estado') ? 1 : 0;
        $paciente->save();

        return redirect()->route('pacientes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de registrar correctamente el paciente',
            'alert'   => 'success',
            'data'    => $paciente->codigo_generado,
        ]);
    }

    public function update(Request $request, Paciente $paciente)
    {
        $request->validate([
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo'             => 'nullable|in:M,F',
            'peso'             => 'nullable|numeric|min:0|max:300',
        ]);

        $paciente->fecha_nacimiento = $request->fecha_nacimiento ?? null;
        $paciente->sexo             = $request->sexo ?? null;
        $paciente->peso             = $request->peso ?? null;
        $paciente->estado           = $request->has('estado') ? 1 : 0;
        $paciente->save();

        return redirect()->route('pacientes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el paciente',
            'alert'   => 'success',
            'data'    => $paciente->codigo_generado,
        ]);
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->estado = 0;
        $paciente->save();

        return redirect()->route('pacientes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar el paciente',
            'alert'   => 'danger',
            'data'    => $paciente->codigo_generado,
        ]);
    }

    public function activar(Paciente $paciente)
    {
        $paciente->estado = 1;
        $paciente->save();

        return redirect()->route('pacientes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar el paciente',
            'alert'   => 'primary',
            'data'    => $paciente->codigo_generado,
        ]);
    }
}

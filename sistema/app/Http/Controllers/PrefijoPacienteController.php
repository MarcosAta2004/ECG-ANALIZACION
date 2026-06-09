<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PrefijoPaciente;
use Illuminate\Http\Request;

class PrefijoPacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = PrefijoPaciente::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('nombre', 'like', '%' . $searchTerm . '%');
        }

        $prefijos = $query->orderBy('prefijo_id', 'desc')->paginate(10);
        $prefijos->appends(['search' => $request->input('search')]);

        return view('mantenimientos.prefijos_paciente.index', compact('prefijos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|max:100|unique:prefijos_paciente,nombre',
            'descripcion' => 'nullable',
        ]);

        $prefijo = new PrefijoPaciente();
        $prefijo->nombre      = strtoupper($request->nombre);
        $prefijo->descripcion = $request->descripcion ?? null;
        $prefijo->save();

        return redirect()->route('prefijos-paciente.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de guardar correctamente el registro de',
            'alert'   => 'success',
            'data'    => $prefijo->nombre,
        ]);
    }

    public function update(Request $request, PrefijoPaciente $prefijoPaciente)
    {
        $request->validate([
            'nombre'      => 'required|max:100|unique:prefijos_paciente,nombre,' . $prefijoPaciente->prefijo_id . ',prefijo_id',
            'descripcion' => 'nullable',
        ]);

        $prefijoPaciente->nombre      = strtoupper($request->nombre);
        $prefijoPaciente->descripcion = $request->descripcion ?? null;
        $prefijoPaciente->save();

        return redirect()->route('prefijos-paciente.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el registro de',
            'alert'   => 'success',
            'data'    => $prefijoPaciente->nombre,
        ]);
    }

    public function destroy(PrefijoPaciente $prefijoPaciente)
    {
        $prefijoPaciente->estado = 0;
        $prefijoPaciente->save();

        return redirect()->route('prefijos-paciente.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar el registro de',
            'alert'   => 'danger',
            'data'    => $prefijoPaciente->nombre,
        ]);
    }

    public function activar(PrefijoPaciente $prefijoPaciente)
    {
        $prefijoPaciente->estado = 1;
        $prefijoPaciente->save();

        return redirect()->route('prefijos-paciente.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar el registro de',
            'alert'   => 'primary',
            'data'    => $prefijoPaciente->nombre,
        ]);
    }
}

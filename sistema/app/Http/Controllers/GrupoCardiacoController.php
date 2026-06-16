<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GrupoCardiaco;
use Illuminate\Http\Request;

class GrupoCardiacoController extends Controller
{
    public function index(Request $request)
    {
        $query = GrupoCardiaco::query();

        if ($request->filled('search')) {
            $searchTerm = trim($request->input('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('descripcion', 'like', '%' . $searchTerm . '%');
            });
        }

        $grupos = $query->orderBy('grupo_id', 'desc')->paginate(10);
        $grupos->appends(['search' => $request->input('search')]);

        return view('mantenimientos.grupos_cardiacos.index', compact('grupos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|max:100|unique:grupos_cardiacos,nombre',
            'descripcion' => 'nullable',
        ]);

        $grupo = new GrupoCardiaco();
        $grupo->nombre      = strtoupper($request->nombre);
        $grupo->descripcion = $request->descripcion ?? null;
        $grupo->estado = $request->has('estado') ? 1 : 0;
        $grupo->save();

        return redirect()->route('grupos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de guardar correctamente el registro de {$grupo->nombre}",
            'alert'   => 'success',
            'data'    => $grupo->nombre,
        ]);
    }

    public function update(Request $request, GrupoCardiaco $grupoCardiaco)
    {
        $request->validate([
            'nombre'      => 'required|max:100|unique:grupos_cardiacos,nombre,' . $grupoCardiaco->grupo_id . ',grupo_id',
            'descripcion' => 'nullable',
        ]);

        $grupoCardiaco->nombre      = strtoupper($request->nombre);
        $grupoCardiaco->descripcion = $request->descripcion ?? null;
        $grupoCardiaco->estado = $request->has('estado') ? 1 : 0;
        $grupoCardiaco->save();

        return redirect()->route('grupos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de actualizar correctamente el registro de {$grupoCardiaco->nombre}",
            'alert'   => 'success',
            'data'    => $grupoCardiaco->nombre,
        ]);
    }

    public function destroy(GrupoCardiaco $grupoCardiaco)
    {
        $grupoCardiaco->estado = 0;
        $grupoCardiaco->save();

        return redirect()->route('grupos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de deshabilitar el registro de {$grupoCardiaco->nombre}",
            'alert'   => 'danger',
            'data'    => $grupoCardiaco->nombre,
        ]);
    }

    public function activar(GrupoCardiaco $grupoCardiaco)
    {
        $grupoCardiaco->estado = 1;
        $grupoCardiaco->save();

        return redirect()->route('grupos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de habilitar el registro de {$grupoCardiaco->nombre}",
            'alert'   => 'primary',
            'data'    => $grupoCardiaco->nombre,
        ]);
    }
}

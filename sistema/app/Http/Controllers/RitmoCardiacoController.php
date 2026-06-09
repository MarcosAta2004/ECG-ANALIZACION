<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RitmoCardiaco;
use App\Models\GrupoCardiaco;
use App\Models\NivelGravedad;
use App\Models\ClasificacionArritmia;
use Illuminate\Http\Request;

class RitmoCardiacoController extends Controller
{
    public function index(Request $request)
    {
        $query = RitmoCardiaco::with(['grupoCardiaco', 'nivelGravedad', 'clasificacionArritmia']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('label', 'like', '%' . $searchTerm . '%');
            });
        }

        $ritmos           = $query->orderBy('ritmo_id', 'desc')->paginate(10);
        $grupos           = GrupoCardiaco::where('estado', 1)->get();
        $niveles          = NivelGravedad::where('estado', 1)->get();
        $clasificaciones  = ClasificacionArritmia::where('estado', 1)->get();

        $ritmos->appends(['search' => $request->input('search')]);

        return view('ritmos_cardiacos.index', compact('ritmos', 'grupos', 'niveles', 'clasificaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'grupo_id'         => 'required|exists:grupos_cardiacos,grupo_id',
            'nivel_id'         => 'required|exists:niveles_gravedad,nivel_id',
            'clasificacion_id' => 'required|exists:clasificaciones_arritmia,clasificacion_id',
            'label'            => 'required|max:50|unique:ritmos_cardiacos,label',
            'nombre'           => 'required|max:150',
            'descripcion'      => 'nullable',
        ]);

        $ritmo = new RitmoCardiaco();
        $ritmo->grupo_id         = $request->grupo_id;
        $ritmo->nivel_id         = $request->nivel_id;
        $ritmo->clasificacion_id = $request->clasificacion_id;
        $ritmo->label            = strtoupper($request->label);
        $ritmo->nombre           = strtoupper($request->nombre);
        $ritmo->descripcion      = $request->descripcion ?? null;
        $ritmo->estado           = $request->has('estado') ? 1 : 0;
        $ritmo->save();

        return redirect()->route('ritmos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de guardar correctamente el registro de',
            'alert'   => 'success',
            'data'    => $ritmo->nombre,
        ]);
    }

    public function update(Request $request, RitmoCardiaco $ritmoCardiaco)
    {
        $request->validate([
            'grupo_id'         => 'required|exists:grupos_cardiacos,grupo_id',
            'nivel_id'         => 'required|exists:niveles_gravedad,nivel_id',
            'clasificacion_id' => 'required|exists:clasificaciones_arritmia,clasificacion_id',
            'label'            => 'required|max:50|unique:ritmos_cardiacos,label,' . $ritmoCardiaco->ritmo_id . ',ritmo_id',
            'nombre'           => 'required|max:150',
            'descripcion'      => 'nullable',
        ]);

        $ritmoCardiaco->grupo_id         = $request->grupo_id;
        $ritmoCardiaco->nivel_id         = $request->nivel_id;
        $ritmoCardiaco->clasificacion_id = $request->clasificacion_id;
        $ritmoCardiaco->label            = strtoupper($request->label);
        $ritmoCardiaco->nombre           = strtoupper($request->nombre);
        $ritmoCardiaco->descripcion      = $request->descripcion ?? null;
        $ritmoCardiaco->estado           = $request->has('estado') ? 1 : 0;
        $ritmoCardiaco->save();

        return redirect()->route('ritmos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el registro de',
            'alert'   => 'success',
            'data'    => $ritmoCardiaco->nombre,
        ]);
    }

    public function destroy(RitmoCardiaco $ritmoCardiaco)
    {
        $ritmoCardiaco->estado = 0;
        $ritmoCardiaco->save();

        return redirect()->route('ritmos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar el registro de',
            'alert'   => 'danger',
            'data'    => $ritmoCardiaco->nombre,
        ]);
    }

    public function activar(RitmoCardiaco $ritmoCardiaco)
    {
        $ritmoCardiaco->estado = 1;
        $ritmoCardiaco->save();

        return redirect()->route('ritmos-cardiacos.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar el registro de',
            'alert'   => 'primary',
            'data'    => $ritmoCardiaco->nombre,
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClasificacionArritmia;
use Illuminate\Http\Request;

class ClasificacionArritmiaController extends Controller
{
    public function index(Request $request)
    {
        $query = ClasificacionArritmia::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('nombre', 'like', '%' . $searchTerm . '%');
        }

        $clasificaciones = $query->orderBy('clasificacion_id', 'desc')->paginate(10);
        $clasificaciones->appends(['search' => $request->input('search')]);

        return view('mantenimientos.clasificaciones_arritmia.index', compact('clasificaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:clasificaciones_arritmia,nombre',
        ]);

        $clasificacion = new ClasificacionArritmia();
        $clasificacion->nombre = strtoupper($request->nombre);
        $clasificacion->estado = $request->has('estado') ? 1 : 0;
        $clasificacion->save();

        return redirect()->route('clasificaciones-arritmia.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de guardar correctamente el registro de {$clasificacion->nombre}",
            'alert'   => 'success',
            'data'    => $clasificacion->nombre,
        ]);
    }

    public function update(Request $request, ClasificacionArritmia $clasificacionArritmia)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:clasificaciones_arritmia,nombre,' . $clasificacionArritmia->clasificacion_id . ',clasificacion_id',
        ]);

        $clasificacionArritmia->nombre = strtoupper($request->nombre);
        $clasificacionArritmia->estado = $request->has('estado') ? 1 : 0;
        $clasificacionArritmia->save();

        return redirect()->route('clasificaciones-arritmia.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de actualizar correctamente el registro de {$clasificacionArritmia->nombre}",
            'alert'   => 'success',
            'data'    => $clasificacionArritmia->nombre,
        ]);
    }

    public function destroy(ClasificacionArritmia $clasificacionArritmia)
    {
        $clasificacionArritmia->estado = 0;
        $clasificacionArritmia->save();

        return redirect()->route('clasificaciones-arritmia.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de deshabilitar el registro de {$clasificacionArritmia->nombre}",
            'alert'   => 'danger',
            'data'    => $clasificacionArritmia->nombre,
        ]);
    }

    public function activar(ClasificacionArritmia $clasificacionArritmia)
    {
        $clasificacionArritmia->estado = 1;
        $clasificacionArritmia->save();

        return redirect()->route('clasificaciones-arritmia.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de habilitar el registro de {$clasificacionArritmia->nombre}",
            'alert'   => 'primary',
            'data'    => $clasificacionArritmia->nombre,
        ]);
    }
}

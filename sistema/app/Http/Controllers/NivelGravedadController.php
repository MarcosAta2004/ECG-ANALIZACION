<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NivelGravedad;
use Illuminate\Http\Request;

class NivelGravedadController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:niveles-gravedad.index')->only('index');
        $this->middleware('can:niveles-gravedad.update')->only('update');
        $this->middleware('can:niveles-gravedad.store')->only('store');
        $this->middleware('can:niveles-gravedad.destroy')->only('destroy');
        $this->middleware('can:niveles-gravedad.activar')->only('activar');
    }

    public function index(Request $request)
    {
        $query = NivelGravedad::query();

        if ($request->filled('search')) {
            $searchTerm = trim($request->input('search'));
            $query->where('nombre', 'like', '%' . $searchTerm . '%');
        }

        $niveles = $query->orderBy('nivel_id', 'desc')->paginate(10);
        $niveles->appends(['search' => $request->input('search')]);

        return view('mantenimientos.niveles_gravedad.index', compact('niveles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:niveles_gravedad,nombre',
        ]);

        $nivel         = new NivelGravedad();
        $nivel->nombre = strtoupper($request->nombre);
        $nivel->estado = $request->has('estado') ? 1 : 0;
        $nivel->save();

        return redirect()->route('niveles-gravedad.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de guardar correctamente el registro de {$nivel->nombre}",
            'alert' => 'success',
            'data'  => $nivel->nombre,
        ]);
    }

    public function update(Request $request, NivelGravedad $nivelGravedad)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:niveles_gravedad,nombre,' . $nivelGravedad->nivel_id . ',nivel_id',
        ]);

        $nivelGravedad->nombre = strtoupper($request->nombre);
        $nivelGravedad->estado = $request->has('estado') ? 1 : 0;
        $nivelGravedad->save();

        return redirect()->route('niveles-gravedad.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de actualizar correctamente el registro de {$nivelGravedad->nombre}",
            'alert' => 'success',
            'data'  => $nivelGravedad->nombre,
        ]);
    }

    public function destroy(NivelGravedad $nivelGravedad)
    {
        $nivelGravedad->estado = 0;
        $nivelGravedad->save();

        return redirect()->route('niveles-gravedad.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de deshabilitar el registro de {$nivelGravedad->nombre}",
            'alert' => 'danger',
            'data'  => $nivelGravedad->nombre,
        ]);
    }

    public function activar(NivelGravedad $nivelGravedad)
    {
        $nivelGravedad->estado = 1;
        $nivelGravedad->save();

        return redirect()->route('niveles-gravedad.index')->with([
            'ok'      => 'enabled',
            'message' => "Se acaba de habilitar el registro de {$nivelGravedad->nombre}",
            'alert' => 'primary',
            'data'  => $nivelGravedad->nombre,
        ]);
    }
}

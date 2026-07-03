<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prediccion;
use Illuminate\Http\Request;

class PrediccionController extends Controller
{
    // La predicción la genera exclusivamente el modelo CNN-LSTM vía FastAPI.
    // Laravel solo consulta y gestiona el estado — nunca crea ni modifica predicciones.

    public function index(Request $request)
    {
        // Carga eager de ritmoCardiaco con sus relaciones maestras:
        // grupoCardiaco → nombre del grupo (Sinusal, Ectopias, etc.)
        // nivelGravedad → nivel de gravedad (Baja, Moderada, Alta)
        // clasificacionArritmia → clasificación (NORMAL / ARRITMIA)
        $query = Prediccion::with([
            'imagen.estudio.paciente',
            'ritmoCardiaco.grupoCardiaco',
            'ritmoCardiaco.nivelGravedad',
            'ritmoCardiaco.clasificacionArritmia',
        ]);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('imagen.estudio.paciente', function ($q) use ($searchTerm) {
                $q->where('codigo_generado', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtro opcional por ritmo predicho
        if ($request->filled('ritmo_id')) {
            $query->where('ritmo_id', $request->ritmo_id);
        }

        $predicciones = $query->orderBy('prediccion_id', 'desc')->paginate(10);
        $predicciones->appends($request->only(['search', 'ritmo_id']));

        return view('predicciones.index', compact('predicciones'));
    }

    public function show($codigoPaciente)
    {
        return redirect()->route('predicciones.index', ['search' => $codigoPaciente]);
    }

    public function destroy(Prediccion $prediccion)
    {
        $prediccion->estado = 0;
        $prediccion->save();

        return redirect()->route('predicciones.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar la predicción del estudio',
            'alert'   => 'danger',
            'data'    => $prediccion->imagen->estudio->paciente->codigo_generado,
        ]);
    }

    public function activar(Prediccion $prediccion)
    {
        $prediccion->estado = 1;
        $prediccion->save();

        return redirect()->route('predicciones.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar la predicción del estudio',
            'alert'   => 'primary',
            'data'    => $prediccion->imagen->estudio->paciente->codigo_generado,
        ]);
    }
}
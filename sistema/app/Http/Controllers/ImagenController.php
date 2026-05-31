<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Imagen;
use App\Models\Estudio;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use App\Services\ServicioAuditoria;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImagenController extends Controller
{
    public function index(Request $request)
    {
        return view('subir-ecg');
    }

    public function store(Request $request)
    {
        $request->validate([
            'estudio_id' => 'required|exists:estudios,estudio_id|unique:imagenes,estudio_id',
            'archivo'    => 'required|file|mimes:png,jpg,jpeg,pdf|max:10240',
        ]);

        $archivo   = $request->file('archivo');
        $formato   = $archivo->getClientOriginalExtension();
        $uuid      = Str::uuid();
        $nombreUUID = $uuid . '.' . $formato;

        // Guardado con nombre UUID para anonimizar el archivo en disco
        $ruta = $archivo->storeAs('ecg', $nombreUUID, 'public');

        // Hash SHA-256 para verificar integridad
        $hash = hash_file('sha256', $archivo->getRealPath());

        // Dimensiones solo para imágenes
        $resolucion = null;
        if (in_array($formato, ['png', 'jpg', 'jpeg'])) {
            [$ancho, $alto] = getimagesize($archivo->getRealPath());
            $resolucion = $ancho . 'x' . $alto;
        }

        $imagen = new Imagen();
        $imagen->estudio_id  = $request->estudio_id;
        $imagen->ruta        = $ruta;
        $imagen->formato     = $formato;
        $imagen->resolucion  = $resolucion;
        $imagen->tamano_kb   = (int) round($archivo->getSize() / 1024);
        $imagen->hash        = $hash;
        $imagen->save();

        return redirect()->route('upload')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de cargar correctamente la imagen ECG del estudio',
            'alert'   => 'success',
            'data'    => $imagen->estudio->paciente->codigo_generado,
        ]);
    }

    public function update(Request $request, Imagen $imagen)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:png,jpg,jpeg,pdf|max:10240',
        ]);

        // Eliminar archivo anterior del disco
        Storage::disk('public')->delete($imagen->ruta);

        $archivo    = $request->file('archivo');
        $formato    = $archivo->getClientOriginalExtension();
        $uuid       = Str::uuid();
        $nombreUUID = $uuid . '.' . $formato;

        $ruta = $archivo->storeAs('ecg', $nombreUUID, 'public');
        $hash = hash_file('sha256', $archivo->getRealPath());

        $resolucion = null;
        if (in_array($formato, ['png', 'jpg', 'jpeg'])) {
            [$ancho, $alto] = getimagesize($archivo->getRealPath());
            $resolucion = $ancho . 'x' . $alto;
        }

        $imagen->ruta       = $ruta;
        $imagen->formato    = $formato;
        $imagen->resolucion = $resolucion;
        $imagen->tamano_kb  = (int) round($archivo->getSize() / 1024);
        $imagen->hash       = $hash;
        $imagen->save();

        return redirect()->route('upload')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de reemplazar correctamente la imagen ECG del estudio',
            'alert'   => 'success',
            'data'    => $imagen->estudio->paciente->codigo_generado,
        ]);
    }

    public function destroy(Imagen $imagen)
    {
        $imagen->estado = 0;
        $imagen->save();

        return redirect()->route('upload')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar la imagen ECG',
            'alert'   => 'danger',
            'data'    => $imagen->estudio->paciente->codigo_generado,
        ]);
    }

    public function activar(Imagen $imagen)
    {
        $imagen->estado = 1;
        $imagen->save();

        return redirect()->route('upload')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar la imagen ECG',
            'alert'   => 'primary',
            'data'    => $imagen->estudio->paciente->codigo_generado,
        ]);
    }

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,pdf,csv,txt|max:20480',
            'age' => 'nullable|numeric',
            'sex' => 'nullable|string',
            'weight' => 'nullable|numeric',
        ]);

        $file = $validated['file'];
        $apiUrl = env('ECG_API_URL', 'http://localhost:8001');
        $client = new Client(['timeout' => 120]);

        try {
            $response = $client->post("{$apiUrl}/predict", [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                    ['name' => 'age', 'contents' => $validated['age'] ?? 0],
                    ['name' => 'sex', 'contents' => $validated['sex'] ?? ''],
                    ['name' => 'weight', 'contents' => $validated['weight'] ?? 0],
                ],
            ]);

            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (RequestException $e) {
            $msg = 'Error al conectar con el servidor de análisis.';
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                $msg = $body['detail'] ?? $msg;
            }

            return response()->json(['error' => $msg], 502);
        }
    }

    /**
     * Envía la imagen a FastAPI y guarda el resultado en predicciones.
     * Se llama después de que la imagen ya fue cargada en el store.
     */
    public function analizar(Imagen $imagen)
    {
        // Verificar que la imagen no tenga ya una predicción registrada
        if ($imagen->prediccion) {
            return response()->json([
                'error' => 'Esta imagen ya tiene una predicción registrada.',
            ], 409);
        }

        $estudio  = $imagen->estudio;
        $paciente = $estudio->paciente;

        $apiUrl = env('ECG_API_URL', 'http://localhost:8001');
        $client = new Client(['timeout' => 120]);

        try {
            $rutaAbsoluta = Storage::disk('public')->path($imagen->ruta);

            $inicio = now();

            $response = $client->post("{$apiUrl}/predict", [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($rutaAbsoluta, 'r'),
                        'filename' => basename($imagen->ruta),
                    ],
                    ['name' => 'age',    'contents' => $estudio->edad    ?? 0],
                    ['name' => 'sex',    'contents' => $paciente->sexo   ?? ''],
                    ['name' => 'weight', 'contents' => $paciente->peso   ?? 0],
                ],
            ]);

            $data      = json_decode($response->getBody()->getContents(), true);
            $tiempo_ms = (int) $inicio->diffInMilliseconds(now());

        } catch (RequestException $e) {
            $msg = 'Error al conectar con el servidor de análisis.';
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                $msg  = $body['detail'] ?? $msg;
            }
            return response()->json(['error' => $msg], 502);
        }

        // Buscar el ritmo cardíaco por el label_code que devuelve FastAPI
        $labelCode = $data['top_predictions'][0]['code'] ?? 'NORM';
        $ritmo     = RitmoCardiaco::where('label', strtoupper($labelCode))->first();

        if (!$ritmo) {
            return response()->json([
                'error' => "El código '{$labelCode}' devuelto por el modelo no existe en el catálogo de ritmos.",
            ], 422);
        }

        // Guardar predicción en la tabla predicciones
        $prediccion = new Prediccion();
        $prediccion->imagen_id        = $imagen->imagen_id;
        $prediccion->ritmo_id         = $ritmo->ritmo_id;
        $prediccion->probabilidad     = (float) ($data['confidence'] ?? 0);
        $prediccion->tiempo_ms        = $tiempo_ms;
        $prediccion->top_predicciones = $data['top_predictions'] ?? [];
        $prediccion->save();

        // Registrar en auditoría
        ServicioAuditoria::registrar(
            accion:    'analizar',
            modulo:    'predicciones',
            entidad:   'predicciones',
            entidadId: $prediccion->prediccion_id,
            descripcion: 'Análisis ECG ejecutado por modelo CNN-LSTM vía FastAPI.',
            valoresAnteriores: null,
            valoresNuevos: [
                'imagen_id'    => $imagen->imagen_id,
                'ritmo'        => $ritmo->label,
                'probabilidad' => $prediccion->probabilidad,
                'tiempo_ms'    => $prediccion->tiempo_ms,
            ]
        );

        return response()->json([
            'message'      => 'Análisis completado correctamente.',
            'prediccion_id' => $prediccion->prediccion_id,
            'ritmo'        => $ritmo->nombre,
            'label'        => $ritmo->label,
            'probabilidad' => $prediccion->probabilidad,
            'tiempo_ms'    => $prediccion->tiempo_ms,
            'top_predictions' => $data['top_predictions'] ?? [],
        ]);
    }
}

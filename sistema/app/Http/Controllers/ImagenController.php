<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Estudio;
use App\Models\Imagen;
use App\Models\Paciente;
use App\Models\PrefijoPaciente;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use App\Services\ServicioAuditoria;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagenController extends Controller
{
    public function index(Request $request)
    {
        $pacientes = Paciente::with('prefijoPaciente')
            ->where('estado', 1)
            ->orderByDesc('paciente_id')
            ->get()
            ->map(function ($paciente) {
                return [
                    'paciente_id' => $paciente->paciente_id,
                    'codigo_generado' => $paciente->codigo_generado,
                    'prefijo_id' => $paciente->prefijo_id,
                    'fecha_nacimiento' => $paciente->fecha_nacimiento,
                    'edad' => $paciente->fecha_nacimiento ? now()->diffInYears($paciente->fecha_nacimiento) : null,
                    'sexo' => $paciente->sexo,
                    'peso' => $paciente->peso,
                ];
            });

        $prefijos = PrefijoPaciente::where('estado', 1)
            ->orderBy('nombre')
            ->get(['prefijo_id', 'nombre', 'descripcion']);

        return view('subir-ecg.index', compact('pacientes', 'prefijos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'estudio_id' => 'required|exists:estudios,estudio_id|unique:imagenes,estudio_id',
            'archivo'    => 'required|file|mimes:png,jpg,jpeg,pdf|max:10240',
        ]);

        $archivo   = $request->file('archivo');
        $formato   = $archivo->getClientOriginalExtension();
        $ruta = $this->guardarArchivoEcg($archivo, $formato);

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

        Storage::disk('public')->delete($imagen->ruta);

        $archivo    = $request->file('archivo');
        $formato    = $archivo->getClientOriginalExtension();
        $ruta = $this->guardarArchivoEcg($archivo, $formato);

        $resolucion = null;
        if (in_array($formato, ['png', 'jpg', 'jpeg'])) {
            [$ancho, $alto] = getimagesize($archivo->getRealPath());
            $resolucion = $ancho . 'x' . $alto;
        }

        $imagen->ruta       = $ruta;
        $imagen->formato    = $formato;
        $imagen->resolucion = $resolucion;
        $imagen->tamano_kb  = (int) round($archivo->getSize() / 1024);
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
            'age' => 'required|numeric|min:0|max:120',
            'sex' => 'required|numeric|in:0,1',
            'weight' => 'required|numeric|min:0|max:400',
            'patient_mode' => 'required|in:existing,new',
            'patient_id' => 'required_if:patient_mode,existing|nullable|integer|exists:pacientes,paciente_id',
            'prefijo_id' => 'required_if:patient_mode,new|nullable|integer|exists:prefijos_paciente,prefijo_id',
            'fecha_nacimiento' => 'nullable|date|before:today',
        ]);

        try {
            DB::beginTransaction();

            if (($validated['patient_mode'] ?? 'existing') === 'existing') {
                $paciente = Paciente::where('estado', 1)->findOrFail($validated['patient_id']);
                $paciente->peso = $validated['weight'];
                $paciente->save();
            } else {
                $prefijo = PrefijoPaciente::findOrFail($validated['prefijo_id']);
                $anio = now()->year;
                $ultimo = Paciente::whereYear('created_at', $anio)->count() + 1;
                $codigo = strtoupper($prefijo->nombre) . '-' . $anio . '-' . str_pad($ultimo, 3, '0', STR_PAD_LEFT);
                $sexoPaciente = ((int) $validated['sex'] === 1) ? 'M' : 'F';

                $paciente = new Paciente();
                $paciente->prefijo_id = $prefijo->prefijo_id;
                $paciente->codigo_generado = $codigo;
                $paciente->fecha_nacimiento = $validated['fecha_nacimiento'] ?? null;
                $paciente->sexo = $sexoPaciente;
                $paciente->peso = $validated['weight'];
                $paciente->registrado_por = Auth::id();
                $paciente->save();
            }

            $estudio = new Estudio();
            $estudio->paciente_id = $paciente->paciente_id;
            $estudio->registrado_por = Auth::id();
            $estudio->edad = (int) $validated['age'];
            $estudio->observaciones = null;
            $estudio->save();

            $archivo = $validated['file'];
            $formato = $archivo->getClientOriginalExtension();
            $ruta = $this->guardarArchivoEcg($archivo, $formato);

            $resolucion = null;
            if (in_array($formato, ['png', 'jpg', 'jpeg'])) {
                [$ancho, $alto] = getimagesize($archivo->getRealPath());
                $resolucion = $ancho . 'x' . $alto;
            }

            $imagen = new Imagen();
            $imagen->estudio_id = $estudio->estudio_id;
            $imagen->ruta = $ruta;
            $imagen->formato = $formato;
            $imagen->resolucion = $resolucion;
            $imagen->tamano_kb = (int) round($archivo->getSize() / 1024);
            $imagen->save();

            $response = $this->analizarImagenPersistida($imagen);

            if (! $response->isSuccessful()) {
                DB::rollBack();
                Storage::disk('public')->delete($imagen->ruta);

                return $response;
            }

            DB::commit();

            $payload = $response->getData(true);
            $payload['patient_id'] = $paciente->paciente_id;
            $payload['patient_code'] = $paciente->codigo_generado;
            $payload['study_id'] = $estudio->estudio_id;
            $payload['image_id'] = $imagen->imagen_id;

            return response()->json($payload);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error en analyze: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'No se pudo registrar el paciente, estudio o imagen del ECG.',
            ], 500);
        }
    }

    public function analizar(Imagen $imagen)
    {
        try {
            DB::beginTransaction();

            $response = $this->analizarImagenPersistida($imagen);

            if (! $response->isSuccessful()) {
                DB::rollBack();

                return $response;
            }

            DB::commit();

            return $response;
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Error al analizar la imagen ECG.',
            ], 500);
        }
    }

    private function guardarArchivoEcg($archivo, string $formato): string
    {
        $fecha = now();

        do {
            $nombre = $fecha->format('Ymd-His') . '.' . strtolower($formato);
            $ruta = 'ecg/' . $nombre;
            $fecha = $fecha->copy()->addSecond();
        } while (Storage::disk('public')->exists($ruta));

        return $archivo->storeAs('ecg', $nombre, 'public');
    }

    private function analizarImagenPersistida(Imagen $imagen)
    {
        if ($imagen->prediccion) {
            return response()->json([
                'error' => 'Esta imagen ya tiene una predicción registrada.',
            ], 409);
        }

        $estudio = $imagen->estudio;
        $paciente = $estudio->paciente;

        $apiUrl = env('ECG_API_URL', 'http://localhost:8001');

        try {
            $rutaAbsoluta = Storage::disk('public')->path($imagen->ruta);
            $inicio = now();

            $response = Http::timeout(120)
                ->attach('file', fopen($rutaAbsoluta, 'r'), basename($imagen->ruta))
                ->post("{$apiUrl}/predict", [
                    'age'    => $estudio->edad ?? 0,
                    'sex'    => ($paciente->sexo === 'M') ? 1 : 0,
                    'weight' => $paciente->peso ?? 0,
                ]);

            if ($response->failed()) {
                $msg = 'Error al conectar con el servidor de análisis.';
                $body = $response->json();
                
                if ($body) {
                    $msg = $body['detail'] ?? $msg;
                    if (is_array($msg)) {
                        $errors = [];
                        foreach ($msg as $error) {
                            $loc = implode('.', $error['loc'] ?? []);
                            $errors[] = $loc . ': ' . ($error['msg'] ?? '');
                        }
                        $msg = 'Error de validación en la IA: ' . implode(', ', $errors);
                    }
                }
                return response()->json(['error' => $msg], 502);
            }

            $data = $response->json();
            $tiempo_ms = (int) $inicio->diffInMilliseconds(now());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Excepción en ImagenController: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error de conexión con el servidor de análisis: ' . $e->getMessage()], 502);
        }

        $labelCode = $data['top_predictions'][0]['code'] ?? 'NORM';
        $ritmo = RitmoCardiaco::where('label', strtoupper($labelCode))->first();

        if (! $ritmo) {
            return response()->json([
                'error' => "El código '{$labelCode}' devuelto por el modelo no existe en el catálogo de ritmos.",
            ], 422);
        }

        $confianza = (float) ($data['confidence'] ?? 0);
        
        $prediccion = new Prediccion();
        $prediccion->imagen_id = $imagen->imagen_id;
        $prediccion->ritmo_id = $ritmo->ritmo_id;
        $prediccion->probabilidad = $confianza > 1 ? $confianza / 100 : $confianza;
        $prediccion->tiempo_ms = $tiempo_ms;
        $prediccion->top_predicciones = $data['top_predictions'] ?? [];
        $prediccion->save();

        ServicioAuditoria::registrar(
            accion: 'analizar',
            modulo: 'predicciones',
            entidad: 'predicciones',
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
            'message' => 'Análisis completado correctamente.',
            'prediccion_id' => $prediccion->prediccion_id,
            'patient_id' => $paciente->paciente_id,
            'patient_code' => $paciente->codigo_generado,
            'study_id' => $estudio->estudio_id,
            'image_id' => $imagen->imagen_id,
            'ritmo' => $ritmo->nombre,
            'label' => $ritmo->label,
            'probabilidad' => $prediccion->probabilidad,
            'tiempo_ms' => $prediccion->tiempo_ms,
            'top_predictions' => $data['top_predictions'] ?? [],
            'signals' => $data['signals'] ?? null,
        ]);
    }
}

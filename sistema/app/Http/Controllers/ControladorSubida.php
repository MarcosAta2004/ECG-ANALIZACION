<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use App\Services\ServicioAuditoria;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class ControladorSubida extends Controlador
{
    public function index()
    {
        return view('subir-ecg');
    }

    /**
     * Recibe el archivo + metadata del frontend,
     * lo reenvía a FastAPI, guarda el resultado en la DB
     * y devuelve el JSON al frontend.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:png,jpg,jpeg,pdf,csv,txt|max:20480',
            'age'    => 'required|numeric|min:0|max:120',
            'sex'    => 'required|in:0,1',
            'weight' => 'required|numeric|min:1|max:300',
        ]);

        $apiUrl = env('ECG_API_URL', 'http://localhost:8001');
        $client = new Client(['timeout' => 120]);

        try {
            $file = $request->file('file');
            $generatedFilename = Carbon::now()->format('Ymd') . '-' . random_int(100000, 999999) . '.pdf';
            do {
                $patientIdentifier = 'PACIENTE_' . random_int(100000, 999999);
            } while (AnalisisEcg::where('patient_identifier', $patientIdentifier)->exists());

            $response = $client->post("{$apiUrl}/predict", [
                'multipart' => [
                    ['name' => 'file',   'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                    ['name' => 'age',    'contents' => $request->age],
                    ['name' => 'sex',    'contents' => $request->sex],
                    ['name' => 'weight', 'contents' => $request->weight],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $msg = 'Error al conectar con el servidor de análisis.';
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                $msg  = $body['detail'] ?? $msg;
            }
            return response()->json(['error' => $msg], 502);
        }

        // Guardar en la base de datos
        $user = Session::get('user');
        $userId = $user['id'] ?? null;

        if ($userId) {
            $isNormal = str_contains(strtolower($data['label'] ?? ''), 'normal');
            $confidence = (float) ($data['confidence'] ?? 0);

            $analisis = AnalisisEcg::create([
                'user_id'         => $userId,
                'filename'        => $generatedFilename,
                'patient_identifier' => $patientIdentifier,
                'patient_age'     => $request->age,
                'patient_sex'     => $request->sex,
                'patient_weight'  => $request->weight,
                'label'           => $data['label']       ?? 'Desconocido',
                'label_code'      => $data['top_predictions'][0]['code'] ?? 'NORM',
                'type'            => $isNormal ? 'normal' : 'arritmia',
                'confidence'      => $confidence,
                'top_predictions' => $data['top_predictions'] ?? [],
            ]);

            ServicioAuditoria::registrar(
                'crear',
                'ECG',
                'ecg_analyses',
                $analisis->id,
                'Carga y analisis de ECG.',
                null,
                [
                    'filename' => $analisis->filename,
                    'patient_identifier' => $analisis->patient_identifier,
                    'label' => $analisis->label,
                    'type' => $analisis->type,
                    'confidence' => $analisis->confidence,
                ],
                $request
            );
        }

        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Imagen;
use App\Models\Reporte;
use App\Models\Estudio;
use App\Services\ExcelReportService;
use App\Services\ServicioAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // Obtenemos estadísticas por paciente cruzando con Estudios e Imágenes
        $patients = \App\Models\Paciente::withCount('estudios')
            ->has('estudios')
            ->get()
            ->map(function($p) {
                return [
                    'patient_identifier' => $p->codigo_generado,
                    'total' => $p->estudios_count,
                    'last_analysis' => $p->estudios()->latest()->first()->created_at ?? null,
                ];
            });

        $stats = [
            'patients' => \App\Models\Paciente::has('estudios')->count(),
            'analyses' => \App\Models\Imagen::count(),
        ];

        return view('reportes.index', compact('patients', 'stats'));
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'mode'       => ['required', 'in:all,selected'],
            'patients'   => ['nullable', 'array'],
            'patients.*' => ['string'],
        ]);

        $selectedPatients = $validated['patients'] ?? [];
        if ($validated['mode'] === 'selected' && empty($selectedPatients)) {
            return back()->with('error', 'Selecciona al menos un paciente para generar el reporte.');
        }

        // Traer imágenes con todas las relaciones necesarias
        $query = Imagen::with([
            'estudio.paciente',
            'prediccion.ritmo',
            'estudio.diagnostico.ritmoCardiaco',
        ])->orderBy('created_at');

        if ($validated['mode'] === 'selected') {
            $query->whereHas('estudio.paciente', function ($q) use ($selectedPatients) {
                $q->whereIn('codigo_generado', $selectedPatients);
            });
        }

        $imagenes = $query->get();

        // Mapear a objetos simples que ExcelReportService pueda consumir
        $rows = $imagenes->map(function ($img) {
            $prediccion  = $img->prediccion;
            $ritmo       = $prediccion?->ritmo;
            $diagnostico = $img->estudio?->diagnostico;
            $ritmoDoc    = $diagnostico?->ritmoCardiaco;
            $prob        = $prediccion ? round($prediccion->probabilidad * 100, 1) : 0;
            $isNormal    = ($ritmo?->label ?? '') === 'NORM';

            return (object) [
                'patient_identifier' => $img->estudio?->paciente?->codigo_generado ?? 'N/A',
                'filename'           => $img->nombre_original ?? $img->ruta ?? 'N/A',
                'created_at'         => $img->created_at,
                'label'              => $ritmo?->nombre ?? 'N/A',
                'confidence'         => $prob,
                'type'               => $isNormal ? 'normal' : 'arritmia',
                'doctor_result'      => $diagnostico ? ($ritmoDoc?->label === 'NORM' ? 'normal' : 'arritmia') : null,
                'doctor_code'        => $ritmoDoc?->label ?? '',
                'doctor_label'       => $ritmoDoc?->nombre ?? '',
                'doctor_notes'       => $diagnostico?->observacion ?? '',
            ];
        });

        $filename = 'reporte_ecg_' . now()->format('Ymd_His') . '.xlsx';

        $service = new ExcelReportService();
        $path    = $service->buildExcelReport($rows);

        $imagenes
            ->pluck('estudio')
            ->filter()
            ->unique('estudio_id')
            ->each(function (Estudio $estudio) use ($filename) {
                $this->registrarReporteGenerado(
                    $estudio,
                    $filename,
                    'Reporte consolidado generado desde el modulo de reportes.'
                );
            });

        ServicioAuditoria::registrar(
            'descargar',
            'Reportes',
            'ecg_analyses',
            null,
            'Descarga de reporte de pacientes.',
            null,
            [
                'mode'            => $validated['mode'],
                'patients'        => $selectedPatients,
                'total_registros' => $rows->count(),
                'filename'        => $filename,
            ],
            $request
        );

        return Response::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'estudio_id' => 'required|exists:estudios,estudio_id|unique:reportes,estudio_id',
            'resumen'    => 'nullable',
        ]);

        $reporte = new Reporte();
        $reporte->estudio_id   = $request->estudio_id;
        $reporte->generado_por = Auth::id();
        $reporte->resumen      = $request->resumen ?? null;
        $reporte->save();

        return redirect()->route('reportes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de generar correctamente el reporte del estudio',
            'alert'   => 'success',
            'data'    => $reporte->estudio->paciente->codigo_generado,
        ]);
    }

    public function update(Request $request, Reporte $reporte)
    {
        $request->validate([
            'resumen' => 'nullable',
        ]);

        $reporte->resumen = $request->resumen ?? null;
        $reporte->save();

        return redirect()->route('reportes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el reporte del estudio',
            'alert'   => 'success',
            'data'    => $reporte->estudio->paciente->codigo_generado,
        ]);
    }

    public function destroy(Reporte $reporte)
    {
        $reporte->estado = 0;
        $reporte->save();

        return redirect()->route('reportes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de deshabilitar el reporte',
            'alert'   => 'danger',
            'data'    => $reporte->estudio->paciente->codigo_generado,
        ]);
    }

    public function activar(Reporte $reporte)
    {
        $reporte->estado = 1;
        $reporte->save();

        return redirect()->route('reportes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de habilitar el reporte',
            'alert'   => 'primary',
            'data'    => $reporte->estudio->paciente->codigo_generado,
        ]);
    }

    public function descargarPDF($id)
    {
        // 1. Obtener el estudio con todas sus relaciones
        $estudio = \App\Models\Estudio::with(['paciente', 'diagnostico.ritmoCardiaco', 'imagen.prediccion.ritmoCardiaco'])->findOrFail($id);
        
        $paciente = $estudio->paciente;
        $diagnostico = $estudio->diagnostico;
        $ia = $estudio->imagen?->prediccion;
        $ritmoIa = $ia?->ritmoCardiaco ?? $ia?->ritmo;
        $ritmoMedico = $diagnostico?->ritmoCardiaco;
        $concordancia = $ritmoIa && $ritmoMedico
            ? mb_strtoupper(trim((string) $ritmoIa->label), 'UTF-8') === mb_strtoupper(trim((string) $ritmoMedico->label), 'UTF-8')
            : ($diagnostico?->concordancia ?? false);
        
        // 2. Preparar datos para la vista
        $data = [
            'paciente' => [
                'codigo_generado' => $paciente->codigo_generado ?? 'N/A',
                'genero' => strtoupper((string) ($paciente->sexo ?? 'N/A')),
                'edad' => $estudio->edad ?? (
                    $paciente->fecha_nacimiento
                        ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age
                        : 'N/A'
                ),
            ],
            'estudio' => [
                'fecha' => $estudio->created_at->format('d/m/Y H:i'),
                'id' => $estudio->estudio_id,
                'archivo' => $estudio->imagen?->nombre_original
                    ?? $estudio->imagen?->filename
                    ?? $estudio->imagen?->ruta
                    ?? 'N/A',
            ],
            'ia' => [
                'resultado' => $ritmoIa?->nombre ?? 'SIN PROCESAR',
                'codigo' => $ritmoIa?->label ?? 'N/A',
                'probabilidad' => isset($ia->probabilidad) ? number_format($ia->probabilidad * 100, 2) . '%' : 'N/A',
            ],
            'diagnostico' => [
                'ritmo' => $ritmoMedico?->nombre ?? 'PENDIENTE',
                'codigo' => $ritmoMedico?->label ?? 'N/A',
                'concordancia' => $concordancia,
                'observacion' => $diagnostico?->observacion ?? 'Sin observaciones médicas.',
            ],
            'medico' => [
                'nombre' => 'ESPECIALISTA EN TURNO', // Opcional: podrías jalar el nombre del usuario que creó el diagnóstico
                'cmp' => ''
            ]
        ];

        // Nombre del archivo profesional
        $filename = 'REPORTE_' . ($paciente->codigo_generado ?? $id) . '_' . now()->format('dmY') . '.pdf';

        $pdf = Pdf::loadView('reportes.estudio_pdf', $data);

        $this->registrarReporteGenerado(
            $estudio,
            $filename,
            'Reporte clinico generado desde historial.'
        );
        
        return $pdf->download($filename);
    }

    private function registrarReporteGenerado(Estudio $estudio, string $filename, string $resumen): Reporte
    {
        $reporte = Reporte::firstOrNew(['estudio_id' => $estudio->estudio_id]);
        $reporte->generado_por = Auth::id();
        $reporte->resumen = $reporte->resumen ?: $resumen;
        $reporte->ruta_pdf = $filename;
        $reporte->estado = 1;
        $reporte->save();

        return $reporte;
    }
}

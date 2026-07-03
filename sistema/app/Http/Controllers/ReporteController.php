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
use Illuminate\Support\Facades\Storage; // Importante para guardar el PDF en el servidor
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

        $reportStats = [
            'patients' => \App\Models\Paciente::has('estudios')->count(),
            'analyses' => \App\Models\Imagen::count(),
            'official_reports' => Estudio::whereHas('diagnostico')->count(),
        ];

        $estudios = Estudio::with([
                'paciente',
                'diagnostico.ritmoCardiaco',
                'imagen.prediccion.ritmoCardiaco',
                'reporte' => function ($query) {
                    $query->where('ruta_pdf', 'like', '%.pdf')
                        ->latest('reporte_id');
                },
            ])
            ->whereHas('diagnostico')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    if (ctype_digit($search)) {
                        $q->where('estudio_id', (int) $search);
                    }

                    $q->orWhereHas('paciente', function ($patientQuery) use ($search) {
                            $patientQuery->where('codigo_generado', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('diagnostico.ritmoCardiaco', function ($rhythmQuery) use ($search) {
                            $rhythmQuery->where('nombre', 'like', '%' . $search . '%')
                                ->orWhere('label', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('estudio_id')
            ->paginate(10)
            ->withQueryString();

        return view('reportes.index', compact('patients', 'reportStats', 'estudios'));
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
                    'Reporte consolidado Excel generado desde el modulo de reportes.'
                );
            });

        ServicioAuditoria::registrar(
            'descargar',
            'Reportes',
            'ecg_analyses',
            null,
            'Descarga de reporte de pacientes en Excel.',
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
            'estudio_id' => 'required|exists:estudios,estudio_id',
            'resumen'    => 'nullable',
        ]);

        $reporte = new Reporte();
        $reporte->estudio_id   = $request->estudio_id;
        $reporte->generado_por = Auth::id();
        $reporte->resumen      = $request->resumen ?? null;
        $reporte->estado       = 1;
        $reporte->save();

        return redirect()->route('reportes.index')->with([
            'ok'      => 'enabled',
            'message' => 'Se acaba de registrar correctamente el reporte del estudio',
            'alert'   => 'success',
            'data'    => $reporte->estudio->paciente->codigo_generado ?? 'N/A',
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
            'data'    => $reporte->estudio->paciente->codigo_generado ?? 'N/A',
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
            'data'    => $reporte->estudio->paciente->codigo_generado ?? 'N/A',
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
            'data'    => $reporte->estudio->paciente->codigo_generado ?? 'N/A',
        ]);
    }

    public function verPDF($id)
    {
        $estudio = \App\Models\Estudio::findOrFail($id);

        // Seguridad: Verificar si existe el diagnóstico final
        if (!$estudio->diagnostico()->exists()) {
            return redirect()->back()->with([
                'alert'   => 'warning',
                'message' => 'El reporte aún no puede generarse. Se requiere el diagnóstico final del cardiólogo.'
            ]);
        }

        if ($reporteActivo = $this->reporteActivoConArchivo($estudio)) {
            return Storage::disk('public')->response(
                'reportes/' . $reporteActivo->ruta_pdf,
                $reporteActivo->ruta_pdf,
                ['Content-Type' => 'application/pdf'],
                'inline'
            );
        }

        [$pdf, $filename] = $this->generarPdfEstudio($id);

        return $pdf->stream($filename);
    }

    public function descargarPDF($id)
    {
        $estudio = \App\Models\Estudio::findOrFail($id);

        if (!$estudio->diagnostico()->exists()) {
            return redirect()->back()->with([
                'alert'   => 'warning',
                'message' => 'El reporte aún no puede generarse para descargarse. Se requiere el diagnóstico final del cardiólogo.'
            ]);
        }

        if ($reporteActivo = $this->reporteActivoConArchivo($estudio)) {
            return Storage::disk('public')->download(
                'reportes/' . $reporteActivo->ruta_pdf,
                $reporteActivo->ruta_pdf
            );
        }

        [$pdf, $filename] = $this->generarPdfEstudio($id);

        return $pdf->download($filename);
    }

    private function generarPdfEstudio($id): array
    {
        // 1. Obtener el estudio con todas sus relaciones
        $estudio = \App\Models\Estudio::with(['paciente', 'diagnostico.ritmoCardiaco', 'imagen.prediccion.ritmoCardiaco'])->findOrFail($id);
        
        // 2. Verificamos si YA EXISTE un reporte ACTIVO y guardado físicamente
        // ==========================================
        // SI NO EXISTE, PREPARAMOS LOS DATOS Y LO GENERAMOS
        // ==========================================

        $paciente = $estudio->paciente;
        $diagnostico = $estudio->diagnostico;
        $ia = $estudio->imagen?->prediccion;
        $ritmoIa = $ia?->ritmoCardiaco ?? $ia?->ritmo;
        $ritmoMedico = $diagnostico?->ritmoCardiaco;
        $concordancia = $ritmoIa && $ritmoMedico
            ? mb_strtoupper(trim((string) $ritmoIa->label), 'UTF-8') === mb_strtoupper(trim((string) $ritmoMedico->label), 'UTF-8')
            : ($diagnostico?->concordancia ?? false);
        
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
                'nombre' => 'ESPECIALISTA EN TURNO',
                'cmp' => ''
            ]
        ];

        // 3. Generamos un nombre ÚNICO para evitar colisiones
        $version = now()->format('dmY_His');
        $filename = 'REPORTE_' . ($paciente->codigo_generado ?? $id) . '_' . $version . '.pdf';

        // 4. Creamos el PDF con DomPDF
        $pdf = Pdf::loadView('reportes.estudio_pdf', $data);

        // 5. GUARDAMOS EL ARCHIVO FÍSICAMENTE EN EL SERVIDOR (storage/app/public/reportes)
        Storage::disk('public')->put('reportes/' . $filename, $pdf->output());

        // 6. Registramos en la Base de Datos
        $this->registrarReporteGenerado(
            $estudio,
            $filename,
            'Reporte clínico oficial validado por cardiología.'
        );

        return [$pdf, $filename];
    }

    private function reporteActivoConArchivo(Estudio $estudio): ?Reporte
    {
        return Reporte::activos()
            ->where('estudio_id', $estudio->estudio_id)
            ->whereNotNull('ruta_pdf')
            ->latest('reporte_id')
            ->get()
            ->first(function (Reporte $reporte) {
                return Storage::disk('public')->exists('reportes/' . $reporte->ruta_pdf);
            });
    }

    private function registrarReporteGenerado(Estudio $estudio, string $filename, string $resumen): Reporte
    {
        // Siempre creamos un nuevo registro para mantener el historial (versionado)
        $reporte = new Reporte();
        $reporte->estudio_id = $estudio->estudio_id;
        $reporte->generado_por = Auth::id();
        $reporte->resumen = $resumen;
        $reporte->ruta_pdf = $filename; // Guardamos el nombre del archivo físico
        $reporte->estado = 1; // Lo marcamos como el oficial actual
        $reporte->save();

        return $reporte;
    }
}

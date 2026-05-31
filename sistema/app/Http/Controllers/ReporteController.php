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

        return view('reportes', compact('patients', 'stats'));
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:all,selected'],
            'patients' => ['nullable', 'array'],
            'patients.*' => ['string'],
        ]);

        $selectedPatients = $validated['patients'] ?? [];
        if ($validated['mode'] === 'selected' && empty($selectedPatients)) {
            return back()->with('error', 'Selecciona al menos un paciente para generar el reporte.');
        }

        $query = Imagen::query()
            ->join('estudios', 'imagenes.estudio_id', '=', 'estudios.estudio_id')
            ->join('pacientes', 'estudios.paciente_id', '=', 'pacientes.paciente_id')
            ->select('imagenes.*', 'pacientes.codigo_generado as patient_identifier')
            ->orderBy('pacientes.codigo_generado')
            ->orderBy('imagenes.created_at');

        if ($validated['mode'] === 'selected') {
            $query->whereIn('pacientes.codigo_generado', $selectedPatients);
        }

        $rows = $query->get();
        $filename = 'reporte_pacientes_' . now()->format('Ymd_His') . '.xlsx';

        $service = new ExcelReportService();
        $path = $service->buildExcelReport($rows);

        ServicioAuditoria::registrar(
            'descargar',
            'Reportes',
            'ecg_analyses',
            null,
            'Descarga de reporte de pacientes.',
            null,
            [
                'mode' => $validated['mode'],
                'patients' => $selectedPatients,
                'total_registros' => $rows->count(),
                'filename' => $filename,
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
        
        // 2. Preparar datos para la vista
        $data = [
            'paciente' => [
                'codigo_generado' => $paciente->codigo_generado ?? 'N/A',
                'genero' => $paciente->genero ?? 'N/A',
                'edad' => $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : 'N/A',
            ],
            'estudio' => [
                'fecha' => $estudio->created_at->format('d/m/Y H:i'),
            ],
            'ia' => [
                'resultado' => $ia->resultado ?? 'SIN PROCESAR',
                'probabilidad' => isset($ia->probabilidad) ? number_format($ia->probabilidad, 2) . '%' : 'N/A',
            ],
            'diagnostico' => [
                'ritmo' => $diagnostico->ritmoCardiaco->nombre ?? 'PENDIENTE',
                'concordancia' => $diagnostico->concordancia ?? false,
                'observacion' => $diagnostico->observacion ?? 'Sin observaciones médicas.',
            ],
            'medico' => [
                'nombre' => 'ESPECIALISTA EN TURNO', // Opcional: podrías jalar el nombre del usuario que creó el diagnóstico
                'cmp' => ''
            ]
        ];

        $pdf = Pdf::loadView('reportes.estudio_pdf', $data);
        
        // Nombre del archivo profesional
        $filename = 'REPORTE_' . ($paciente->codigo_generado ?? $id) . '_' . now()->format('dmY') . '.pdf';
        
        return $pdf->download($filename);
    }
}

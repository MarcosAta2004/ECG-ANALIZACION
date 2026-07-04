<?php

namespace App\Http\Controllers;

use App\Models\Diagnostico;
use App\Models\Estudio;
use App\Models\Imagen;
use App\Models\Paciente;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use App\Models\Reporte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class MenuPrincipalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $routeName = $request->route()?->getName();
        $from = $request->date('from');
        $to = $request->date('to');

        $stats = $this->buildSummaryStats($from, $to);
        $workQueue = $this->buildWorkQueue($from, $to);
        $distribucionArritmias = $this->buildDistribucionArritmias($from, $to);

        if ($routeName === 'dashboard') {
            $metrics = $this->buildMetricsReport($request);

            return view('dashboard.index', array_merge($metrics, [
                'stats'                => $stats,
                'workQueue'            => $workQueue,
                'distribucionArritmias'=> $distribucionArritmias,
                'filters'              => $metrics['filters'] ?? [],
            ]));
        }

        return view('dashboard.index', compact('stats', 'workQueue', 'distribucionArritmias'));
    }

    public function downloadStatisticsCsv(Request $request)
    {
        $metrics = $this->buildMetricsReport($request);
        $filters = $metrics['filters'];

        $filteredImages = Imagen::query()
            ->with(['prediccion.ritmo', 'estudio.diagnostico.ritmoCardiaco', 'estudio.paciente'])
            ->when($filters['from'], fn($q) => $q->where('created_at', '>=', $filters['from']))
            ->when($filters['to'],   fn($q) => $q->where('created_at', '<=', $filters['to'] . ' 23:59:59'))
            ->get();

        $filename = 'estadisticas_ecg_' . now()->format('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($filteredImages) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fputs($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'ID Estudio',
                'Paciente Doc.',
                'Fecha Subida',
                'Analisis IA',
                'Confianza IA (%)',
                'Validado Por Medico',
                'Diagnostico Medico',
                'Concordancia'
            ], ';'); // Usamos punto y coma porque Excel hispano lo prefiere

            foreach ($filteredImages as $img) {
                $estudio = $img->estudio;
                $prediccion = $img->prediccion;
                $diagnostico = $estudio?->diagnostico;

                $iaLabel = $prediccion?->ritmo?->nombre ?? 'Desconocido';
                $iaConfianza = $prediccion ? round($prediccion->probabilidad * 100, 2) : 0;
                
                $medicoChecked = $diagnostico ? 'SI' : 'NO';
                $medicoLabel = $diagnostico?->ritmoCardiaco?->nombre ?? 'N/A';
                
                $concordancia = 'N/A';
                if ($diagnostico) {
                    $concordancia = $diagnostico->concordancia ? 'SI' : 'NO';
                }

                fputcsv($file, [
                    $estudio?->estudio_id ?? '-',
                    $estudio?->paciente?->numero_documento ?? '-',
                    $img->created_at->format('Y-m-d H:i:s'),
                    $iaLabel,
                    $iaConfianza,
                    $medicoChecked,
                    $medicoLabel,
                    $concordancia
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    private function buildStatisticsPdfNative(array $data): string
    {
        $blue   = [0.04, 0.55, 0.80];
        $green  = [0.08, 0.62, 0.42];
        $orange = [0.93, 0.55, 0.10];
        $red    = [0.86, 0.18, 0.22];
        $gray   = [0.30, 0.36, 0.45];
        $light  = [0.95, 0.98, 1.00];

        // ---- Pagina 1: Resumen general ----
        $page1 = '';
        $this->pdfText($page1, 50, 805, 'REPORTE DE ESTADISTICAS ECG', 18, $blue);
        $this->pdfText($page1, 50, 784, 'Generado: ' . $data['generated_at'] . '   |   Periodo: ' . $data['period'], 10, $gray);
        $this->pdfLine($page1, 50, 772, 545, 772, [0.82, 0.88, 0.92]);

        $this->pdfCard($page1, 50,  690, 112, 58, 'Total',     number_format($data['total']),                               'analisis',   $blue);
        $this->pdfCard($page1, 174, 690, 112, 58, 'Normales',  number_format($data['normales']),                            number_format($data['pct_normal'], 1) . '%', $green);
        $this->pdfCard($page1, 298, 690, 112, 58, 'Arritmias', number_format($data['arritmias']),                           number_format($data['pct_arr'],    1) . '%', $orange);
        $this->pdfCard($page1, 422, 690, 123, 58, 'Confianza', number_format($data['avg_confidence'], 1) . '%',             'promedio',   $blue);

        $this->pdfText($page1, 50, 650, 'Comparacion de resultados IA', 13, [0.10, 0.14, 0.20]);
        $this->pdfBarChart($page1, 50, 455, 235, 165, [
            ['label' => 'Normal',   'value' => $data['normales'],   'suffix' => ' casos', 'color' => $green],
            ['label' => 'Arritmia', 'value' => $data['arritmias'],  'suffix' => ' casos', 'color' => $orange],
        ], max($data['normales'], $data['arritmias'], 1));

        $this->pdfText($page1, 320, 650, 'Cobertura de validacion medica', 13, [0.10, 0.14, 0.20]);
        $this->pdfBarChart($page1, 320, 455, 225, 165, [
            ['label' => 'Revisados',  'value' => $data['reviewed_count'],   'suffix' => ' casos', 'color' => $blue],
            ['label' => 'Pendientes', 'value' => $data['unreviewed_count'],  'suffix' => ' casos', 'color' => $gray],
        ], max($data['reviewed_count'], $data['unreviewed_count'], 1));

        $this->pdfRect($page1, 50, 335, 495, 82, $light, [0.82, 0.88, 0.92]);
        $this->pdfText($page1, 68, 392, 'Lectura detallada del periodo', 12, [0.10, 0.14, 0.20]);
        $this->pdfText($page1, 68, 372, 'La IA clasifico ' . number_format($data['normales']) . ' ECG como normales y ' . number_format($data['arritmias']) . ' como arritmias.', 10, $gray);
        $this->pdfText($page1, 68, 356, 'La cobertura clinica revisada es ' . number_format($data['reviewed_rate'], 1) . '%, con confianza media revisada de ' . number_format($data['reviewed_avg_confidence'], 1) . '%.', 10, $gray);
        $this->pdfText($page1, 68, 340, 'Este reporte compara volumen, validacion medica y rendimiento del clasificador en el rango seleccionado.', 10, $gray);
        $this->pdfFooter($page1, 1);

        // ---- Pagina 2: Validacion clinica ----
        $page2 = '';
        $this->pdfText($page2, 50, 805, 'VALIDACION CLINICA Y RENDIMIENTO', 18, $blue);
        $this->pdfText($page2, 50, 784, 'Matriz de confusion y metricas comparativas del modelo.', 10, $gray);
        $this->pdfLine($page2, 50, 772, 545, 772, [0.82, 0.88, 0.92]);

        if ($data['real_metrics']) {
            $rm = $data['real_metrics'];
            $this->pdfText($page2, 50, 735, 'Matriz de confusion', 13, [0.10, 0.14, 0.20]);
            $this->pdfConfCell($page2, 50,  640, 110, 62, 'Verdadero +', $rm['tp'], $green);
            $this->pdfConfCell($page2, 170, 640, 110, 62, 'Falso -',     $rm['fn'], $red);
            $this->pdfConfCell($page2, 50,  565, 110, 62, 'Falso +',     $rm['fp'], $orange);
            $this->pdfConfCell($page2, 170, 565, 110, 62, 'Verdadero -', $rm['tn'], $blue);

            $this->pdfText($page2, 320, 735, 'Metricas de rendimiento', 13, [0.10, 0.14, 0.20]);
            $this->pdfHBars($page2, 320, 575, 220, [
                ['label' => 'Sensibilidad',  'value' => $rm['sensitivity'], 'color' => $green],
                ['label' => 'Especificidad', 'value' => $rm['specificity'], 'color' => $blue],
                ['label' => 'Precision',     'value' => $rm['precision'],   'color' => [0.48, 0.28, 0.78]],
                ['label' => 'Exactitud',     'value' => $rm['accuracy'],    'color' => $orange],
                ['label' => 'F1-Score',      'value' => $rm['f1'],          'color' => [0.18, 0.45, 0.72]],
            ]);
        } else {
            $this->pdfText($page2, 50, 720, 'No hay revisiones medicas suficientes', 11, $gray);
            $this->pdfText($page2, 50, 706, 'para calcular la matriz de confusion.', 11, $gray);
        }

        $this->pdfRect($page2, 50,  390, 235, 115, $light, [0.82, 0.88, 0.92]);
        $this->pdfText($page2, 68,  478, 'Curva ROC', 13, [0.10, 0.14, 0.20]);
        if ($data['roc_data']) {
            $this->pdfText($page2, 68, 452, 'AUC-ROC: ' . number_format($data['roc_data']['auc'], 3), 12, $blue);
            $this->pdfText($page2, 68, 430, 'Umbral optimo: ' . number_format($data['roc_data']['best_threshold'] * 100, 1) . '%', 11, $gray);
            $this->pdfText($page2, 68, 410, 'Evalua la capacidad del score para', 9, $gray);
            $this->pdfText($page2, 68, 399, 'separar arritmia y ritmo normal.', 9, $gray);
        } else {
            $this->pdfText($page2, 68, 448, 'ROC no disponible en este rango.', 11, $gray);
            $this->pdfText($page2, 68, 428, 'Se necesitan casos revisados de ambas clases.', 10, $gray);
        }

        $this->pdfRect($page2, 310, 375, 235, 130, [1.00, 0.98, 0.93], [0.90, 0.82, 0.66]);
        $this->pdfText($page2, 328, 478, 'Interpretacion', 13, [0.10, 0.14, 0.20]);
        $this->pdfText($page2, 328, 452, 'La estadistica resume coincidencia', 10, $gray);
        $this->pdfText($page2, 328, 440, 'entre IA y medico.', 10, $gray);
        $this->pdfText($page2, 328, 424, 'Los falsos positivos indican', 10, $gray);
        $this->pdfText($page2, 328, 412, 'alarmas innecesarias.', 10, $gray);
        $this->pdfText($page2, 328, 396, 'Los falsos negativos requieren', 10, $gray);
        $this->pdfText($page2, 328, 384, 'especial seguimiento clinico.', 10, $gray);

        $this->pdfText($page2, 50, 340, 'Nota clinica', 12, [0.10, 0.14, 0.20]);
        $this->pdfText($page2, 50, 320, 'Este reporte es una herramienta de apoyo para lectura del ECG', 10, $gray);
        $this->pdfText($page2, 50, 308, 'y no sustituye la evaluacion del medico especialista.', 10, $gray);
        $this->pdfFooter($page2, 2);

        return $this->buildPdfDoc([$page1, $page2]);
    }

    // ---- Helpers PDF nativos ----

    private function pdfText(string &$c, float $x, float $y, string $text, int $size, array $color): void
    {
        $encoded = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
        $c .= "BT\n";
        $c .= sprintf('%.3F %.3F %.3F rg', $color[0], $color[1], $color[2]) . "\n";
        $c .= "/F1 {$size} Tf\n";
        $c .= sprintf('%.2F %.2F Td', $x, $y) . "\n";
        $c .= '(' . $escaped . ") Tj\n";
        $c .= "ET\n";
    }

    private function pdfRect(string &$c, float $x, float $y, float $w, float $h, array $fill, ?array $stroke = null): void
    {
        $c .= "q\n";
        $c .= sprintf('%.3F %.3F %.3F rg', $fill[0], $fill[1], $fill[2]) . "\n";
        if ($stroke) {
            $c .= sprintf('%.3F %.3F %.3F RG', $stroke[0], $stroke[1], $stroke[2]) . "\n";
            $c .= sprintf('%.2F %.2F %.2F %.2F re B', $x, $y, max($w, 0), max($h, 0)) . "\n";
        } else {
            $c .= sprintf('%.2F %.2F %.2F %.2F re f', $x, $y, max($w, 0), max($h, 0)) . "\n";
        }
        $c .= "Q\n";
    }

    private function pdfLine(string &$c, float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $c .= "q\n";
        $c .= sprintf('%.3F %.3F %.3F RG', $color[0], $color[1], $color[2]) . "\n";
        $c .= sprintf('%.2F %.2F m %.2F %.2F l S', $x1, $y1, $x2, $y2) . "\n";
        $c .= "Q\n";
    }

    private function pdfCard(string &$c, float $x, float $y, float $w, float $h, string $title, string $value, string $sub, array $color): void
    {
        $this->pdfRect($c, $x, $y, $w, $h, [0.97, 0.99, 1.00], [0.82, 0.88, 0.92]);
        $this->pdfText($c, $x + 10, $y + 38, $title, 9,  [0.30, 0.36, 0.45]);
        $this->pdfText($c, $x + 10, $y + 19, $value, 16, $color);
        $this->pdfText($c, $x + 10, $y + 8,  $sub,   8,  [0.38, 0.45, 0.54]);
    }

    private function pdfBarChart(string &$c, float $x, float $y, float $w, float $h, array $items, float $max): void
    {
        $this->pdfLine($c, $x, $y, $x + $w, $y, [0.70, 0.76, 0.82]);
        $bw  = 54;
        $gap = ($w - ($bw * count($items))) / max(count($items) + 1, 1);
        foreach ($items as $i => $item) {
            $bh  = $max > 0 ? (($item['value'] / $max) * ($h - 35)) : 0;
            $bx  = $x + $gap + (($bw + $gap) * $i);
            $by  = $y + 18;
            $this->pdfRect($c, $bx, $by, $bw, $bh, $item['color']);
            $this->pdfText($c, $bx + 4, $by + $bh + 10, number_format($item['value']) . $item['suffix'], 9, [0.10, 0.14, 0.20]);
            $this->pdfText($c, $bx + 1, $y + 3, $item['label'], 9, [0.30, 0.36, 0.45]);
        }
    }

    private function pdfConfCell(string &$c, float $x, float $y, float $w, float $h, string $label, int $value, array $color): void
    {
        $this->pdfRect($c, $x, $y, $w, $h, [0.98, 0.99, 1.00], $color);
        $this->pdfText($c, $x + 10, $y + 39, $label,               10, [0.30, 0.36, 0.45]);
        $this->pdfText($c, $x + 10, $y + 15, number_format($value), 20, $color);
    }

    private function pdfHBars(string &$c, float $x, float $y, float $w, array $items): void
    {
        foreach ($items as $i => $item) {
            $ry = $y + ((count($items) - $i - 1) * 30);
            $this->pdfText($c, $x, $ry + 10, $item['label'], 9, [0.30, 0.36, 0.45]);
            $this->pdfRect($c, $x + 78, $ry + 8, $w - 118, 9, [0.88, 0.92, 0.95]);
            $this->pdfRect($c, $x + 78, $ry + 8, (($w - 118) * min(max($item['value'], 0), 100)) / 100, 9, $item['color']);
            $this->pdfText($c, $x + $w - 32, $ry + 7, number_format($item['value'], 1) . '%', 9, [0.10, 0.14, 0.20]);
        }
    }

    private function pdfFooter(string &$c, int $page): void
    {
        $this->pdfLine($c, 50, 42, 545, 42, [0.82, 0.88, 0.92]);
        $this->pdfText($c, 50,  25, 'ECG Analizacion - Reporte generado automaticamente', 8, [0.45, 0.50, 0.58]);
        $this->pdfText($c, 510, 25, 'Pagina ' . $page, 8, [0.45, 0.50, 0.58]);
    }

    private function buildPdfDoc(array $pages): string
    {
        $objects = [
            1 => "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            3 => "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n",
        ];
        $kids = [];

        foreach (array_values($pages) as $index => $content) {
            $po = 4 + ($index * 2);
            $co = $po + 1;
            $kids[] = $po . ' 0 R';
            $objects[$po] = "{$po} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents {$co} 0 R >>\nendobj\n";
            $objects[$co] = "{$co} 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream\nendobj\n";
        }

        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . '] /Count ' . count($pages) . " >>\nendobj\n";
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $maxObj  = max(array_keys($objects));

        for ($i = 1; $i <= $maxObj; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $objects[$i];
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObj; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    public function indexActualizarContrasena()
    {
        return view('perfil.index');
    }

    public function actualizarContrasena(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with([
                'status' => 'error',
                'message' => 'La contrasena actual es incorrecta',
            ]);
        }

        $user->password = $request->input('password');
        $user->save();

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Contrasena actualizada correctamente',
        ]);
    }

    private function buildMetricsReport(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = ! empty($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = ! empty($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $query = Imagen::query()->with(['prediccion.ritmo', 'estudio.diagnostico.ritmoCardiaco']);
        $filteredImages = $this->applyDateRange(clone $query, $from, $to)->get();

        $total = $filteredImages->count();
        $normales = $filteredImages->filter(fn ($img) => $this->isPositivePrediction($img) === false)->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;
        $avgConfidence = $this->averageConfidence($filteredImages);

        $reviewed = $filteredImages->filter(fn ($img) => $img->estudio?->diagnostico);
        $reviewedCount = $reviewed->count();
        $reviewedRate = $total > 0 ? round($reviewedCount / $total * 100, 1) : 0;
        $positiveReal = $reviewed->filter(fn ($img) => $this->isPositiveDiagnosis($img))->count();
        $negativeReal = $reviewedCount - $positiveReal;
        $reviewedAvgConfidence = $reviewedCount > 0 ? $this->averageConfidence($reviewed) : 0;

        $realMetrics = $reviewedCount > 0 ? $this->buildRealMetrics($reviewed) : null;
        $rocData = $reviewedCount > 1 && $positiveReal > 0 && $negativeReal > 0
            ? $this->buildRocData($reviewed)
            : null;

        $metricCards = $realMetrics
            ? [
                [
                    'key' => 'sens',
                    'label' => 'Sensibilidad',
                    'sublabel' => 'Recall / TPR',
                    'value' => $realMetrics['sensitivity'],
                    'badge' => $reviewedCount . ' revisados',
                    'color' => '#10b981',
                    'desc' => 'Detecta correctamente ' . $realMetrics['sensitivity'] . '% de las arritmias revisadas en el rango.',
                ],
                [
                    'key' => 'spec',
                    'label' => 'Especificidad',
                    'sublabel' => 'TNR / Selectividad',
                    'value' => $realMetrics['specificity'],
                    'badge' => $negativeReal . ' normales',
                    'color' => '#0ea5e9',
                    'desc' => 'Descarta correctamente ' . $realMetrics['specificity'] . '% de los ritmos normales revisados.',
                ],
                [
                    'key' => 'prec',
                    'label' => 'Precision',
                    'sublabel' => 'Valor Predictivo +',
                    'value' => $realMetrics['precision'],
                    'badge' => $positiveReal . ' arritmias',
                    'color' => '#8b5cf6',
                    'desc' => 'De cada alerta emitida, ' . $realMetrics['precision'] . '% coincide con la evaluacion medica.',
                ],
                [
                    'key' => 'acc',
                    'label' => 'Exactitud',
                    'sublabel' => 'Overall Accuracy',
                    'value' => $realMetrics['accuracy'],
                    'badge' => $total . ' analisis',
                    'color' => '#f59e0b',
                    'desc' => 'Porcentaje global de coincidencia entre modelo y medico en el periodo seleccionado.',
                ],
            ]
            : [
                [
                    'key' => 'normal_rate',
                    'label' => 'Ritmos Normales',
                    'sublabel' => 'Distribucion del rango',
                    'value' => $pctNormal,
                    'badge' => $normales . ' casos',
                    'color' => '#10b981',
                    'desc' => 'Representan ' . $pctNormal . '% de los ECG analizados en el periodo seleccionado.',
                ],
                [
                    'key' => 'arrhythmia_rate',
                    'label' => 'Arritmias',
                    'sublabel' => 'Distribucion del rango',
                    'value' => $pctArr,
                    'badge' => $arritmias . ' casos',
                    'color' => '#0ea5e9',
                    'desc' => 'Representan ' . $pctArr . '% de los ECG analizados en el periodo seleccionado.',
                ],
                [
                    'key' => 'avg_confidence',
                    'label' => 'Confianza Media',
                    'sublabel' => 'Promedio del modelo',
                    'value' => $avgConfidence,
                    'badge' => $total . ' lecturas',
                    'color' => '#8b5cf6',
                    'desc' => 'Confianza promedio del modelo sobre los analisis del rango filtrado.',
                ],
                [
                    'key' => 'reviewed_rate',
                    'label' => 'Casos Revisados',
                    'sublabel' => 'Cobertura clinica',
                    'value' => $reviewedRate,
                    'badge' => $reviewedCount . ' revisados',
                    'color' => '#f59e0b',
                    'desc' => 'Porcentaje de estudios con validacion medica dentro del rango seleccionado.',
                ],
            ];

        $secondaryCards = [
            [
                'key' => 'f1',
                'label' => 'F1-Score',
                'formula' => '2PR/(P+R)',
                'value' => $realMetrics ? round($realMetrics['f1'] / 100, 3) : 0,
                'display' => $realMetrics ? number_format($realMetrics['f1'] / 100, 3) : '0.000',
                'delta' => $realMetrics ? number_format($realMetrics['f1'], 1) . '%' : 'sin revision',
                'color' => '#10b981',
                'format' => 'score',
                'desc' => 'Balance entre precision y sensibilidad usando solo los casos revisados del periodo.',
            ],
            [
                'key' => 'auc',
                'label' => 'AUC-ROC',
                'formula' => 'AREA ROC',
                'value' => $rocData['auc'] ?? 0,
                'display' => $rocData ? number_format($rocData['auc'], 3) : '0.000',
                'delta' => $rocData ? 'umbral optimo ' . number_format($rocData['best_threshold'] * 100, 1) . '%' : 'sin curva',
                'color' => '#0ea5e9',
                'format' => 'score',
                'desc' => 'Capacidad del score de separar arritmia y normal dentro del rango filtrado.',
            ],
            [
                'key' => 'coverage',
                'label' => 'Cobertura Revisada',
                'formula' => 'REV/TOT',
                'value' => round($reviewedRate / 100, 3),
                'display' => number_format($reviewedRate, 1) . '%',
                'delta' => $reviewedCount . ' casos',
                'color' => '#f59e0b',
                'format' => 'percent',
                'desc' => 'Porcion del periodo que ya cuenta con validacion medica para medir rendimiento real.',
            ],
        ];

        $dateRangeLabel = match (true) {
            $from && $to => 'Del ' . $from->format('d/m/Y') . ' al ' . $to->format('d/m/Y'),
            $from !== null => 'Desde el ' . $from->format('d/m/Y'),
            $to !== null => 'Hasta el ' . $to->format('d/m/Y'),
            default => 'Todo el historial',
        };

        return [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'label' => $dateRangeLabel,
                'hasRange' => $from !== null || $to !== null,
            ],
            'dashboardData' => [
                'metricCards' => $metricCards,
                'secondaryCards' => $secondaryCards,
            ],
            'realMetrics' => $realMetrics,
            'rocData' => $rocData,
            'positiveReal' => $positiveReal,
            'negativeReal' => $negativeReal,
            'reviewedAvgConfidence' => $reviewedAvgConfidence,
        ];
    }

    private function buildSummaryStats(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): array
    {
        $queryFilter = function ($q) use ($from, $to) {
            $q->when($from, fn($query) => $query->where('created_at', '>=', $from))
              ->when($to, fn($query) => $query->where('created_at', '<=', $to->copy()->endOfDay()));
        };

        $totalImagenes    = Imagen::where($queryFilter)->count();
        $arritmias        = Prediccion::whereHas('ritmo', fn($q) => $q->where('label', '!=', 'NORM'))
                                      ->where($queryFilter)->count();

        // Pendientes: estudios activos sin diagnóstico médico
        $pendientes = Estudio::where('estado', 1)
            ->whereHas('imagen.prediccion')
            ->doesntHave('diagnostico')
            ->where($queryFilter)
            ->count();

        // Concordancia: % de diagnósticos que coincidieron con la IA
        $diagConConcordancia = Diagnostico::where('estado', 1)
            ->whereNotNull('concordancia')
            ->where($queryFilter)
            ->count();
        $diagConcordantes = Diagnostico::where('estado', 1)
            ->where('concordancia', 1)
            ->where($queryFilter)
            ->count();
        $tasaConcordancia = $diagConConcordancia > 0
            ? round($diagConcordantes / $diagConConcordancia * 100, 1)
            : null;

        return [
            [
                'title'       => 'ECGs Analizados',
                'value'       => $totalImagenes,
                'subtitle'    => 'Total procesados por IA',
                'extra'       => null,
                'color'       => 'primary',
            ],
            [
                'title'       => 'Arritmias Detectadas',
                'value'       => $arritmias,
                'subtitle'    => 'Clasificadas como anómalas',
                'extra'       => $totalImagenes > 0 ? round($arritmias / $totalImagenes * 100, 1) . '% del total' : null,
                'color'       => 'danger',
            ],
            [
                'title'       => 'Pendientes de Revisión',
                'value'       => $pendientes,
                'subtitle'    => 'Estudios sin diagnóstico médico',
                'extra'       => $pendientes > 0 ? 'Requieren atención' : 'Sin pendientes',
                'color'       => $pendientes > 0 ? 'warning' : 'success',
            ],
            [
                'title'       => 'Concordancia IA-Médico',
                'value'       => $tasaConcordancia !== null ? $tasaConcordancia . '%' : 'N/A',
                'subtitle'    => 'Acuerdos sobre ' . $diagConConcordancia . ' casos',
                'extra'       => $tasaConcordancia !== null
                    ? ($tasaConcordancia >= 85 ? 'Excelente alineación' : ($tasaConcordancia >= 70 ? 'Buena alineación' : 'Requiere revisión'))
                    : 'Sin datos suficientes',
                'color'       => $tasaConcordancia !== null
                    ? ($tasaConcordancia >= 85 ? 'success' : ($tasaConcordancia >= 70 ? 'info' : 'warning'))
                    : 'secondary',
            ],
        ];
    }

    /**
     * Cola de trabajo: estudios con arritmia detectada por IA, SIN diagnóstico médico aún.
     * Ordenados por confianza descendente (los más urgentes primero).
     */
    private function buildWorkQueue(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): Collection
    {
        return Estudio::with(['paciente', 'imagen.prediccion.ritmoCardiaco'])
            ->where('estado', 1)
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->whereHas('imagen.prediccion')
            ->doesntHave('diagnostico')
            ->get()
            ->sortByDesc(function ($estudio) {
                $prediccion = $estudio->imagen?->prediccion;
                $label = $prediccion?->ritmoCardiaco?->label ?? '';
                $isArrhythmia = $label !== '' && $label !== 'NORM';
                $confidence = (float) ($prediccion?->probabilidad ?? 0);

                return ($isArrhythmia ? 1000 : 0) + $confidence;
            })
            ->take(8)
            ->map(function ($estudio) {
                $prediccion = $estudio->imagen?->prediccion;
                $ritmo      = $prediccion?->ritmoCardiaco;
                $label      = $ritmo?->label ?? '?';
                $isArrhythmia = $label !== '?' && $label !== 'NORM';
                $prob       = $prediccion ? round($prediccion->probabilidad * 100, 1) : 0;

                return [
                    'estudio_id'    => $estudio->estudio_id,
                    'paciente'      => $estudio->paciente?->codigo_generado ?? 'N/A',
                    'ritmo'         => $ritmo?->nombre ?? 'Desconocido',
                    'label'         => $label,
                    'is_arrhythmia' => $isArrhythmia,
                    'resultado'     => $isArrhythmia ? 'Arritmia' : 'Normal',
                    'confianza'     => $prob,
                    'fecha'         => $estudio->created_at->format('d/m/Y H:i'),
                    'hace'          => $estudio->created_at->diffForHumans(),
                ];
            })->values();
    }

    /**
     * Distribución de los tipos de arritmia detectados por la IA.
     */
    private function buildDistribucionArritmias(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): array
    {
        $data = Prediccion::with('ritmoCardiaco')
            ->whereHas('ritmoCardiaco', fn($q) => $q->where('label', '!=', 'NORM'))
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->get()
            ->groupBy(fn($p) => $p->ritmoCardiaco?->label ?? '?')
            ->map(function ($group) {
                $ritmo = $group->first()->ritmoCardiaco;
                return [
                    'label'  => $ritmo?->label ?? '?',
                    'nombre' => $ritmo?->nombre ?? 'Desconocido',
                    'count'  => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(6)
            ->values()
            ->all();

        return $data;
    }

    private function buildRealMetrics(Collection $reviewed): array
    {
        $tp = $reviewed->filter(fn ($img) => $this->isPositivePrediction($img) && $this->isPositiveDiagnosis($img))->count();
        $tn = $reviewed->filter(fn ($img) => ! $this->isPositivePrediction($img) && ! $this->isPositiveDiagnosis($img))->count();
        $fp = $reviewed->filter(fn ($img) => $this->isPositivePrediction($img) && ! $this->isPositiveDiagnosis($img))->count();
        $fn = $reviewed->filter(fn ($img) => ! $this->isPositivePrediction($img) && $this->isPositiveDiagnosis($img))->count();

        $sensitivity = ($tp + $fn) > 0 ? round($tp / ($tp + $fn) * 100, 1) : 0;
        $specificity = ($tn + $fp) > 0 ? round($tn / ($tn + $fp) * 100, 1) : 0;
        $precision = ($tp + $fp) > 0 ? round($tp / ($tp + $fp) * 100, 1) : 0;
        $accuracy = max(1, $reviewed->count()) > 0 ? round(($tp + $tn) / max(1, $reviewed->count()) * 100, 1) : 0;
        $f1 = ($precision + $sensitivity) > 0
            ? round((2 * $precision * $sensitivity) / ($precision + $sensitivity), 1)
            : 0;

        return [
            'tp' => $tp,
            'tn' => $tn,
            'fp' => $fp,
            'fn' => $fn,
            'sensitivity' => $sensitivity,
            'specificity' => $specificity,
            'precision' => $precision,
            'accuracy' => $accuracy,
            'f1' => $f1,
            'reviewedCount' => $reviewed->count(),
        ];
    }

    private function buildRocData(Collection $reviewed): ?array
    {
        $points = $reviewed->map(function ($img) {
            return [
                'score' => $this->positiveClassScore($img),
                'actual' => $this->isPositiveDiagnosis($img),
            ];
        })->sortByDesc('score')->values();

        $positives = $points->where('actual', true)->count();
        $negatives = $points->where('actual', false)->count();

        if ($positives === 0 || $negatives === 0) {
            return null;
        }

        $thresholds = $points->pluck('score')->unique()->sortDesc()->values();
        $roc = [[0, 0]];
        $best = [
            'j' => -INF,
            'threshold' => $thresholds->first() ?? 0,
            'fpr' => 0,
            'tpr' => 0,
        ];

        foreach ($thresholds as $threshold) {
            $predictedPositive = $points->filter(fn ($row) => $row['score'] >= $threshold);
            $tp = $predictedPositive->where('actual', true)->count();
            $fp = $predictedPositive->where('actual', false)->count();
            $tpr = $positives > 0 ? $tp / $positives : 0;
            $fpr = $negatives > 0 ? $fp / $negatives : 0;

            $roc[] = [round($fpr, 4), round($tpr, 4)];

            $j = $tpr - $fpr;
            if ($j > $best['j']) {
                $best = [
                    'j' => $j,
                    'threshold' => $threshold,
                    'fpr' => $fpr,
                    'tpr' => $tpr,
                ];
            }
        }

        $roc[] = [1, 1];

        $auc = 0;
        for ($i = 1; $i < count($roc); $i++) {
            $x1 = $roc[$i - 1][0];
            $y1 = $roc[$i - 1][1];
            $x2 = $roc[$i][0];
            $y2 = $roc[$i][1];
            $auc += (($x2 - $x1) * ($y1 + $y2)) / 2;
        }

        $svg = [
            'width' => 420,
            'height' => 260,
            'padding' => 36,
            'plot_width' => 348,
            'plot_height' => 188,
        ];

        $svgPoints = collect($roc)->map(function ($point) use ($svg) {
            return [
                'x' => round($svg['padding'] + ($point[0] * $svg['plot_width']), 2),
                'y' => round($svg['padding'] + ((1 - $point[1]) * $svg['plot_height']), 2),
            ];
        })->values()->all();

        $buildSmoothPath = function (array $points): string {
            $count = count($points);

            if ($count === 0) {
                return '';
            }

            if ($count === 1) {
                return 'M ' . $points[0]['x'] . ' ' . $points[0]['y'];
            }

            $path = 'M ' . $points[0]['x'] . ' ' . $points[0]['y'];

            for ($i = 0; $i < $count - 1; $i++) {
                $p0 = $points[max(0, $i - 1)];
                $p1 = $points[$i];
                $p2 = $points[$i + 1];
                $p3 = $points[min($count - 1, $i + 2)];

                $c1x = $p1['x'] + (($p2['x'] - $p0['x']) / 6);
                $c1y = $p1['y'] + (($p2['y'] - $p0['y']) / 6);
                $c2x = $p2['x'] - (($p3['x'] - $p1['x']) / 6);
                $c2y = $p2['y'] - (($p3['y'] - $p1['y']) / 6);

                $path .= sprintf(
                    ' C %.2f %.2f, %.2f %.2f, %.2f %.2f',
                    $c1x,
                    $c1y,
                    $c2x,
                    $c2y,
                    $p2['x'],
                    $p2['y']
                );
            }

            return $path;
        };

        $linePath = $buildSmoothPath($svgPoints);
        $startX = $svg['padding'];
        $baselineY = $svg['padding'] + $svg['plot_height'];
        $endX = $svg['padding'] + $svg['plot_width'];
        $areaPath = 'M ' . $startX . ' ' . $baselineY
            . ' L ' . $svgPoints[0]['x'] . ' ' . $svgPoints[0]['y']
            . substr($linePath, 1)
            . ' L ' . $endX . ' ' . $baselineY
            . ' Z';
        $bestX = $svg['padding'] + ($best['fpr'] * $svg['plot_width']);
        $bestY = $svg['padding'] + ((1 - $best['tpr']) * $svg['plot_height']);

        return [
            'auc' => round($auc, 3),
            'best_threshold' => round($best['threshold'] / 100, 4),
            'area_path' => $areaPath,
            'line_path' => $linePath,
            'svg' => array_merge($svg, [
                'best_x' => round($bestX, 2),
                'best_y' => round($bestY, 2),
            ]),
        ];
    }

    private function averageConfidence(Collection $images): float
    {
        $values = $images->map(function ($img) {
            return (float) ($img->prediccion?->probabilidad ?? 0) * 100;
        })->filter(fn ($value) => $value > 0);

        return $values->count() > 0 ? round($values->avg(), 1) : 0;
    }

    private function positiveClassScore($image): float
    {
        $confidence = (float) ($image->prediccion?->probabilidad ?? 0) * 100;
        $isNormal = (($image->prediccion?->ritmo?->label ?? '') === 'NORM');

        return $isNormal ? round(100 - $confidence, 4) : round($confidence, 4);
    }

    private function isPositivePrediction($image): bool
    {
        return (($image->prediccion?->ritmo?->label ?? '') !== 'NORM');
    }

    private function isPositiveDiagnosis($image): bool
    {
        return (($image->estudio?->diagnostico?->ritmoCardiaco?->label ?? '') !== 'NORM');
    }

    private function applyDateRange($query, ?Carbon $from, ?Carbon $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }
}

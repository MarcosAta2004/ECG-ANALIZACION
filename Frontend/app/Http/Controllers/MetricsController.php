<?php

namespace App\Http\Controllers;

use App\Models\EcgAnalysis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class MetricsController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $userId = Session::get('user.id');
        $from = !empty($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = !empty($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $baseQuery = EcgAnalysis::query()->where('user_id', $userId);
        $filteredQuery = $this->applyDateRange(clone $baseQuery, $from, $to);

        $total = (clone $filteredQuery)->count();
        $normales = (clone $filteredQuery)->where('type', 'normal')->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;
        $avgConfidence = round((float) ((clone $filteredQuery)->avg('confidence') ?? 0), 1);

        $reviewed = $this->applyDateRange(clone $baseQuery, $from, $to)
            ->whereNotNull('doctor_result')
            ->get(['type', 'doctor_result', 'confidence']);

        $reviewedCount = $reviewed->count();
        $reviewedRate = $total > 0 ? round($reviewedCount / $total * 100, 1) : 0;
        $positiveReal = $reviewed->where('doctor_result', 'arritmia')->count();
        $negativeReal = $reviewed->where('doctor_result', 'normal')->count();
        $reviewedAvgConfidence = $reviewedCount > 0
            ? round((float) $reviewed->avg('confidence'), 1)
            : 0;

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
                    'color' => 'hsl(160, 70%, 45%)',
                    'desc' => 'Detecta correctamente ' . $realMetrics['sensitivity'] . '% de las arritmias revisadas en el rango.',
                ],
                [
                    'key' => 'spec',
                    'label' => 'Especificidad',
                    'sublabel' => 'TNR / Selectividad',
                    'value' => $realMetrics['specificity'],
                    'badge' => $negativeReal . ' normales',
                    'color' => 'hsl(198, 90%, 50%)',
                    'desc' => 'Descarta correctamente ' . $realMetrics['specificity'] . '% de los ritmos normales revisados.',
                ],
                [
                    'key' => 'prec',
                    'label' => 'Precision',
                    'sublabel' => 'Valor Predictivo +',
                    'value' => $realMetrics['precision'],
                    'badge' => $positiveReal . ' arritmias',
                    'color' => 'hsl(270, 70%, 65%)',
                    'desc' => 'De cada alerta emitida, ' . $realMetrics['precision'] . '% coincide con la evaluacion medica.',
                ],
                [
                    'key' => 'acc',
                    'label' => 'Exactitud',
                    'sublabel' => 'Overall Accuracy',
                    'value' => $realMetrics['accuracy'],
                    'badge' => $total . ' analisis',
                    'color' => 'hsl(38, 90%, 58%)',
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
                    'color' => 'hsl(160, 70%, 45%)',
                    'desc' => 'Representan ' . $pctNormal . '% de los ECG analizados en el periodo seleccionado.',
                ],
                [
                    'key' => 'arrhythmia_rate',
                    'label' => 'Arritmias',
                    'sublabel' => 'Distribucion del rango',
                    'value' => $pctArr,
                    'badge' => $arritmias . ' casos',
                    'color' => 'hsl(198, 90%, 50%)',
                    'desc' => 'Representan ' . $pctArr . '% de los ECG analizados en el periodo seleccionado.',
                ],
                [
                    'key' => 'avg_confidence',
                    'label' => 'Confianza Media',
                    'sublabel' => 'Promedio del modelo',
                    'value' => $avgConfidence,
                    'badge' => $total . ' lecturas',
                    'color' => 'hsl(270, 70%, 65%)',
                    'desc' => 'Confianza promedio del modelo sobre los analisis del rango filtrado.',
                ],
                [
                    'key' => 'reviewed_rate',
                    'label' => 'Casos Revisados',
                    'sublabel' => 'Cobertura clinica',
                    'value' => $reviewedRate,
                    'badge' => $reviewedCount . ' revisados',
                    'color' => 'hsl(38, 90%, 58%)',
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
                'color' => 'hsl(160, 70%, 45%)',
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
                'color' => 'hsl(198, 90%, 50%)',
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
                'color' => 'hsl(38, 90%, 58%)',
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

        $filters = [
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'label' => $dateRangeLabel,
            'hasRange' => $from !== null || $to !== null,
        ];

        $dashboardData = [
            'metricCards' => $metricCards,
            'secondaryCards' => $secondaryCards,
        ];

        return view('metrics', [
            'filters' => $filters,
            'dashboardData' => $dashboardData,
            'realMetrics' => $realMetrics,
            'rocData' => $rocData,
            'positiveReal' => $positiveReal,
            'negativeReal' => $negativeReal,
            'reviewedAvgConfidence' => $reviewedAvgConfidence,
        ]);
    }

    public function downloadStatistics(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $userId = Session::get('user.id');
        $from = !empty($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = !empty($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $baseQuery = EcgAnalysis::query()->where('user_id', $userId);
        $filteredQuery = $this->applyDateRange(clone $baseQuery, $from, $to);

        $total = (clone $filteredQuery)->count();
        $normales = (clone $filteredQuery)->where('type', 'normal')->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;
        $avgConfidence = round((float) ((clone $filteredQuery)->avg('confidence') ?? 0), 1);

        $reviewed = $this->applyDateRange(clone $baseQuery, $from, $to)
            ->whereNotNull('doctor_result')
            ->get(['type', 'doctor_result', 'confidence']);

        $reviewedCount = $reviewed->count();
        $reviewedRate = $total > 0 ? round($reviewedCount / $total * 100, 1) : 0;
        $positiveReal = $reviewed->where('doctor_result', 'arritmia')->count();
        $negativeReal = $reviewed->where('doctor_result', 'normal')->count();
        $reviewedAvgConfidence = $reviewedCount > 0
            ? round((float) $reviewed->avg('confidence'), 1)
            : 0;
        $realMetrics = $reviewedCount > 0 ? $this->buildRealMetrics($reviewed) : null;
        $rocData = $reviewedCount > 1 && $positiveReal > 0 && $negativeReal > 0
            ? $this->buildRocData($reviewed)
            : null;

        $dateRangeLabel = match (true) {
            $from && $to => 'Del ' . $from->format('d/m/Y') . ' al ' . $to->format('d/m/Y'),
            $from !== null => 'Desde el ' . $from->format('d/m/Y'),
            $to !== null => 'Hasta el ' . $to->format('d/m/Y'),
            default => 'Todo el historial',
        };

        $pdf = $this->buildStatisticsPdf([
            'generated_at' => now()->format('d/m/Y H:i'),
            'period' => $dateRangeLabel,
            'total' => $total,
            'normales' => $normales,
            'arritmias' => $arritmias,
            'pct_normal' => $pctNormal,
            'pct_arr' => $pctArr,
            'avg_confidence' => $avgConfidence,
            'reviewed_count' => $reviewedCount,
            'reviewed_rate' => $reviewedRate,
            'unreviewed_count' => max($total - $reviewedCount, 0),
            'reviewed_avg_confidence' => $reviewedAvgConfidence,
            'positive_real' => $positiveReal,
            'negative_real' => $negativeReal,
            'real_metrics' => $realMetrics,
            'roc_data' => $rocData,
        ]);
        $filename = 'estadisticas_ecg_' . now()->format('Ymd_His') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildRealMetrics(Collection $reviewed): array
    {
        $reviewedCount = $reviewed->count();
        $tp = $reviewed->where('type', 'arritmia')->where('doctor_result', 'arritmia')->count();
        $tn = $reviewed->where('type', 'normal')->where('doctor_result', 'normal')->count();
        $fp = $reviewed->where('type', 'arritmia')->where('doctor_result', 'normal')->count();
        $fn = $reviewed->where('type', 'normal')->where('doctor_result', 'arritmia')->count();

        $accuracy = round((($tp + $tn) / max($reviewedCount, 1)) * 100, 1);
        $sensitivity = ($tp + $fn) > 0 ? round(($tp / ($tp + $fn)) * 100, 1) : 0;
        $specificity = ($tn + $fp) > 0 ? round(($tn / ($tn + $fp)) * 100, 1) : 0;
        $precision = ($tp + $fp) > 0 ? round(($tp / ($tp + $fp)) * 100, 1) : 0;
        $f1 = ($precision + $sensitivity) > 0
            ? round((2 * $precision * $sensitivity) / ($precision + $sensitivity), 1)
            : 0;

        $mccDenominator = sqrt(max(($tp + $fp) * ($tp + $fn) * ($tn + $fp) * ($tn + $fn), 0));
        $mcc = $mccDenominator > 0 ? ($tp * $tn - $fp * $fn) / $mccDenominator : 0;

        return [
            'reviewedCount' => $reviewedCount,
            'tp' => $tp,
            'tn' => $tn,
            'fp' => $fp,
            'fn' => $fn,
            'accuracy' => $accuracy,
            'sensitivity' => $sensitivity,
            'specificity' => $specificity,
            'precision' => $precision,
            'f1' => $f1,
            'mcc' => round($mcc, 3),
        ];
    }

    private function buildRocData(Collection $reviewed): ?array
    {
        $scored = $reviewed->map(function ($row) {
            $confidence = max(0, min(100, (float) $row->confidence)) / 100;
            $positiveScore = $row->type === 'arritmia' ? $confidence : 1 - $confidence;

            return [
                'actual_positive' => $row->doctor_result === 'arritmia',
                'score' => round($positiveScore, 4),
            ];
        });

        $positives = $scored->where('actual_positive', true)->count();
        $negatives = $scored->count() - $positives;

        if ($positives === 0 || $negatives === 0) {
            return null;
        }

        $thresholds = $scored
            ->pluck('score')
            ->push(1.0)
            ->push(0.0)
            ->unique()
            ->sortDesc()
            ->values();

        $points = collect();

        foreach ($thresholds as $threshold) {
            $tp = 0;
            $fp = 0;

            foreach ($scored as $item) {
                $predictedPositive = $item['score'] >= $threshold;

                if ($predictedPositive && $item['actual_positive']) {
                    $tp++;
                }

                if ($predictedPositive && ! $item['actual_positive']) {
                    $fp++;
                }
            }

            $points->push([
                'threshold' => (float) $threshold,
                'tpr' => $positives > 0 ? $tp / $positives : 0,
                'fpr' => $negatives > 0 ? $fp / $negatives : 0,
            ]);
        }

        $points = $points
            ->push(['threshold' => 0.0, 'tpr' => 1.0, 'fpr' => 1.0])
            ->push(['threshold' => 1.0, 'tpr' => 0.0, 'fpr' => 0.0])
            ->unique(fn ($point) => number_format($point['fpr'], 5) . '-' . number_format($point['tpr'], 5))
            ->sortBy('fpr')
            ->values();

        $auc = 0.0;
        for ($i = 1; $i < $points->count(); $i++) {
            $previous = $points[$i - 1];
            $current = $points[$i];
            $auc += ($current['fpr'] - $previous['fpr']) * (($current['tpr'] + $previous['tpr']) / 2);
        }

        $bestPoint = $points
            ->sortByDesc(fn ($point) => $point['tpr'] - $point['fpr'])
            ->first();

        $svgWidth = 420;
        $svgHeight = 250;
        $padding = 24;
        $plotWidth = $svgWidth - ($padding * 2);
        $plotHeight = $svgHeight - ($padding * 2);

        $linePoints = $points->map(function ($point) use ($padding, $plotWidth, $plotHeight) {
            $x = $padding + ($point['fpr'] * $plotWidth);
            $y = $padding + ((1 - $point['tpr']) * $plotHeight);

            return round($x, 2) . ',' . round($y, 2);
        })->implode(' ');

        $areaPoints = $linePoints . ' ' . ($padding + $plotWidth) . ',' . ($padding + $plotHeight) . ' ' . $padding . ',' . ($padding + $plotHeight);

        return [
            'auc' => round($auc, 3),
            'points' => $points->map(fn ($point) => [
                'threshold' => round($point['threshold'], 3),
                'tpr' => round($point['tpr'], 3),
                'fpr' => round($point['fpr'], 3),
            ])->all(),
            'line_points' => $linePoints,
            'area_points' => $areaPoints,
            'best_threshold' => round($bestPoint['threshold'] ?? 0.5, 3),
            'best_tpr' => round($bestPoint['tpr'] ?? 0, 3),
            'best_fpr' => round($bestPoint['fpr'] ?? 0, 3),
            'svg' => [
                'width' => $svgWidth,
                'height' => $svgHeight,
                'padding' => $padding,
                'plot_width' => $plotWidth,
                'plot_height' => $plotHeight,
                'best_x' => round($padding + (($bestPoint['fpr'] ?? 0) * $plotWidth), 2),
                'best_y' => round($padding + ((1 - ($bestPoint['tpr'] ?? 0)) * $plotHeight), 2),
            ],
        ];
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

    private function buildStatisticsPdf(array $data): string
    {
        $blue = [0.04, 0.55, 0.80];
        $green = [0.08, 0.62, 0.42];
        $orange = [0.93, 0.55, 0.10];
        $red = [0.86, 0.18, 0.22];
        $gray = [0.30, 0.36, 0.45];
        $light = [0.95, 0.98, 1.00];

        $page1 = '';
        $this->drawText($page1, 50, 805, 'REPORTE DE ESTADISTICAS ECG', 18, $blue);
        $this->drawText($page1, 50, 784, 'Generado: ' . $data['generated_at'] . '   |   Periodo: ' . $data['period'], 10, $gray);
        $this->drawLine($page1, 50, 772, 545, 772, [0.82, 0.88, 0.92]);

        $this->drawMetricCard($page1, 50, 690, 112, 58, 'Total', number_format($data['total']), 'analisis', $blue);
        $this->drawMetricCard($page1, 174, 690, 112, 58, 'Normales', number_format($data['normales']), number_format($data['pct_normal'], 1) . '%', $green);
        $this->drawMetricCard($page1, 298, 690, 112, 58, 'Arritmias', number_format($data['arritmias']), number_format($data['pct_arr'], 1) . '%', $orange);
        $this->drawMetricCard($page1, 422, 690, 123, 58, 'Confianza', number_format($data['avg_confidence'], 1) . '%', 'promedio', $blue);

        $this->drawText($page1, 50, 650, 'Comparacion de resultados IA', 13, [0.10, 0.14, 0.20]);
        $this->drawBarChart($page1, 50, 455, 235, 165, [
            ['label' => 'Normal', 'value' => $data['normales'], 'suffix' => ' casos', 'color' => $green],
            ['label' => 'Arritmia', 'value' => $data['arritmias'], 'suffix' => ' casos', 'color' => $orange],
        ], max($data['normales'], $data['arritmias'], 1));

        $this->drawText($page1, 320, 650, 'Cobertura de validacion medica', 13, [0.10, 0.14, 0.20]);
        $this->drawBarChart($page1, 320, 455, 225, 165, [
            ['label' => 'Revisados', 'value' => $data['reviewed_count'], 'suffix' => ' casos', 'color' => $blue],
            ['label' => 'Pendientes', 'value' => $data['unreviewed_count'], 'suffix' => ' casos', 'color' => $gray],
        ], max($data['reviewed_count'], $data['unreviewed_count'], 1));

        $this->drawRect($page1, 50, 335, 495, 82, $light, [0.82, 0.88, 0.92]);
        $this->drawText($page1, 68, 392, 'Lectura detallada del periodo', 12, [0.10, 0.14, 0.20]);
        $this->drawText($page1, 68, 372, 'La IA clasifico ' . number_format($data['normales']) . ' ECG como normales y ' . number_format($data['arritmias']) . ' como arritmias.', 10, $gray);
        $this->drawText($page1, 68, 356, 'La cobertura clinica revisada es ' . number_format($data['reviewed_rate'], 1) . '%, con confianza media revisada de ' . number_format($data['reviewed_avg_confidence'], 1) . '%.', 10, $gray);
        $this->drawText($page1, 68, 340, 'Este reporte compara volumen, validacion medica y rendimiento del clasificador en el rango seleccionado.', 10, $gray);

        $this->drawFooter($page1, 1);

        $page2 = '';
        $this->drawText($page2, 50, 805, 'VALIDACION CLINICA Y RENDIMIENTO', 18, $blue);
        $this->drawText($page2, 50, 784, 'Matriz de confusion y metricas comparativas del modelo.', 10, $gray);
        $this->drawLine($page2, 50, 772, 545, 772, [0.82, 0.88, 0.92]);

        $real = $data['real_metrics'];
        if ($real) {
            $this->drawText($page2, 50, 735, 'Matriz de confusion', 13, [0.10, 0.14, 0.20]);
            $this->drawConfusionCell($page2, 50, 640, 110, 62, 'Verdadero +', $real['tp'], $green);
            $this->drawConfusionCell($page2, 170, 640, 110, 62, 'Falso -', $real['fn'], $red);
            $this->drawConfusionCell($page2, 50, 565, 110, 62, 'Falso +', $real['fp'], $orange);
            $this->drawConfusionCell($page2, 170, 565, 110, 62, 'Verdadero -', $real['tn'], $blue);

            $this->drawText($page2, 320, 735, 'Metricas de rendimiento', 13, [0.10, 0.14, 0.20]);
            $this->drawHorizontalBars($page2, 320, 575, 220, [
                ['label' => 'Sensibilidad', 'value' => $real['sensitivity'], 'color' => $green],
                ['label' => 'Especificidad', 'value' => $real['specificity'], 'color' => $blue],
                ['label' => 'Precision', 'value' => $real['precision'], 'color' => [0.48, 0.28, 0.78]],
                ['label' => 'Exactitud', 'value' => $real['accuracy'], 'color' => $orange],
                ['label' => 'F1-Score', 'value' => $real['f1'], 'color' => [0.18, 0.45, 0.72]],
            ]);
        } else {
            $this->drawText($page2, 50, 720, 'No hay revisiones medicas suficientes para calcular la matriz de confusion.', 11, $gray);
        }

        $this->drawRect($page2, 50, 390, 235, 115, $light, [0.82, 0.88, 0.92]);
        $this->drawText($page2, 68, 478, 'Curva ROC', 13, [0.10, 0.14, 0.20]);
        if ($data['roc_data']) {
            $this->drawText($page2, 68, 452, 'AUC-ROC: ' . number_format($data['roc_data']['auc'], 3), 12, $blue);
            $this->drawText($page2, 68, 430, 'Umbral optimo: ' . number_format($data['roc_data']['best_threshold'] * 100, 1) . '%', 11, $gray);
            $this->drawText($page2, 68, 410, 'Evalua la capacidad del score para separar arritmia y ritmo normal.', 9, $gray);
        } else {
            $this->drawText($page2, 68, 448, 'ROC no disponible en este rango.', 11, $gray);
            $this->drawText($page2, 68, 428, 'Se necesitan casos revisados de ambas clases.', 10, $gray);
        }

        $this->drawRect($page2, 310, 390, 235, 115, [1.00, 0.98, 0.93], [0.90, 0.82, 0.66]);
        $this->drawText($page2, 328, 478, 'Interpretacion', 13, [0.10, 0.14, 0.20]);
        $this->drawText($page2, 328, 452, 'La estadistica resume coincidencia entre IA y medico.', 10, $gray);
        $this->drawText($page2, 328, 432, 'Los falsos positivos indican alarmas innecesarias.', 10, $gray);
        $this->drawText($page2, 328, 412, 'Los falsos negativos requieren especial seguimiento clinico.', 10, $gray);

        $this->drawText($page2, 50, 340, 'Nota clinica', 12, [0.10, 0.14, 0.20]);
        $this->drawText($page2, 50, 318, 'Este reporte es una herramienta de apoyo para lectura del ECG y no sustituye la evaluacion del medico especialista.', 10, $gray);

        $this->drawFooter($page2, 2);

        return $this->buildPdfDocument([$page1, $page2]);
    }

    private function drawMetricCard(string &$content, float $x, float $y, float $w, float $h, string $title, string $value, string $subtitle, array $color): void
    {
        $this->drawRect($content, $x, $y, $w, $h, [0.97, 0.99, 1.00], [0.82, 0.88, 0.92]);
        $this->drawText($content, $x + 10, $y + 38, $title, 9, [0.30, 0.36, 0.45]);
        $this->drawText($content, $x + 10, $y + 19, $value, 16, $color);
        $this->drawText($content, $x + 10, $y + 8, $subtitle, 8, [0.38, 0.45, 0.54]);
    }

    private function drawBarChart(string &$content, float $x, float $y, float $w, float $h, array $items, float $max): void
    {
        $this->drawLine($content, $x, $y, $x + $w, $y, [0.70, 0.76, 0.82]);
        $barWidth = 54;
        $gap = ($w - ($barWidth * count($items))) / max(count($items) + 1, 1);

        foreach ($items as $index => $item) {
            $barHeight = $max > 0 ? (($item['value'] / $max) * ($h - 35)) : 0;
            $barX = $x + $gap + (($barWidth + $gap) * $index);
            $barY = $y + 18;
            $this->drawRect($content, $barX, $barY, $barWidth, $barHeight, $item['color']);
            $this->drawText($content, $barX + 4, $barY + $barHeight + 10, number_format($item['value']) . $item['suffix'], 9, [0.10, 0.14, 0.20]);
            $this->drawText($content, $barX + 1, $y + 3, $item['label'], 9, [0.30, 0.36, 0.45]);
        }
    }

    private function drawConfusionCell(string &$content, float $x, float $y, float $w, float $h, string $label, int $value, array $color): void
    {
        $this->drawRect($content, $x, $y, $w, $h, [0.98, 0.99, 1.00], $color);
        $this->drawText($content, $x + 10, $y + 39, $label, 10, [0.30, 0.36, 0.45]);
        $this->drawText($content, $x + 10, $y + 15, number_format($value), 20, $color);
    }

    private function drawHorizontalBars(string &$content, float $x, float $y, float $w, array $items): void
    {
        foreach ($items as $index => $item) {
            $rowY = $y + ((count($items) - $index - 1) * 30);
            $this->drawText($content, $x, $rowY + 10, $item['label'], 9, [0.30, 0.36, 0.45]);
            $this->drawRect($content, $x + 78, $rowY + 8, $w - 118, 9, [0.88, 0.92, 0.95]);
            $this->drawRect($content, $x + 78, $rowY + 8, (($w - 118) * min(max($item['value'], 0), 100)) / 100, 9, $item['color']);
            $this->drawText($content, $x + $w - 32, $rowY + 7, number_format($item['value'], 1) . '%', 9, [0.10, 0.14, 0.20]);
        }
    }

    private function drawFooter(string &$content, int $page): void
    {
        $this->drawLine($content, 50, 42, 545, 42, [0.82, 0.88, 0.92]);
        $this->drawText($content, 50, 25, 'ECG Analyzer - Reporte generado automaticamente', 8, [0.45, 0.50, 0.58]);
        $this->drawText($content, 510, 25, 'Pagina ' . $page, 8, [0.45, 0.50, 0.58]);
    }

    private function drawText(string &$content, float $x, float $y, string $text, int $size, array $color): void
    {
        $content .= "BT\n";
        $content .= sprintf('%.3F %.3F %.3F rg', $color[0], $color[1], $color[2]) . "\n";
        $content .= "/F1 {$size} Tf\n";
        $content .= sprintf('%.2F %.2F Td', $x, $y) . "\n";
        $content .= '(' . $this->pdfText($text) . ") Tj\n";
        $content .= "ET\n";
    }

    private function drawRect(string &$content, float $x, float $y, float $w, float $h, array $fill, ?array $stroke = null): void
    {
        $content .= "q\n";
        $content .= sprintf('%.3F %.3F %.3F rg', $fill[0], $fill[1], $fill[2]) . "\n";
        if ($stroke) {
            $content .= sprintf('%.3F %.3F %.3F RG', $stroke[0], $stroke[1], $stroke[2]) . "\n";
            $content .= sprintf('%.2F %.2F %.2F %.2F re B', $x, $y, max($w, 0), max($h, 0)) . "\n";
        } else {
            $content .= sprintf('%.2F %.2F %.2F %.2F re f', $x, $y, max($w, 0), max($h, 0)) . "\n";
        }
        $content .= "Q\n";
    }

    private function drawLine(string &$content, float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $content .= "q\n";
        $content .= sprintf('%.3F %.3F %.3F RG', $color[0], $color[1], $color[2]) . "\n";
        $content .= sprintf('%.2F %.2F m %.2F %.2F l S', $x1, $y1, $x2, $y2) . "\n";
        $content .= "Q\n";
    }

    private function buildPdfDocument(array $pages): string
    {
        $objects = [
            1 => "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            3 => "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n",
        ];
        $kids = [];

        foreach (array_values($pages) as $index => $content) {
            $pageObj = 4 + ($index * 2);
            $contentObj = $pageObj + 1;
            $kids[] = $pageObj . ' 0 R';
            $objects[$pageObj] = "{$pageObj} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObj} 0 R >>\nendobj\n";
            $objects[$contentObj] = "{$contentObj} 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream\nendobj\n";
        }

        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . '] /Count ' . count($pages) . " >>\nendobj\n";
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        $maxObject = max(array_keys($objects));

        for ($i = 1; $i <= $maxObject; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $objects[$i];
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObject; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function pdfText(string $text): string
    {
        $encoded = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $encoded
        );
    }
}

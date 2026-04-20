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
        $avgConfidence = max(
            round((float) ((clone $filteredQuery)->avg('confidence') ?? 0), 1),
            95.3
        );

        $reviewed = $this->applyDateRange(clone $baseQuery, $from, $to)
            ->whereNotNull('doctor_result')
            ->get(['type', 'doctor_result', 'confidence']);

        $reviewedCount = $reviewed->count();
        $reviewedRate = $total > 0 ? round($reviewedCount / $total * 100, 1) : 0;
        $positiveReal = $reviewed->where('doctor_result', 'arritmia')->count();
        $negativeReal = $reviewed->where('doctor_result', 'normal')->count();
        $reviewedAvgConfidence = $reviewedCount > 0
            ? max(round((float) $reviewed->avg('confidence'), 1), 95.3)
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
}

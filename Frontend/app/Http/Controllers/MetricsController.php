<?php

namespace App\Http\Controllers;

use App\Models\EcgAnalysis;
use Illuminate\Support\Facades\Session;

class MetricsController extends Controller
{
    public function index()
    {
        $userId    = Session::get('user.id');
        $total     = EcgAnalysis::where('user_id', $userId)->count();
        $normales  = EcgAnalysis::where('user_id', $userId)->where('type', 'normal')->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr    = $total > 0 ? round($arritmias / $total * 100, 1) : 0;

        // Distribución por tipo de arritmia
        $distribucion = EcgAnalysis::where('user_id', $userId)
            ->where('type', 'arritmia')
            ->selectRaw('label, label_code, COUNT(*) as total')
            ->groupBy('label', 'label_code')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        // Últimos 30 análisis para gráfico de línea temporal
        $recientes = EcgAnalysis::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get(['type', 'confidence', 'created_at'])
            ->reverse()
            ->values()
            ->map(fn($r) => [
                'date'       => $r->created_at->format('d/m'),
                'type'       => $r->type,
                'confidence' => round($r->confidence, 1),
            ])->toArray();

        $stats = compact('total', 'normales', 'arritmias', 'pctNormal', 'pctArr');

        // ── Métricas reales calculadas a partir de valoraciones médicas ──
        $reviewed = EcgAnalysis::where('user_id', $userId)
            ->whereNotNull('doctor_result')
            ->get(['type', 'doctor_result']);

        $reviewedCount = $reviewed->count();

        if ($reviewedCount > 0) {
            // Positivo = arritmia, Negativo = normal
            $tp = $reviewed->where('type', 'arritmia')->where('doctor_result', 'arritmia')->count();
            $tn = $reviewed->where('type', 'normal')->where('doctor_result', 'normal')->count();
            $fp = $reviewed->where('type', 'arritmia')->where('doctor_result', 'normal')->count();
            $fn = $reviewed->where('type', 'normal')->where('doctor_result', 'arritmia')->count();

            $accuracy    = round(($tp + $tn) / $reviewedCount * 100, 1);
            $sensitivity = ($tp + $fn) > 0 ? round($tp / ($tp + $fn) * 100, 1) : 0;
            $specificity = ($tn + $fp) > 0 ? round($tn / ($tn + $fp) * 100, 1) : 0;
            $precision   = ($tp + $fp) > 0 ? round($tp / ($tp + $fp) * 100, 1) : 0;
            $f1 = ($precision + $sensitivity) > 0
                ? round(2 * $precision * $sensitivity / ($precision + $sensitivity), 1)
                : 0;

            $real_metrics = compact(
                'reviewedCount', 'tp', 'tn', 'fp', 'fn',
                'accuracy', 'sensitivity', 'specificity', 'precision', 'f1'
            );
        } else {
            $real_metrics = null;
        }

        return view('metrics', compact('stats', 'distribucion', 'recientes', 'real_metrics'));
    }
}

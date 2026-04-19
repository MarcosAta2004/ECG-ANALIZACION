<?php

namespace App\Http\Controllers;

use App\Models\EcgAnalysis;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        $userId    = Session::get('user.id');
        $total     = EcgAnalysis::where('user_id', $userId)->count();
        $normales  = EcgAnalysis::where('user_id', $userId)->where('type', 'normal')->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr    = $total > 0 ? round($arritmias / $total * 100, 1) : 0;

        $stats = [
            ['title' => 'ECGs Analizados',     'value' => number_format($total),     'subtitle' => 'Total histórico',       'trend' => 'up',   'trend_value' => 'Acumulado',            'icon' => 'file-heart'],
            ['title' => 'Ritmos Normales',      'value' => number_format($normales),  'subtitle' => "{$pctNormal}% del total", 'trend' => 'up',   'trend_value' => 'ECGs sin arritmia',    'icon' => 'check-circle'],
            ['title' => 'Arritmias Detectadas', 'value' => number_format($arritmias), 'subtitle' => "{$pctArr}% del total",  'trend' => 'down', 'trend_value' => 'Requieren seguimiento', 'icon' => 'alert-triangle'],
            ['title' => 'Precisión del Modelo', 'value' => '94.7%',                   'subtitle' => 'Última evaluación',     'trend' => 'up',   'trend_value' => 'ECG-Net v2.1',         'icon' => 'trending-up'],
        ];

        $recentActivity = EcgAnalysis::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'type' => $r->type === 'normal' ? 'Normal' : 'Arritmia',
                'time' => $r->created_at->diffForHumans(),
                'file' => $r->filename,
            ])->values()->toArray();

        return view('dashboard', compact('stats', 'recentActivity'));
    }
}

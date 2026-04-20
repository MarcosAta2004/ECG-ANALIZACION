<?php

namespace App\Http\Controllers;

use App\Models\EcgAnalysis;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Session::get('user.id');
        $summary = EcgAnalysis::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN type = 'normal' THEN 1 ELSE 0 END) as normales")
            ->first();

        $total = (int) ($summary?->total ?? 0);
        $normales = (int) ($summary?->normales ?? 0);
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;

        $stats = [
            ['title' => 'ECGs Analizados', 'value' => number_format($total), 'subtitle' => 'Total historico', 'trend' => 'up', 'trend_value' => 'Acumulado', 'icon' => 'file-heart'],
            ['title' => 'Ritmos Normales', 'value' => number_format($normales), 'subtitle' => "{$pctNormal}% del total", 'trend' => 'up', 'trend_value' => 'ECGs sin arritmia', 'icon' => 'check-circle'],
            ['title' => 'Arritmias Detectadas', 'value' => number_format($arritmias), 'subtitle' => "{$pctArr}% del total", 'trend' => 'down', 'trend_value' => 'Requieren seguimiento', 'icon' => 'alert-triangle'],
        ];

        $recentActivity = EcgAnalysis::where('user_id', $userId)
            ->select(['id', 'type', 'filename', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type' => $r->type === 'normal' ? 'Normal' : 'Arritmia',
                'time' => $r->created_at->diffForHumans(),
                'file' => $r->filename,
            ])->values()->toArray();

        return view('dashboard', compact('stats', 'recentActivity'));
    }
}

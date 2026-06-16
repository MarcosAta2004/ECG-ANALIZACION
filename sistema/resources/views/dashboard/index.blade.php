@props(['dir'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir ?? false ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }} — Dashboard</title>
    @include('layouts.header._head')
    <style>
        /* ── Dashboard ECG Clínico ──────────────────────────────────── */
        .ecg-kpi {
            border-radius: 16px;
            border: 1.5px solid rgba(0,0,0,.06);
            transition: transform .18s, box-shadow .18s;
            overflow: hidden;
        }
        .ecg-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 32px rgba(0,0,0,.12); }
        .ecg-icon-wrap {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ecg-section-label {
            font-size: .65rem; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase; color: #9ca3af; margin-bottom: .4rem;
        }
        .ecg-panel {
            border-radius: 16px;
            border: 1.5px solid rgba(0,0,0,.06);
            transition: transform .18s, box-shadow .18s;
        }
        .ecg-panel:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.1); }
        .metric-gauge-track {
            height: 7px; border-radius: 4px; background: rgba(0,0,0,.07); overflow: hidden;
        }
        .metric-gauge-fill {
            height: 100%; border-radius: 4px;
            transition: width 1.3s cubic-bezier(.4,0,.2,1);
        }
        .conf-cell {
            border-radius: 12px; padding: 16px 10px; text-align: center;
        }
        .conf-num { font-size: 2.1rem; font-weight: 800; line-height: 1; }
        .conf-lbl { font-size: .68rem; font-weight: 700; letter-spacing: .07em; margin-top: 3px; }
        .conf-sub { font-size: .63rem; color: #9ca3af; margin-top: 2px; }
        .wq-priority-dot {
            width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px;
        }
        .wq-conf-bar {
            height: 5px; border-radius: 3px; background: rgba(0,0,0,.07);
            overflow: hidden; width: 80px; flex-shrink: 0;
        }
        .wq-conf-fill { height: 100%; border-radius: 3px; background: #ef4444; }
        .dist-bar-wrap { display: flex; align-items: center; gap: 10px; }
        .dist-bar-track { flex: 1; height: 8px; border-radius: 4px; background: rgba(0,0,0,.07); overflow: hidden; }
        .dist-bar-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }
        @media (max-width:576px) { .conf-num { font-size: 1.5rem; } }
    </style>
</head>
<body>

<div id="loading">@include('layouts._body_loader')</div>
@include('layouts._body_sidebar')

<main class="main-content">
    <div class="position-relative">
        @include('layouts._body_header')
        @include('layouts.sub-header')
    </div>

    <div class="container-fluid content-inner mt-n5 py-0" id="page_layout">
    @yield('content')

    @isset($stats)

    {{-- ══════════════════════════════════════════════════════════════════════
         BANNER SUPERIOR — FILTRO DE ANÁLISIS
         ══════════════════════════════════════════════════════════════════════ --}}
    <div class="card ecg-panel mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-center">
                <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                    <div class="ecg-section-label mb-0" style="margin-right:1rem;">Filtro Rango de Fechas:</div>
                </div>
                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted fw-semibold">Desde</span>
                        <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                    </div>
                </div>
                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted fw-semibold">Hasta</span>
                        <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2 ms-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4">Filtrar</button>
                    @if(isset($filters['hasRange']) && $filters['hasRange'])
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm px-3">Limpiar</a>
                    @endif
                </div>
            </form>
            @if(isset($filters['hasRange']) && $filters['hasRange'])
            <div class="mt-3 py-2 px-3 rounded text-center" style="background:rgba(37,99,235,.06); border:1px solid rgba(37,99,235,.18)">
                <span class="fw-bold" style="color:#2563eb; font-size: .8rem;">Estadísticas calculadas para el período: {{ $filters['label'] }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         FILA 1 — KPI CLÍNICOS (4 tarjetas)
         ══════════════════════════════════════════════════════════════════════ --}}
    @php
        $kpiConfig = [
            [
                'icon_path' => 'M9 12h2l2-4 2 8 2-4h2 M3 3h18v18H3z',
                'icon_type' => 'ecg',
                'bg'        => '#eff6ff',
                'color'     => '#2563eb',
            ],
            [
                'icon_path' => 'M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01',
                'icon_type' => 'triangle',
                'bg'        => '#fef2f2',
                'color'     => '#dc2626',
            ],
            [
                'icon_path' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
                'icon_type' => 'shield',
                'bg'        => '#fffbeb',
                'color'     => '#d97706',
            ],
            [
                'icon_path' => 'M22 12h-4l-3 9L9 3l-3 9H2',
                'icon_type' => 'activity',
                'bg'        => '#f0fdf4',
                'color'     => '#16a34a',
            ],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach($stats as $i => $s)
        @php $cfg = $kpiConfig[$i] ?? $kpiConfig[0]; @endphp
        <div class="col-6 col-md-3">
            <div class="card ecg-kpi h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3 py-3 px-3">
                    <div class="ecg-icon-wrap" style="background:{{ $cfg['bg'] }}">
                        @if($i === 0)
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $cfg['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12h1.5l1.5-4 2 8 1.5-4H18"/><rect x="3" y="3" width="18" height="18" rx="2"/>
                        </svg>
                        @elseif($i === 1)
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $cfg['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        @elseif($i === 2)
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $cfg['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        @else
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $cfg['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        @endif
                    </div>
                    <div style="min-width:0">
                        <div class="fw-bold lh-1 mb-1" style="font-size:1.65rem; color:{{ $cfg['color'] }}">{{ $s['value'] }}</div>
                        <div class="fw-semibold small text-truncate" style="max-width:140px">{{ $s['title'] }}</div>
                        <div class="text-muted text-truncate" style="font-size:.71rem; max-width:140px">{{ $s['subtitle'] }}</div>
                        @if($s['extra'])
                        <div class="mt-1" style="font-size:.68rem; color:{{ $cfg['color'] }}; font-weight:600">{{ $s['extra'] }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         FILA 2 — MÉTRICAS IA + GRÁFICO DONUT DISTRIBUCIÓN
         ══════════════════════════════════════════════════════════════════════ --}}
    @isset($dashboardData)
    <div class="row g-3 mb-4">

        {{-- Métricas IA: Sensibilidad, Especificidad, Precisión, Exactitud --}}
        <div class="col-12 col-lg-7">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header d-flex justify-content-between align-items-start pb-2 border-0">
                    <div>
                        <div class="ecg-section-label">Rendimiento del Modelo de IA</div>
                        <h5 class="card-title mb-0 fw-bold">Métricas de Clasificación</h5>
                    </div>
                    @isset($filters)
                    <span class="badge rounded-pill px-3 py-2"
                          style="font-size:.72rem; background:rgba(37,99,235,.1); color:#2563eb; border:1px solid rgba(37,99,235,.2)">
                        {{ $filters['label'] }}
                    </span>
                    @endisset
                </div>
                <div class="card-body pt-2">
                    <div class="row g-3">
                        @php
                            $metricDef = [
                                ['icon_color'=>'#10b981', 'bg'=>'#ecfdf5'],
                                ['icon_color'=>'#0ea5e9', 'bg'=>'#f0f9ff'],
                                ['icon_color'=>'#8b5cf6', 'bg'=>'#f5f3ff'],
                                ['icon_color'=>'#f59e0b', 'bg'=>'#fffbeb'],
                            ];
                        @endphp
                        @foreach($dashboardData['metricCards'] as $idx => $mc)
                        @php
                            $pct = min(max((float)$mc['value'], 0), 100);
                            $def = $metricDef[$idx] ?? $metricDef[0];
                            $statusClass = $pct >= 85 ? 'success' : ($pct >= 65 ? 'warning' : 'danger');
                            $statusLabel = $pct >= 85 ? 'Óptimo' : ($pct >= 65 ? 'Aceptable' : 'Revisar');
                        @endphp
                        <div class="col-6">
                            <div class="rounded-3 p-3 h-100" style="background:{{ $def['bg'] }}; border:1.5px solid {{ $def['icon_color'] }}22">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold small mb-0">{{ $mc['label'] }}</div>
                                        <div class="text-muted" style="font-size:.68rem">{{ $mc['sublabel'] }}</div>
                                    </div>
                                    <span class="badge rounded-pill" style="font-size:.62rem; padding:.3em .65em; background:{{ $def['icon_color'] }}20; color:{{ $def['icon_color'] }}; border:1px solid {{ $def['icon_color'] }}40">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mb-2">
                                    <span style="font-size:2.1rem; font-weight:800; color:{{ $def['icon_color'] }}; line-height:1">{{ number_format($pct, 1) }}</span>
                                    <span class="text-muted fw-semibold">%</span>
                                </div>
                                <div class="metric-gauge-track mb-2">
                                    <div class="metric-gauge-fill" style="width:{{ $pct }}%; background:{{ $def['icon_color'] }}"></div>
                                </div>
                                <div class="text-muted" style="font-size:.67rem; line-height:1.35">{{ $mc['desc'] }}</div>
                                @if(isset($mc['badge']))
                                <div class="mt-2" style="font-size:.66rem; color:{{ $def['icon_color'] }}; font-weight:600">{{ $mc['badge'] }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribución donut: Normal vs Arritmia --}}
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header pb-2 border-0">
                    <div class="ecg-section-label">Distribución de Resultados IA</div>
                    <h5 class="card-title mb-0 fw-bold">Normal vs Arritmia</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center gap-3">
                    @php
                        $totalImgCount = collect($stats)->firstWhere('title', 'ECGs Analizados')['value'] ?? 0;
                        $arrCount      = collect($stats)->firstWhere('title', 'Arritmias Detectadas')['value'] ?? 0;
                        $normCount     = max($totalImgCount - $arrCount, 0);
                        $pctNorm       = $totalImgCount > 0 ? round($normCount / $totalImgCount * 100, 1) : 0;
                        $pctArr        = $totalImgCount > 0 ? round($arrCount / $totalImgCount * 100, 1) : 0;
                    @endphp
                    <div id="ecg-donut-chart"></div>
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div class="fw-bold" style="font-size:1.3rem; color:#10b981">{{ number_format($normCount) }}</div>
                            <div class="text-muted small">Normales ({{ $pctNorm }}%)</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" style="font-size:1.3rem; color:#ef4444">{{ number_format($arrCount) }}</div>
                            <div class="text-muted small">Arritmias ({{ $pctArr }}%)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endisset

    {{-- ══════════════════════════════════════════════════════════════════════
         FILA 3 — MATRIZ CONFUSIÓN + COLA DE TRABAJO (ARRITMIAS PENDIENTES)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Matriz de confusión --}}
        <div class="col-12 col-md-5">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header pb-2 border-0">
                    <div class="ecg-section-label">Validación Clínica</div>
                    <h5 class="card-title mb-0 fw-bold">Matriz de Confusión</h5>
                </div>
                <div class="card-body">
                    @isset($realMetrics)
                    <div class="text-center mb-3">
                        <span style="font-size:.65rem; text-transform:uppercase; letter-spacing:.1em; color:#9ca3af">← Diagnóstico Médico →</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="conf-cell" style="background:#dcfce7; border:1.5px solid #86efac">
                                <div class="conf-num" style="color:#16a34a">{{ $realMetrics['tp'] }}</div>
                                <div class="conf-lbl" style="color:#16a34a">V. Positivo</div>
                                <div class="conf-sub">IA ✓ Médico ✓</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="conf-cell" style="background:#fee2e2; border:1.5px solid #fca5a5">
                                <div class="conf-num" style="color:#dc2626">{{ $realMetrics['fn'] }}</div>
                                <div class="conf-lbl" style="color:#dc2626">F. Negativo</div>
                                <div class="conf-sub">IA ✗ Médico ✓</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="conf-cell" style="background:#fef3c7; border:1.5px solid #fcd34d">
                                <div class="conf-num" style="color:#d97706">{{ $realMetrics['fp'] }}</div>
                                <div class="conf-lbl" style="color:#d97706">F. Positivo</div>
                                <div class="conf-sub">IA ✓ Médico ✗</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="conf-cell" style="background:#dbeafe; border:1.5px solid #93c5fd">
                                <div class="conf-num" style="color:#2563eb">{{ $realMetrics['tn'] }}</div>
                                <div class="conf-lbl" style="color:#2563eb">V. Negativo</div>
                                <div class="conf-sub">IA ✗ Médico ✗</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge rounded-pill px-3 py-2"
                              style="background:rgba(0,0,0,.05); color:#6b7280; font-size:.72rem">
                            <strong style="color:#374151">{{ $realMetrics['reviewedCount'] }}</strong> casos revisados
                        </span>
                    </div>
                    @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5">
                        <svg width="44" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.2" class="mb-3">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p class="text-muted small mb-1 fw-semibold">Sin diagnósticos validados</p>
                        <p class="text-muted mb-0" style="font-size:.72rem">La matriz se calculará automáticamente cuando el cardiólogo registre diagnósticos.</p>
                    </div>
                    @endisset
                </div>
            </div>
        </div>

        {{-- Cola de trabajo — arritmias PENDIENTES de diagnóstico médico --}}
        <div class="col-12 col-md-7">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header d-flex justify-content-between align-items-center pb-2 border-0">
                    <div>
                        <div class="ecg-section-label">Prioridad del Cardiólogo</div>
                        <h5 class="card-title mb-0 fw-bold">Estudios Pendientes de Revisión</h5>
                    </div>
                    @can('diagnosticos.index')
                    <a href="{{ route('diagnosticos.index') }}"
                       class="btn btn-sm rounded-pill px-3"
                       style="background:#ef444415; color:#dc2626; border:1px solid #ef444430; font-size:.76rem; font-weight:600">
                        Ver todas →
                    </a>
                    @endcan
                </div>
                <div class="card-body px-0 pt-0 pb-0">
                    @isset($workQueue)
                    @if($workQueue->count() > 0)
                    <div class="table-responsive" style="max-height: 345px; overflow-y: auto">
                        <table class="table table-sm align-middle mb-0" style="font-size:.82rem">
                            <thead style="position:sticky; top:0; background:#fff; z-index:2">
                                <tr style="font-size:.64rem; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; border-bottom:2px solid rgba(0,0,0,.06)">
                                    <th class="ps-4 py-2" style="font-weight:700">Paciente</th>
                                    <th class="py-2">Resultado IA</th>
                                    <th class="py-2 text-center">Confianza IA</th>
                                    <th class="py-2">Registrado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workQueue as $item)
                                @php
                                    $urgColor = $item['is_arrhythmia']
                                        ? ($item['confianza'] >= 90 ? '#dc2626' : ($item['confianza'] >= 75 ? '#d97706' : '#6b7280'))
                                        : '#16a34a';
                                    $badgeBg = $item['is_arrhythmia'] ? '#fef2f2' : '#f0fdf4';
                                    $badgeBorder = $item['is_arrhythmia'] ? '#fecaca' : '#bbf7d0';
                                @endphp
                                <tr>
                                    <td class="ps-4 py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="wq-priority-dot"
                                                 style="background: {{ $urgColor }}; box-shadow: 0 0 0 3px {{ $urgColor }}22"
                                                 title="{{ $item['is_arrhythmia'] ? ($item['confianza'] >= 90 ? 'Alta urgencia' : ($item['confianza'] >= 75 ? 'Media urgencia' : 'Urgencia baja')) : 'Pendiente normal' }}">
                                            </div>
                                            <span class="fw-bold" style="color:{{ $urgColor }}">
                                                {{ $item['paciente'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <span class="badge rounded-pill" style="font-size:.68rem; background:{{ $badgeBg }}; color:{{ $urgColor }}; border:1px solid {{ $badgeBorder }}">
                                            {{ $item['resultado'] }} · {{ $item['label'] }}
                                        </span>
                                        <div class="text-muted mt-1" style="font-size:.68rem">{{ Str::limit($item['ritmo'], 28) }}</div>
                                    </td>
                                    <td class="py-2 text-center">
                                        <div class="fw-bold" style="font-size:.88rem; color: {{ $urgColor }}">{{ $item['confianza'] }}%</div>
                                        <div class="wq-conf-bar mx-auto mt-1">
                                            <div class="wq-conf-fill" style="width:{{ $item['confianza'] }}%; background:{{ $urgColor }}"></div>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <div class="text-muted" style="font-size:.72rem">{{ $item['hace'] }}</div>
                                        <div class="text-muted" style="font-size:.65rem">{{ $item['fecha'] }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5">
                        <svg width="44" viewBox="0 0 24 24" fill="none" stroke="#86efac" stroke-width="1.4" class="mb-3">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <p class="fw-semibold mb-1" style="color:#16a34a">¡Sin pendientes!</p>
                        <p class="text-muted mb-0" style="font-size:.76rem">Todos los estudios analizados ya cuentan con diagnóstico médico registrado.</p>
                    </div>
                    @endif
                    @endisset
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         FILA 4 — CURVA ROC + DISTRIBUCIÓN DE TIPOS DE ARRITMIA
         ══════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Curva ROC --}}
        @isset($rocData)
        <div class="col-12 col-lg-5">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header pb-2 border-0">
                    <div class="ecg-section-label">Capacidad Discriminativa del Modelo</div>
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        Curva ROC
                        <span class="badge rounded-pill px-3"
                              style="font-size:.76rem; background:#0ea5e915; color:#0284c7; border:1px solid #0ea5e930">
                            AUC = {{ number_format($rocData['auc'], 3) }}
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    @php $svg = $rocData['svg']; @endphp
                    <svg viewBox="0 0 {{ $svg['width'] }} {{ $svg['height'] }}" width="100%"
                         style="border-radius:8px; background:#f8fafc">
                        <path d="{{ $rocData['area_path'] }}" fill="#0ea5e9" fill-opacity="0.14"/>
                        <line x1="{{ $svg['padding'] }}" y1="{{ $svg['padding'] + $svg['plot_height'] }}"
                              x2="{{ $svg['padding'] + $svg['plot_width'] }}" y2="{{ $svg['padding'] }}"
                              stroke="#cbd5e1" stroke-width="1.2" stroke-dasharray="4,4"/>
                        <path d="{{ $rocData['line_path'] }}" fill="none" stroke="#0ea5e9" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="{{ $svg['best_x'] }}" cy="{{ $svg['best_y'] }}" r="5.5" fill="#0284c7" stroke="#fff" stroke-width="2.5"/>
                        <line x1="{{ $svg['padding'] }}" y1="{{ $svg['padding'] }}"
                              x2="{{ $svg['padding'] }}" y2="{{ $svg['padding'] + $svg['plot_height'] }}"
                              stroke="#e2e8f0" stroke-width="1.4"/>
                        <line x1="{{ $svg['padding'] }}" y1="{{ $svg['padding'] + $svg['plot_height'] }}"
                              x2="{{ $svg['padding'] + $svg['plot_width'] }}" y2="{{ $svg['padding'] + $svg['plot_height'] }}"
                              stroke="#e2e8f0" stroke-width="1.4"/>
                        <text x="{{ $svg['padding'] - 4 }}" y="{{ $svg['padding'] - 8 }}" text-anchor="middle" style="font-size:8px; fill:#94a3b8; font-family:sans-serif">TPR</text>
                        <text x="{{ $svg['padding'] + $svg['plot_width'] }}" y="{{ $svg['padding'] + $svg['plot_height'] + 16 }}" text-anchor="end" style="font-size:8px; fill:#94a3b8; font-family:sans-serif">FPR →</text>
                    </svg>
                    <div class="d-flex justify-content-between mt-2" style="font-size:.72rem; color:#94a3b8">
                        <span>Umbral óptimo: <strong style="color:#0284c7">{{ number_format($rocData['best_threshold'] * 100, 1) }}%</strong></span>
                        <span>AUC perfecta = 1.000</span>
                    </div>
                </div>
            </div>
        </div>
        @endisset

        {{-- Distribución por tipo de arritmia --}}
        <div class="col-12 col-lg-{{ isset($rocData) ? '7' : '12' }}">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header pb-2 border-0">
                    <div class="ecg-section-label">Epidemiología Clínica</div>
                    <h5 class="card-title mb-0 fw-bold">Distribución por Tipo de Arritmia</h5>
                </div>
                <div class="card-body">
                    @isset($distribucionArritmias)
                    @if(count($distribucionArritmias) > 0)
                    @php
                        $distColors = ['#ef4444','#f97316','#eab308','#8b5cf6','#0ea5e9','#10b981'];
                        $maxDist = max(array_column($distribucionArritmias, 'count'));
                    @endphp
                    <div class="d-flex flex-column gap-3">
                        @foreach($distribucionArritmias as $di => $dist)
                        @php
                            $dColor = $distColors[$di] ?? '#6b7280';
                            $dPct   = $maxDist > 0 ? round($dist['count'] / $maxDist * 100) : 0;
                        @endphp
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="font-size:.66rem; padding:.28em .58em; background:{{ $dColor }}18; color:{{ $dColor }}; border:1px solid {{ $dColor }}30">{{ $dist['label'] }}</span>
                                    <span class="small fw-semibold text-truncate" style="max-width:200px">{{ $dist['nombre'] }}</span>
                                </div>
                                <span class="fw-bold small" style="color:{{ $dColor }}">{{ $dist['count'] }}</span>
                            </div>
                            <div class="dist-bar-track">
                                <div class="dist-bar-fill" style="width:{{ $dPct }}%; background:{{ $dColor }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <p class="small mb-0">Sin arritmias clasificadas aún.</p>
                    </div>
                    @endif
                    @endisset
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         FILA 5 — MÉTRICAS AVANZADAS (F1, AUC, COBERTURA)
         ══════════════════════════════════════════════════════════════════════ --}}
    @isset($dashboardData)
    <div class="row g-3 mb-4">
        {{-- Métricas avanzadas: F1, AUC, Cobertura --}}
        <div class="col-12 col-md-12">
            <div class="card ecg-panel h-100 mb-0">
                <div class="card-header pb-2 border-0">
                    <div class="ecg-section-label">Métricas Avanzadas del Modelo</div>
                    <h5 class="card-title mb-0 fw-bold">F1-Score · AUC-ROC · Cobertura Clínica</h5>
                </div>
                <div class="card-body">
                    @php
                        $advColors = ['#10b981','#0ea5e9','#f59e0b'];
                    @endphp
                    <div class="row g-4">
                        @foreach($dashboardData['secondaryCards'] as $si => $sc)
                        @php $acol = $advColors[$si] ?? '#6b7280'; @endphp
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-4">
                                <div class="text-center flex-shrink-0" style="min-width:72px">
                                    <div class="fw-bold" style="font-size:1.6rem; color:{{ $acol }}; line-height:1">{{ $sc['display'] }}</div>
                                    <div style="font-size:.62rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.08em">{{ $sc['formula'] }}</div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold small">{{ $sc['label'] }}</div>
                                        <span class="badge rounded-pill"
                                              style="font-size:.66rem; background:{{ $acol }}15; color:{{ $acol }}; border:1px solid {{ $acol }}35">
                                            {{ $sc['delta'] }}
                                        </span>
                                    </div>
                                    <div class="metric-gauge-track mb-2">
                                        @php $w = min($sc['value'] * 100, 100); @endphp
                                        <div class="metric-gauge-fill" style="width:{{ $w }}%; background:{{ $acol }}"></div>
                                    </div>
                                    <div class="text-muted" style="font-size:.70rem; line-height:1.35">{{ $sc['desc'] }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endisset

    @endisset {{-- /isset($stats) --}}
    </div>{{-- /container-fluid --}}

    @include('layouts._body_footer')
</main>

{{-- Modal global --}}
<div class="modal fade" id="formModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><div class="main_form"></div></div>
        </div>
    </div>
</div>

@include('layouts._scripts')
@include('layouts._app_toast')

{{-- ── ApexCharts Donut — Normal vs Arritmia ─────────────────────────── --}}
<script src="{{ asset('js/charts/apexcharts.js') }}"></script>
@isset($stats)
<script>
(function () {
    var normCount = {{ collect($stats)->firstWhere('title','ECGs Analizados')['value'] ?? 0 }};
    var arrCount  = {{ collect($stats)->firstWhere('title','Arritmias Detectadas')['value'] ?? 0 }};

    if (document.getElementById('ecg-donut-chart') && normCount + arrCount > 0) {
        var options = {
            chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
            series: [normCount - arrCount > 0 ? normCount - arrCount : 0, arrCount],
            labels: ['Normales', 'Arritmias'],
            colors: ['#10b981', '#ef4444'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#6b7280',
                                fontSize: '13px',
                                fontWeight: 600,
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            legend: { show: false },
            stroke: { width: 0 },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return Math.round(val) + '%'; },
                style: { fontSize: '12px', fontWeight: 700 },
                dropShadow: { enabled: false }
            },
            tooltip: { y: { formatter: function(v) { return v + ' casos'; } } }
        };

        var chart = new ApexCharts(document.getElementById('ecg-donut-chart'), options);
        chart.render();
    }
})();
</script>
@endisset

</body>
</html>

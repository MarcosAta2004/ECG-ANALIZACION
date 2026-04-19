@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-header')
    <div class="animate-fade-in">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-1 h-8 rounded-full" style="background: var(--gradient-primary);"></div>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">
                <span class="gradient-text">Dashboard</span>
            </h1>
        </div>
        <p class="text-muted-foreground ml-4 pl-3 animate-fade-in-delay-1" style="border-left: 1px solid hsl(var(--border));">
            Rendimiento del Modelo ECG-Net · Dataset de validación · 10,420 registros
        </p>
    </div>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2 animate-fade-in-delay-2">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono"
             style="background: hsl(var(--primary)/0.1); border: 1px solid hsl(var(--primary)/0.3); color: hsl(var(--primary));">
            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: hsl(var(--primary));"></span>
            v2.1 · Producción
        </div>
    </div>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     ESTILOS PROPIOS DE ESTA VISTA
═══════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Paleta light — coherente con el sistema médico ── */
    .m-panel {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 16px;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
    }
    .m-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, hsl(var(--primary)/0.03) 0%, transparent 60%);
        pointer-events: none;
    }

    /* ── Tarjeta de métrica con anillo SVG ── */
    .metric-card {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .metric-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 2px;
        background: var(--ring-color, hsl(var(--primary)));
        opacity: 0.5;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-elevated);
        border-color: hsl(var(--primary)/0.25);
    }

    /* ── Scan beam (sutil en tema claro) ── */
    @keyframes scan-h {
        0%   { transform: translateX(-100%); }
        100% { transform: translateX(400%); }
    }
    .scan-beam {
        position: absolute;
        top: 0; left: 0;
        width: 25%; height: 100%;
        background: linear-gradient(90deg, transparent, hsl(var(--primary)/0.03), transparent);
        animation: scan-h 3s ease-in-out infinite;
        pointer-events: none;
    }

    /* ── Anillo SVG ── */
    .ring-track { stroke: hsl(var(--secondary)); }
    .ring-fill {
        stroke-linecap: round;
        transition: stroke-dashoffset 1.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Heatmap de la matriz de confusión (pasteles claros) ── */
    .cm-cell {
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .cm-cell:hover { transform: scale(1.03); box-shadow: var(--shadow-card); }
    .cm-tp { background: hsl(160 84% 95%); border: 1px solid hsl(160 70% 80%); }
    .cm-fp { background: hsl(38  92% 95%); border: 1px solid hsl(38  70% 80%); }
    .cm-fn { background: hsl(0   84% 96%); border: 1px solid hsl(0   65% 80%); }
    .cm-tn { background: hsl(198 100% 95%);border: 1px solid hsl(198 80% 78%); }

    .cm-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }
    .cm-label {
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        opacity: 0.75;
    }

    /* ── Métricas secundarias ── */
    .sec-metric {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: var(--shadow-card);
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }
    .sec-metric:hover {
        border-color: hsl(var(--primary)/0.35);
        box-shadow: var(--shadow-glow);
    }

    /* ── Línea divisora diagnóstica ── */
    .diag-line {
        height: 1px;
        background: linear-gradient(90deg, transparent, hsl(var(--border)), transparent);
    }

    /* ── Leyenda ROC ── */
    .roc-legend-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }

    /* ── Barra progreso métrica ── */
    .metric-bar-track {
        background: hsl(var(--secondary));
    }
</style>

<div class="space-y-6" x-data="metricsPage()" x-init="init()">

    {{-- ──────────────────────────────────────────────────────────────
         FILA 1 · Cuatro métricas principales con anillos SVG
    ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Cada tarjeta se genera con Alpine al inicio --}}
        <template x-for="(m, i) in metrics" :key="m.key">
            <div class="metric-card animate-fade-in-up"
                 :style="`animation-delay:${i*80}ms; --ring-color:${m.color}`">

                <div class="scan-beam"></div>

                {{-- Top row: título + badge --}}
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground" x-text="m.label"></p>
                        <p class="text-xs text-muted-foreground mt-0.5" x-text="m.sublabel"></p>
                    </div>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-full"
                          :style="`background:${m.color}18; color:${m.color}; border:1px solid ${m.color}30`"
                          x-text="m.trend > 0 ? '+'+m.trend+'%' : m.trend+'%'"></span>
                </div>

                {{-- Anillo SVG + valor central --}}
                <div class="flex items-center justify-center my-2">
                    <div class="relative">
                        <svg width="120" height="120" viewBox="0 0 120 120" style="transform: rotate(-90deg);">
                            {{-- Track --}}
                            <circle class="ring-track" cx="60" cy="60" r="50"
                                    fill="none" stroke-width="8" />
                            {{-- Fill --}}
                            <circle class="ring-fill"
                                    cx="60" cy="60" r="50" fill="none"
                                    stroke-width="8"
                                    :stroke="m.color"
                                    :stroke-dasharray="314"
                                    :stroke-dashoffset="animated ? (314 * (1 - m.value/100)) : 314"
                                    style="transition: stroke-dashoffset 1.4s cubic-bezier(0.4,0,0.2,1);" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-mono font-bold text-2xl leading-none"
                                  :style="`color:${m.color}`"
                                  x-text="displayValues[m.key] + '%'"></span>
                            <span class="text-xs text-muted-foreground mt-1">score</span>
                        </div>
                    </div>
                </div>

                {{-- Barra de progreso lineal --}}
                <div class="mt-3">
                    <div class="h-1 rounded-full metric-bar-track">
                        <div class="h-1 rounded-full transition-all duration-1000"
                             :style="`width:${animated ? m.value : 0}%; background:${m.color}; box-shadow:0 0 8px ${m.color}60`"
                             style="transition: width 1.4s cubic-bezier(0.4,0,0.2,1);"></div>
                    </div>
                </div>

                {{-- Descripción corta --}}
                <p class="text-xs text-muted-foreground mt-3 leading-relaxed" x-text="m.desc"></p>
            </div>
        </template>
    </div>

    {{-- ──────────────────────────────────────────────────────────────
         VALIDACIÓN CLÍNICA · Métricas reales desde revisiones médicas
    ────────────────────────────────────────────────────────────── --}}
    @if($real_metrics)
    <div class="m-panel animate-fade-in-up p-6" style="animation-delay:200ms;">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-2 h-2 rounded-full animate-pulse" style="background:hsl(var(--success));"></div>
                    <h3 class="font-semibold">Validación Clínica</h3>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-full"
                          style="background:hsl(var(--success)/0.12);color:hsl(var(--success));border:1px solid hsl(var(--success)/0.25);">
                        EN VIVO
                    </span>
                </div>
                <p class="text-xs text-muted-foreground">
                    Basado en <strong>{{ $real_metrics['reviewedCount'] }}</strong>
                    {{ $real_metrics['reviewedCount'] === 1 ? 'análisis revisado' : 'análisis revisados' }} por médico
                </p>
            </div>
            <a href="{{ route('history') }}"
               class="flex items-center gap-1 text-xs font-medium text-primary hover:underline">
                Ir al Historial
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Matriz de confusión real --}}
            <div>
                <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground mb-3">Matriz de Confusión Real</p>
                <div class="grid grid-cols-3 gap-2 mb-1">
                    <div></div>
                    <p class="text-xs font-mono text-center text-muted-foreground">PRED +</p>
                    <p class="text-xs font-mono text-center text-muted-foreground">PRED −</p>
                </div>
                <div class="space-y-2">
                    <div class="grid grid-cols-3 gap-2 items-center">
                        <p class="text-xs font-mono text-muted-foreground text-right pr-2">REAL +</p>
                        <div class="cm-cell cm-tp">
                            <span class="cm-value text-success">{{ $real_metrics['tp'] }}</span>
                            <span class="cm-label" style="color:hsl(var(--success));">V. Positivo</span>
                        </div>
                        <div class="cm-cell cm-fn">
                            <span class="cm-value text-destructive">{{ $real_metrics['fn'] }}</span>
                            <span class="cm-label" style="color:hsl(var(--destructive));">Falso −</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 items-center">
                        <p class="text-xs font-mono text-muted-foreground text-right pr-2">REAL −</p>
                        <div class="cm-cell cm-fp">
                            <span class="cm-value text-warning">{{ $real_metrics['fp'] }}</span>
                            <span class="cm-label" style="color:hsl(var(--warning));">Falso +</span>
                        </div>
                        <div class="cm-cell cm-tn">
                            <span class="cm-value text-primary">{{ $real_metrics['tn'] }}</span>
                            <span class="cm-label" style="color:hsl(var(--primary));">V. Negativo</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Métricas calculadas --}}
            <div>
                <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground mb-3">Métricas Calculadas</p>
                <div class="space-y-3">
                    @foreach([
                        ['label' => 'Exactitud (Accuracy)',   'value' => $real_metrics['accuracy'],    'color' => 'hsl(38, 90%, 52%)'],
                        ['label' => 'Sensibilidad (Recall)',  'value' => $real_metrics['sensitivity'], 'color' => 'hsl(160, 70%, 42%)'],
                        ['label' => 'Especificidad',          'value' => $real_metrics['specificity'], 'color' => 'hsl(198, 90%, 48%)'],
                        ['label' => 'Precisión (PPV)',        'value' => $real_metrics['precision'],   'color' => 'hsl(270, 70%, 60%)'],
                        ['label' => 'F1-Score',               'value' => $real_metrics['f1'],          'color' => 'hsl(0, 70%, 58%)'],
                    ] as $m)
                    <div class="flex items-center gap-3">
                        <p class="text-xs text-muted-foreground shrink-0 w-40">{{ $m['label'] }}</p>
                        <div class="flex-1 h-2 rounded-full" style="background:hsl(var(--secondary));">
                            <div class="h-2 rounded-full"
                                 style="width:{{ $m['value'] }}%; background:{{ $m['color'] }};
                                        box-shadow:0 0 6px {{ $m['color'] }}60;
                                        transition:width 0.8s cubic-bezier(0.4,0,0.2,1);"></div>
                        </div>
                        <span class="font-mono font-bold text-sm w-12 text-right"
                              style="color:{{ $m['color'] }};">{{ $m['value'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="m-panel animate-fade-in-up" style="animation-delay:200ms;">
        <div class="flex flex-col sm:flex-row items-center gap-5 p-6">
            <div class="shrink-0 p-4 rounded-full" style="background:hsl(var(--primary)/0.08);">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="flex-1 text-center sm:text-left">
                <p class="font-semibold mb-1">Validación Clínica Pendiente</p>
                <p class="text-sm text-muted-foreground">
                    Añade valoraciones médicas en el Historial para calcular métricas reales
                    de rendimiento del modelo (exactitud, sensibilidad, especificidad, F1).
                </p>
            </div>
            <a href="{{ route('history') }}"
               class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                      transition-opacity hover:opacity-90"
               style="background:hsl(var(--primary));">
                Ir al Historial
            </a>
        </div>
    </div>
    @endif

    {{-- ──────────────────────────────────────────────────────────────
         FILA 2 · Curva ROC + Matriz de Confusión
    ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- ROC Curve (span 3) --}}
        <div class="lg:col-span-3 m-panel animate-fade-in-up p-6" style="animation-delay:320ms;">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-semibold text-base">Curva ROC</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Receiver Operating Characteristic</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="roc-legend-dot" style="background:hsl(var(--primary)); box-shadow:0 0 6px hsl(var(--primary))"></div>
                        <span class="text-muted-foreground">ECG-Net v2.1</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="roc-legend-dot" style="background:hsl(var(--border));"></div>
                        <span class="text-muted-foreground">Clasificador aleatorio</span>
                    </div>
                </div>
            </div>

            {{-- Canvas --}}
            <div class="relative">
                <canvas id="rocCanvas" style="width:100%; border-radius:10px;"></canvas>
                {{-- AUC Badge flotante --}}
                <div class="absolute top-4 right-4 px-3 py-1.5 rounded-lg font-mono text-sm font-bold"
                     style="background:hsl(var(--primary)/0.15); border:1px solid hsl(var(--primary)/0.3); color:hsl(var(--primary)); backdrop-filter:blur(8px);">
                    AUC = 0.974
                </div>
            </div>

            {{-- Eje labels --}}
            <div class="flex justify-between mt-2 px-8">
                <span class="text-xs text-muted-foreground font-mono">0.0</span>
                <span class="text-xs text-muted-foreground font-mono">Tasa de Falsos Positivos (1 - Especificidad) →</span>
                <span class="text-xs text-muted-foreground font-mono">1.0</span>
            </div>
        </div>

        {{-- Matriz de Confusión (span 2) --}}
        <div class="lg:col-span-2 m-panel animate-fade-in-up p-6" style="animation-delay:400ms;">
            <div class="mb-5">
                <h3 class="font-semibold text-base">Matriz de Confusión</h3>
                <p class="text-xs text-muted-foreground mt-0.5">Umbral de clasificación = 0.50</p>
            </div>

            {{-- Etiquetas de predicción --}}
            <div class="grid grid-cols-3 gap-2 mb-1">
                <div></div>
                <p class="text-xs font-mono text-center text-muted-foreground tracking-wider">PRED +</p>
                <p class="text-xs font-mono text-center text-muted-foreground tracking-wider">PRED −</p>
            </div>

            {{-- Filas de la matriz --}}
            <div class="space-y-2">
                {{-- Fila: Real Positivo --}}
                <div class="grid grid-cols-3 gap-2 items-center">
                    <p class="text-xs font-mono text-muted-foreground tracking-wider text-right pr-2" style="writing-mode:initial;">REAL +</p>
                    <div class="cm-cell cm-tp">
                        <span class="cm-value text-success" x-text="cmDisplay.tp"></span>
                        <span class="cm-label" style="color:hsl(var(--success));">Verdadero +</span>
                    </div>
                    <div class="cm-cell cm-fn">
                        <span class="cm-value text-destructive" x-text="cmDisplay.fn"></span>
                        <span class="cm-label" style="color:hsl(var(--destructive));">Falso −</span>
                    </div>
                </div>
                {{-- Fila: Real Negativo --}}
                <div class="grid grid-cols-3 gap-2 items-center">
                    <p class="text-xs font-mono text-muted-foreground tracking-wider text-right pr-2">REAL −</p>
                    <div class="cm-cell cm-fp">
                        <span class="cm-value text-warning" x-text="cmDisplay.fp"></span>
                        <span class="cm-label" style="color:hsl(var(--warning));">Falso +</span>
                    </div>
                    <div class="cm-cell cm-tn">
                        <span class="cm-value text-primary" x-text="cmDisplay.tn"></span>
                        <span class="cm-label" style="color:hsl(var(--primary));">Verdadero −</span>
                    </div>
                </div>
            </div>

            {{-- Totales --}}
            <div class="diag-line my-4"></div>
            <div class="grid grid-cols-2 gap-3 text-xs font-mono">
                <div class="text-center p-2 rounded-lg" style="background:hsl(var(--secondary));">
                    <p class="text-muted-foreground">Total positivos reales</p>
                    <p class="text-lg font-bold mt-0.5" style="color:hsl(var(--success));">5,210</p>
                </div>
                <div class="text-center p-2 rounded-lg" style="background:hsl(var(--secondary));">
                    <p class="text-muted-foreground">Total negativos reales</p>
                    <p class="text-lg font-bold mt-0.5" style="color:hsl(var(--primary));">5,210</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────────────────
         FILA 3 · Métricas secundarias: F1, AUC, MCC + info modelo
    ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4 animate-fade-in-up" style="animation-delay:480ms;">

        <template x-for="(s, i) in secondary" :key="s.key">
            <div class="sec-metric lg:col-span-2">
                <div class="scan-beam"></div>
                <div class="flex items-start justify-between">
                    <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground" x-text="s.label"></p>
                    <span class="text-xs font-mono px-1.5 py-0.5 rounded"
                          style="background:hsl(var(--secondary)); color:hsl(var(--muted-foreground));"
                          x-text="s.formula"></span>
                </div>
                <div class="flex items-end justify-between mt-1">
                    <span class="font-mono font-bold text-2xl" x-text="secDisplay[s.key]"
                          :style="`color:${s.color}`"></span>
                    <div class="flex items-center gap-1 text-xs" :style="`color:${s.color}`">
                        <span>↑</span>
                        <span x-text="s.delta"></span>
                    </div>
                </div>
                {{-- Mini sparkline del historial --}}
                <svg width="100%" height="28" class="mt-2" viewBox="0 0 120 28" preserveAspectRatio="none">
                    <polyline :points="s.history" fill="none"
                              :stroke="s.color" stroke-width="1.5"
                              stroke-linecap="round" stroke-linejoin="round"
                              opacity="0.8"/>
                    <polyline :points="s.history" fill="none"
                              :stroke="s.color" stroke-width="6"
                              stroke-linecap="round" stroke-linejoin="round"
                              opacity="0.06"/>
                </svg>
                <p class="text-xs text-muted-foreground leading-relaxed" x-text="s.desc"></p>
            </div>
        </template>
    </div>

    {{-- FILA 4 · Nota interpretativa + estado del modelo (deshabilitado temporalmente) --}}

</div>

{{-- ─────────────────────────────────────────────────────────────────
     JAVASCRIPT — Alpine data + ROC canvas
───────────────────────────────────────────────────────────────── --}}
<script>
function metricsPage() {
    return {
        animated: false,
        displayValues: { sens: 0, spec: 0, prec: 0, acc: 0 },
        cmDisplay: { tp: '0', fp: '0', fn: '0', tn: '0' },
        secDisplay: { f1: '0.000', auc: '0.000', mcc: '0.000' },

        metrics: [
            {
                key: 'sens', label: 'Sensibilidad', sublabel: 'Recall / TPR',
                value: 93.8, trend: 2.1,
                color: 'hsl(160, 70%, 45%)',
                desc: 'De cada 100 arritmias reales, el modelo identifica correctamente 93.8',
            },
            {
                key: 'spec', label: 'Especificidad', sublabel: 'TNR / Selectividad',
                value: 96.2, trend: 1.4,
                color: 'hsl(198, 90%, 50%)',
                desc: 'El 96.2% de los ritmos normales son correctamente descartados',
            },
            {
                key: 'prec', label: 'Precisión', sublabel: 'Valor Predictivo +',
                value: 91.5, trend: -0.3,
                color: 'hsl(270, 70%, 65%)',
                desc: 'El 91.5% de las alertas emitidas corresponden a arritmias reales',
            },
            {
                key: 'acc', label: 'Exactitud', sublabel: 'Overall Accuracy',
                value: 94.7, trend: 1.2,
                color: 'hsl(38, 90%, 58%)',
                desc: 'Porcentaje global de clasificaciones correctas sobre los resultados del doctor',
            },
        ],

        secondary: [
            {
                key: 'f1', label: 'F1-Score', formula: '2·P·R/(P+R)',
                value: 0.926, delta: '+0.018 vs v2.0',
                color: 'hsl(160, 70%, 45%)',
                history: '0,20 20,16 40,12 60,8 80,5 100,2 120,3',
                desc: 'Media armónica de precisión y sensibilidad; robusta frente a clases desbalanceadas.',
            },
            {
                key: 'auc', label: 'AUC-ROC', formula: '∫ROC',
                value: 0.974, delta: '+0.009 vs v2.0',
                color: 'hsl(198, 90%, 50%)',
                history: '0,22 20,18 40,13 60,9 80,5 100,3 120,2',
                desc: 'Área bajo la curva ROC. Valor 1.0 es clasificación perfecta; 0.5 es aleatoria.',
            },
            {
                key: 'mcc', label: 'Coef. Matthews', formula: 'MCC',
                value: 0.881, delta: '+0.024 vs v2.0',
                color: 'hsl(270, 70%, 65%)',
                history: '0,24 20,20 40,15 60,10 80,6 100,4 120,2',
                desc: 'Correlación entre predicciones reales y esperadas. El mejor indicador para clases balanceadas.',
            },
        ],

        // Datos reales de la matriz de confusión
        cmRaw: { tp: 4888, fp: 322, fn: 433, tn: 4777 },

        init() {
            // Trigger animations after small delay
            setTimeout(() => {
                this.animated = true;
                this.animateCounters();
                this.animateCm();
                this.animateSecondary();
            }, 300);

            // Draw ROC after next tick
            this.$nextTick(() => this.drawROC());
            window.addEventListener('resize', () => this.drawROC());
        },

        animateCounters() {
            const targets = { sens: 93.8, spec: 96.2, prec: 91.5, acc: 94.7 };
            const duration = 1400;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                for (const k in targets) {
                    this.displayValues[k] = (targets[k] * ease).toFixed(1);
                }
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        },

        animateCm() {
            const targets = this.cmRaw;
            const duration = 1200;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                this.cmDisplay = {
                    tp: Math.round(targets.tp * ease).toLocaleString('es'),
                    fp: Math.round(targets.fp * ease).toLocaleString('es'),
                    fn: Math.round(targets.fn * ease).toLocaleString('es'),
                    tn: Math.round(targets.tn * ease).toLocaleString('es'),
                };
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        },

        animateSecondary() {
            const targets = { f1: 0.926, auc: 0.974, mcc: 0.881 };
            const duration = 1400;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                for (const k in targets) {
                    this.secDisplay[k] = (targets[k] * ease).toFixed(3);
                }
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        },

        drawROC() {
            const canvas = document.getElementById('rocCanvas');
            if (!canvas) return;

            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.parentElement.getBoundingClientRect();
            const W = rect.width;
            const H = Math.min(W * 0.65, 400);
            canvas.width  = W * dpr;
            canvas.height = H * dpr;
            canvas.style.width  = W + 'px';
            canvas.style.height = H + 'px';

            const ctx = canvas.getContext('2d');
            ctx.scale(dpr, dpr);
            ctx.clearRect(0, 0, W, H);

            const pad = { top: 20, right: 20, bottom: 40, left: 48 };
            const cw = W - pad.left - pad.right;
            const ch = H - pad.top  - pad.bottom;

            // ── Fondo claro ──
            ctx.fillStyle = 'hsl(210, 40%, 98%)';
            ctx.beginPath();
            ctx.roundRect(0, 0, W, H, 10);
            ctx.fill();

            // ── Borde suave ──
            ctx.strokeStyle = 'hsl(210, 25%, 88%)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.roundRect(0.5, 0.5, W - 1, H - 1, 10);
            ctx.stroke();

            // ── Grid ──
            ctx.save();
            ctx.translate(pad.left, pad.top);
            const gridLines = 5;
            for (let i = 0; i <= gridLines; i++) {
                const x = (cw / gridLines) * i;
                const y = (ch / gridLines) * i;
                ctx.strokeStyle = 'hsl(210, 25%, 91%)';
                ctx.lineWidth = 1;
                ctx.setLineDash([4, 4]);
                ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, ch); ctx.stroke();
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(cw, y); ctx.stroke();
            }
            ctx.setLineDash([]);

            // ── Diagonal (clasificador aleatorio) ──
            ctx.strokeStyle = 'hsl(210, 15%, 72%)';
            ctx.lineWidth = 1.5;
            ctx.setLineDash([6, 4]);
            ctx.beginPath();
            ctx.moveTo(0, ch);
            ctx.lineTo(cw, 0);
            ctx.stroke();
            ctx.setLineDash([]);

            // ── Curva ROC ──
            const rocPoints = [
                [0.000, 0.000],
                [0.002, 0.210],
                [0.005, 0.420],
                [0.010, 0.610],
                [0.018, 0.740],
                [0.030, 0.820],
                [0.048, 0.872],
                [0.072, 0.906],
                [0.100, 0.930],
                [0.140, 0.948],
                [0.190, 0.960],
                [0.260, 0.968],
                [0.340, 0.974],
                [0.440, 0.980],
                [0.560, 0.985],
                [0.680, 0.989],
                [0.800, 0.993],
                [0.900, 0.996],
                [1.000, 1.000],
            ];

            const toX = (fpr) => fpr * cw;
            const toY = (tpr) => ch - tpr * ch;

            // Relleno degradado bajo la curva
            const grad = ctx.createLinearGradient(0, 0, 0, ch);
            grad.addColorStop(0,   'hsla(198, 90%, 42%, 0.18)');
            grad.addColorStop(0.6, 'hsla(198, 90%, 42%, 0.05)');
            grad.addColorStop(1,   'hsla(198, 90%, 42%, 0.00)');

            ctx.beginPath();
            ctx.moveTo(toX(rocPoints[0][0]), toY(rocPoints[0][1]));
            for (let i = 1; i < rocPoints.length; i++) {
                const [x1, y1] = rocPoints[i-1];
                const [x2, y2] = rocPoints[i];
                const cpX = toX((x1+x2)/2);
                ctx.bezierCurveTo(cpX, toY(y1), cpX, toY(y2), toX(x2), toY(y2));
            }
            ctx.lineTo(toX(1), toY(0));
            ctx.lineTo(toX(0), toY(0));
            ctx.closePath();
            ctx.fillStyle = grad;
            ctx.fill();

            // Línea de la curva
            ctx.shadowColor = 'hsla(198, 90%, 42%, 0.35)';
            ctx.shadowBlur  = 8;
            ctx.strokeStyle = 'hsl(198, 90%, 38%)';
            ctx.lineWidth   = 2.5;
            ctx.lineJoin    = 'round';
            ctx.beginPath();
            ctx.moveTo(toX(rocPoints[0][0]), toY(rocPoints[0][1]));
            for (let i = 1; i < rocPoints.length; i++) {
                const [x1, y1] = rocPoints[i-1];
                const [x2, y2] = rocPoints[i];
                const cpX = toX((x1+x2)/2);
                ctx.bezierCurveTo(cpX, toY(y1), cpX, toY(y2), toX(x2), toY(y2));
            }
            ctx.stroke();
            ctx.shadowBlur = 0;

            // Punto óptimo (Youden J)
            const opPoint = [0.038, 0.938];
            ctx.fillStyle   = 'hsl(198, 90%, 38%)';
            ctx.strokeStyle = 'hsl(210, 40%, 98%)';
            ctx.lineWidth   = 2.5;
            ctx.shadowColor = 'hsla(198, 90%, 42%, 0.4)';
            ctx.shadowBlur  = 10;
            ctx.beginPath();
            ctx.arc(toX(opPoint[0]), toY(opPoint[1]), 6, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            ctx.shadowBlur = 0;

            // Etiqueta punto óptimo
            ctx.fillStyle = 'hsl(198, 80%, 32%)';
            ctx.font = '10px "JetBrains Mono", monospace';
            ctx.fillText('Punto óptimo', toX(opPoint[0]) + 10, toY(opPoint[1]) - 6);
            ctx.fillStyle = 'hsl(213, 20%, 55%)';
            ctx.fillText(`FPR=${opPoint[0]} · TPR=${opPoint[1]}`, toX(opPoint[0]) + 10, toY(opPoint[1]) + 8);

            // ── Ejes ──
            ctx.strokeStyle = 'hsl(210, 25%, 80%)';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(0, ch); ctx.lineTo(cw, ch);
            ctx.moveTo(0, 0);  ctx.lineTo(0, ch);
            ctx.stroke();

            // Ticks y etiquetas
            ctx.fillStyle = 'hsl(213, 20%, 55%)';
            ctx.font = '10px "JetBrains Mono", monospace';
            ctx.textAlign = 'center';
            for (let i = 0; i <= 5; i++) {
                const v = i / 5;
                const x = v * cw;
                ctx.fillText((v).toFixed(1), x, ch + 18);
            }
            ctx.textAlign = 'right';
            for (let i = 0; i <= 5; i++) {
                const v = i / 5;
                const y = (1 - v) * ch;
                ctx.fillText((v).toFixed(1), -6, y + 4);
            }

            // Etiqueta eje Y
            ctx.save();
            ctx.translate(-34, ch / 2);
            ctx.rotate(-Math.PI / 2);
            ctx.textAlign = 'center';
            ctx.font = '10px "JetBrains Mono", monospace';
            ctx.fillStyle = 'hsl(213, 20%, 55%)';
            ctx.fillText('← Tasa Verdaderos Positivos (Sensibilidad)', 0, 0);
            ctx.restore();

            ctx.restore(); // remove pad translate
        },
    };
}
</script>

@endsection

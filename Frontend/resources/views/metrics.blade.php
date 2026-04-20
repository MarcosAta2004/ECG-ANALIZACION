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
            Estadisticas del sistema ECG filtradas por periodo · {{ $filters['label'] }}
        </p>
    </div>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2 animate-fade-in-delay-2">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono"
             style="background: hsl(var(--primary)/0.1); border: 1px solid hsl(var(--primary)/0.3); color: hsl(var(--primary));">
            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: hsl(var(--primary));"></span>
            {{ $filters['hasRange'] ? 'Rango activo' : 'Historico completo' }}
        </div>
    </div>
@endsection

@section('content')
<style>
    .dash-panel {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 16px;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
    }

    .dash-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, hsl(var(--primary) / 0.04), transparent 55%);
        pointer-events: none;
    }

    .metric-card {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 2px;
        background: var(--ring-color, hsl(var(--primary)));
        opacity: 0.45;
    }

    .ring-track { stroke: hsl(var(--secondary)); }
    .ring-fill { stroke-linecap: round; }

    .mini-metric {
        background: hsl(var(--background) / 0.55);
        border: 1px solid hsl(var(--border));
        border-radius: 14px;
        padding: 1rem 1.1rem;
    }

    .cm-box {
        border-radius: 14px;
        padding: 1rem;
        text-align: center;
        border: 1px solid transparent;
    }

    .cm-tp { background: hsl(160 84% 95%); border-color: hsl(160 70% 80%); }
    .cm-fp { background: hsl(38 92% 95%); border-color: hsl(38 70% 80%); }
    .cm-fn { background: hsl(0 84% 96%); border-color: hsl(0 65% 82%); }
    .cm-tn { background: hsl(198 100% 95%); border-color: hsl(198 80% 78%); }

    .roc-grid-line {
        stroke: hsl(var(--border));
        stroke-dasharray: 4 4;
        opacity: 0.9;
    }

    .roc-axis {
        stroke: hsl(var(--muted-foreground) / 0.45);
        stroke-width: 1.25;
    }

    .filter-panel {
        display: grid;
        gap: 0.85rem;
        align-items: center;
    }

    .filter-intro {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .filter-grid {
        display: grid;
        gap: 0.7rem;
        align-items: end;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .filter-label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: hsl(var(--muted-foreground));
    }

    .filter-actions {
        display: grid;
        gap: 0.6rem;
    }

    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 42px;
        padding: 0.65rem 0.9rem;
        border-radius: 12px;
        border: 1px solid hsl(var(--border));
        background: hsl(var(--background) / 0.8);
        color: hsl(var(--foreground));
        font-size: 0.88rem;
        font-weight: 600;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .filter-clear-btn:hover {
        background: hsl(var(--secondary));
        border-color: hsl(var(--primary) / 0.18);
        transform: translateY(-1px);
    }

    .filter-range-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        width: fit-content;
        padding: 0.45rem 0.72rem;
        border-radius: 999px;
        border: 1px solid hsl(var(--border));
        background: hsl(var(--background) / 0.78);
        color: hsl(var(--foreground));
        font-size: 0.72rem;
        font-family: "JetBrains Mono", monospace;
    }

    .confidence-spotlight {
        grid-column: span 2;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 1.05rem 1.15rem;
        border: 1px solid hsl(var(--primary) / 0.18);
        background:
            radial-gradient(circle at top right, hsl(var(--accent) / 0.24), transparent 34%),
            linear-gradient(135deg, hsl(var(--primary) / 0.12), hsl(198 100% 97%));
        box-shadow: 0 10px 28px hsl(var(--primary) / 0.08);
    }

    .confidence-spotlight::after {
        content: '';
        position: absolute;
        right: -26px;
        top: -18px;
        width: 110px;
        height: 110px;
        border-radius: 999px;
        background: hsl(var(--primary) / 0.08);
        pointer-events: none;
    }

    .confidence-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.32rem 0.68rem;
        border-radius: 999px;
        background: hsl(var(--card) / 0.72);
        border: 1px solid hsl(var(--primary) / 0.16);
        color: hsl(var(--primary));
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        position: relative;
        z-index: 1;
    }

    .confidence-value {
        position: relative;
        z-index: 1;
        margin-top: 0.7rem;
        display: flex;
        align-items: baseline;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .confidence-value strong {
        font-size: clamp(1.75rem, 2.4vw, 2.35rem);
        line-height: 1;
        font-weight: 800;
        color: hsl(var(--primary));
        letter-spacing: -0.03em;
    }

    .confidence-value span {
        font-size: 0.82rem;
        color: hsl(var(--foreground) / 0.82);
        font-family: "JetBrains Mono", monospace;
    }

    .confidence-caption {
        position: relative;
        z-index: 1;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        line-height: 1.45;
        color: hsl(var(--foreground) / 0.82);
        max-width: 34rem;
    }

    .confidence-caption b {
        color: hsl(var(--foreground));
    }

    @media (min-width: 1024px) {
        .filter-panel {
            grid-template-columns: auto minmax(0, 1fr);
            gap: 1rem;
        }

        .filter-grid {
            grid-template-columns: minmax(140px, 170px) minmax(140px, 170px) auto;
        }

        .filter-actions {
            grid-column: auto;
            grid-template-columns: auto auto;
        }
    }
</style>

<div class="space-y-6" x-data="metricsPage()" x-init="init()">
    <section class="dash-panel p-4 sm:p-5 animate-fade-in-up">
        <form method="GET" action="{{ route('dashboard') }}" class="filter-panel">
            <div class="filter-intro">
                <p class="text-base font-semibold">Filtrar por fechas</p>
                <div class="filter-range-chip">
                    <span class="w-2 h-2 rounded-full" style="background:hsl(var(--primary));"></span>
                    {{ $filters['label'] }}
                </div>
            </div>
            <div class="filter-grid">
                <label class="filter-field">
                    <span class="filter-label">Desde</span>
                    <input type="date" name="from" value="{{ $filters['from'] }}" class="input-field" />
                </label>
                <label class="filter-field">
                    <span class="filter-label">Hasta</span>
                    <input type="date" name="to" value="{{ $filters['to'] }}" class="input-field" />
                </label>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary w-full">Aplicar filtro</button>
                    <a href="{{ route('dashboard') }}" class="filter-clear-btn">
                        Limpiar rango
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <template x-for="(metric, index) in metrics" :key="metric.key">
            <article class="metric-card animate-fade-in-up" :style="`animation-delay:${index * 90}ms; --ring-color:${metric.color}`">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground" x-text="metric.label"></p>
                        <p class="text-xs text-muted-foreground mt-1" x-text="metric.sublabel"></p>
                    </div>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-full"
                          :style="`background:${metric.color}18; color:${metric.color}; border:1px solid ${metric.color}30`"
                          x-text="metric.badge"></span>
                </div>

                <div class="flex items-center justify-center">
                    <div class="relative">
                        <svg width="120" height="120" viewBox="0 0 120 120" style="transform: rotate(-90deg);">
                            <circle class="ring-track" cx="60" cy="60" r="50" fill="none" stroke-width="8"></circle>
                            <circle class="ring-fill"
                                    cx="60" cy="60" r="50" fill="none" stroke-width="8"
                                    :stroke="metric.color"
                                    :stroke-dasharray="314"
                                    :stroke-dashoffset="animated ? (314 * (1 - metric.value / 100)) : 314"
                                    style="transition: stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1);"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-mono font-bold text-2xl leading-none"
                                  :style="`color:${metric.color}`"
                                  x-text="metricDisplay[metric.key]"></span>
                            <span class="text-xs text-muted-foreground mt-1">score</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 h-1 rounded-full" style="background:hsl(var(--secondary));">
                    <div class="h-1 rounded-full"
                         :style="`width:${animated ? metric.value : 0}%; background:${metric.color}; box-shadow:0 0 8px ${metric.color}60; transition: width 1.2s cubic-bezier(0.4,0,0.2,1);`"></div>
                </div>

                <p class="text-xs text-muted-foreground mt-3 leading-relaxed" x-text="metric.desc"></p>
            </article>
        </template>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-5 gap-4">
        <div class="dash-panel p-5 xl:col-span-3 animate-fade-in-up" style="animation-delay:220ms;">
            <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                <div>
                    <h2 class="font-semibold text-base">Curva ROC</h2>
                    <p class="text-xs text-muted-foreground mt-1">
                        @if($rocData)
                            Construida con la confianza del modelo sobre casos revisados en el periodo.
                        @else
                            Se necesitan casos revisados de ambas clases para calcular la curva ROC.
                        @endif
                    </p>
                </div>

                @if($rocData)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono"
                         style="background:hsl(var(--primary)/0.08); border:1px solid hsl(var(--primary)/0.24); color:hsl(var(--primary));">
                        AUC = {{ number_format($rocData['auc'], 3) }}
                    </div>
                @endif
            </div>

            @if($rocData)
                @php
                    $svg = $rocData['svg'];
                    $plotRight = $svg['padding'] + $svg['plot_width'];
                    $plotBottom = $svg['padding'] + $svg['plot_height'];
                @endphp
                <div class="rounded-2xl border border-border p-3" style="background:hsl(var(--background)/0.55);">
                    <svg viewBox="0 0 {{ $svg['width'] }} {{ $svg['height'] }}" class="w-full h-auto">
                        @for($i = 0; $i <= 5; $i++)
                            @php
                                $x = $svg['padding'] + (($svg['plot_width'] / 5) * $i);
                                $y = $svg['padding'] + (($svg['plot_height'] / 5) * $i);
                            @endphp
                            <line x1="{{ $x }}" y1="{{ $svg['padding'] }}" x2="{{ $x }}" y2="{{ $plotBottom }}" class="roc-grid-line" />
                            <line x1="{{ $svg['padding'] }}" y1="{{ $y }}" x2="{{ $plotRight }}" y2="{{ $y }}" class="roc-grid-line" />
                        @endfor

                        <line x1="{{ $svg['padding'] }}" y1="{{ $plotBottom }}" x2="{{ $plotRight }}" y2="{{ $svg['padding'] }}"
                              stroke="hsl(var(--muted-foreground) / 0.35)" stroke-dasharray="6 5" />

                        <polyline points="{{ $rocData['area_points'] }}"
                                  fill="hsl(var(--primary) / 0.10)" stroke="none" />
                        <polyline points="{{ $rocData['line_points'] }}"
                                  fill="none" stroke="hsl(var(--primary))" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />

                        <circle cx="{{ $rocData['svg']['best_x'] }}" cy="{{ $rocData['svg']['best_y'] }}" r="5"
                                fill="hsl(var(--primary))" stroke="hsl(var(--card))" stroke-width="2" />

                        <line x1="{{ $svg['padding'] }}" y1="{{ $plotBottom }}" x2="{{ $plotRight }}" y2="{{ $plotBottom }}" class="roc-axis" />
                        <line x1="{{ $svg['padding'] }}" y1="{{ $plotBottom }}" x2="{{ $svg['padding'] }}" y2="{{ $svg['padding'] }}" class="roc-axis" />

                        @for($i = 0; $i <= 5; $i++)
                            @php
                                $tick = number_format($i / 5, 1);
                                $x = $svg['padding'] + (($svg['plot_width'] / 5) * $i);
                                $y = $plotBottom - (($svg['plot_height'] / 5) * $i);
                            @endphp
                            <text x="{{ $x }}" y="{{ $plotBottom + 18 }}" text-anchor="middle" font-size="10" fill="hsl(var(--muted-foreground))">{{ $tick }}</text>
                            <text x="{{ $svg['padding'] - 10 }}" y="{{ $y + 3 }}" text-anchor="end" font-size="10" fill="hsl(var(--muted-foreground))">{{ $tick }}</text>
                        @endfor
                    </svg>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <div class="mini-metric">
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground">AUC</p>
                        <p class="text-2xl font-bold text-primary mt-2">{{ number_format($rocData['auc'], 3) }}</p>
                        <p class="text-xs text-muted-foreground mt-1">Area bajo la curva ROC del rango.</p>
                    </div>
                    <div class="mini-metric">
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground">Umbral optimo</p>
                        <p class="text-2xl font-bold mt-2" style="color:hsl(var(--success));">{{ number_format($rocData['best_threshold'] * 100, 1) }}%</p>
                        <p class="text-xs text-muted-foreground mt-1">Maximiza TPR - FPR sobre los revisados.</p>
                    </div>
                </div>
            @else
                <div class="mini-metric">
                    <p class="font-semibold mb-2">ROC no disponible en este rango</p>
                    <p class="text-sm text-muted-foreground">
                        Para calcular la curva ROC y el AUC se necesitan casos revisados por medico tanto normales como con arritmia dentro del periodo seleccionado.
                    </p>
                </div>
            @endif
        </div>

        <div class="dash-panel p-5 xl:col-span-2 animate-fade-in-up" style="animation-delay:280ms;">
            <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
                <div>
                    <h2 class="font-semibold text-base">Validacion Clinica del Rango</h2>
                    <p class="text-xs text-muted-foreground mt-1">
                        @if($realMetrics)
                            Basado en {{ number_format($realMetrics['reviewedCount']) }} analisis revisados por el medico.
                        @else
                            Aun no hay revisiones medicas suficientes dentro del periodo seleccionado.
                        @endif
                    </p>
                </div>
                <a href="{{ route('history') }}" class="text-sm font-medium text-primary hover:underline">Ir al historial</a>
            </div>

            @if($realMetrics)
                <div class="grid grid-cols-2 gap-3">
                    <div class="cm-box cm-tp">
                        <p class="text-xs font-mono text-success mb-1">Verdadero +</p>
                        <p class="text-3xl font-bold text-success">{{ number_format($realMetrics['tp']) }}</p>
                    </div>
                    <div class="cm-box cm-fn">
                        <p class="text-xs font-mono text-destructive mb-1">Falso -</p>
                        <p class="text-3xl font-bold text-destructive">{{ number_format($realMetrics['fn']) }}</p>
                    </div>
                    <div class="cm-box cm-fp">
                        <p class="text-xs font-mono text-warning mb-1">Falso +</p>
                        <p class="text-3xl font-bold text-warning">{{ number_format($realMetrics['fp']) }}</p>
                    </div>
                    <div class="cm-box cm-tn">
                        <p class="text-xs font-mono text-primary mb-1">Verdadero -</p>
                        <p class="text-3xl font-bold text-primary">{{ number_format($realMetrics['tn']) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4 text-xs font-mono">
                    <div class="mini-metric">
                        <p class="text-muted-foreground">Positivos reales</p>
                        <p class="text-lg font-bold text-success mt-1">{{ number_format($positiveReal) }}</p>
                    </div>
                    <div class="mini-metric">
                        <p class="text-muted-foreground">Negativos reales</p>
                        <p class="text-lg font-bold text-primary mt-1">{{ number_format($negativeReal) }}</p>
                    </div>
                    <div class="confidence-spotlight">
                        <div class="confidence-meta">
                            <span class="w-2 h-2 rounded-full" style="background:hsl(var(--primary));"></span>
                            Casos revisados por medico
                        </div>
                        <div class="confidence-value">
                            <strong>{{ number_format($reviewedAvgConfidence, 1) }}%</strong>
                            <span>confianza media del modelo</span>
                        </div>
                        <p class="confidence-caption">
                            Este promedio resume cuan segura fue la prediccion del modelo en los
                            <b>{{ number_format($realMetrics['reviewedCount']) }} casos validados clinicamente</b>
                            dentro del rango seleccionado.
                        </p>
                    </div>
                </div>
            @else
                <div class="mini-metric">
                    <p class="font-semibold mb-2">Sin metrica clinica en este rango</p>
                    <p class="text-sm text-muted-foreground">
                        Cuando existan revisiones medicas entre las fechas seleccionadas, aqui se mostraran sensibilidad,
                        especificidad, precision, exactitud y la matriz de confusion del periodo.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 animate-fade-in-up" style="animation-delay:340ms;">
        <template x-for="card in secondary" :key="card.key">
            <div class="mini-metric">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground" x-text="card.label"></p>
                        <p class="text-xs text-muted-foreground mt-1" x-text="card.desc"></p>
                    </div>
                    <span class="text-[11px] font-mono px-1.5 py-0.5 rounded"
                          style="background:hsl(var(--secondary)); color:hsl(var(--muted-foreground));"
                          x-text="card.formula"></span>
                </div>
                <div class="flex items-end justify-between mt-4">
                    <span class="font-mono font-bold text-2xl" :style="`color:${card.color}`" x-text="secondaryDisplay[card.key]"></span>
                    <span class="text-xs text-right max-w-[9rem]" :style="`color:${card.color}`" x-text="card.delta"></span>
                </div>
            </div>
        </template>
    </section>
</div>

<script>
function metricsPage() {
    const dashboardData = @json($dashboardData);

    return {
        animated: false,
        metrics: dashboardData.metricCards ?? [],
        secondary: dashboardData.secondaryCards ?? [],
        metricDisplay: {},
        secondaryDisplay: {},

        init() {
            this.metrics.forEach((metric) => {
                this.metricDisplay[metric.key] = '0.0%';
            });

            this.secondary.forEach((card) => {
                this.secondaryDisplay[card.key] = card.format === 'score' ? '0.000' : '0.0%';
            });

            setTimeout(() => {
                this.animated = true;
                this.animateMetrics();
                this.animateSecondary();
            }, 250);
        },

        animateMetrics() {
            const duration = 1200;
            const start = performance.now();
            const targets = Object.fromEntries(this.metrics.map((metric) => [metric.key, Number(metric.value) || 0]));

            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);

                Object.keys(targets).forEach((key) => {
                    this.metricDisplay[key] = `${(targets[key] * ease).toFixed(1)}%`;
                });

                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },

        animateSecondary() {
            const duration = 1200;
            const start = performance.now();

            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);

                this.secondary.forEach((card) => {
                    const target = Number(card.value) || 0;

                    if (card.format === 'score') {
                        this.secondaryDisplay[card.key] = (target * ease).toFixed(3);
                    } else {
                        this.secondaryDisplay[card.key] = `${(target * 100 * ease).toFixed(1)}%`;
                    }
                });

                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },
    };
}
</script>
@endsection

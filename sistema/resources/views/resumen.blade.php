@extends('plantillas.aplicacion')

@section('title', 'Resumen')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Resumen general</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Vista consolidada de los analisis ECG y la actividad reciente del sistema.
    </p>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2 px-4 py-2 rounded-full animate-fade-in-delay-2"
         style="background:hsl(var(--success)/0.1);border:1px solid hsl(var(--success)/0.2);">
        <div class="w-2 h-2 rounded-full animate-pulse" style="background:hsl(var(--success));"></div>
        <span class="text-sm font-medium text-success">Sistema activo</span>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    @php
        $statsCount = count($stats);
        $statsGridClass = match (true) {
            $statsCount >= 4 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6',
            $statsCount === 3 => 'grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6',
            $statsCount === 2 => 'grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6',
            default => 'grid grid-cols-1 gap-4 lg:gap-6',
        };
    @endphp

    <section>
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Resumen Estadistico
        </h2>
        <div class="{{ $statsGridClass }}">
            @foreach($stats as $i => $stat)
                <div class="card animate-fade-in-up group h-full min-h-[188px]" style="animation-delay:{{ $i * 100 }}ms;">
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none rounded-xl"
                         style="background:linear-gradient(to bottom right,hsl(var(--primary)/0.05),transparent);"></div>
                    <div class="relative flex items-start justify-between gap-4 h-full">
                        <div class="space-y-2 flex-1">
                            <p class="text-sm text-muted-foreground font-medium">{{ $stat['title'] }}</p>
                            <p class="text-3xl font-bold tracking-tight">{{ $stat['value'] }}</p>
                            <p class="text-sm text-muted-foreground">{{ $stat['subtitle'] }}</p>
                            <div class="inline-flex items-center gap-1 text-sm font-medium {{ $stat['trend'] === 'up' ? 'text-success' : ($stat['trend'] === 'down' ? 'text-destructive' : 'text-muted-foreground') }}">
                                <span>{!! $stat['trend'] === 'up' ? '&uarr;' : ($stat['trend'] === 'down' ? '&darr;' : '&rarr;') !!}</span>
                                <span>{{ $stat['trend_value'] }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl shrink-0 self-start" style="background:hsl(var(--primary)/0.1);">
                            @if($stat['icon'] === 'file-heart')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                             a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @elseif($stat['icon'] === 'check-circle')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($stat['icon'] === 'alert-triangle')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856
                                             c1.54 0 2.502-1.667 1.732-3L13.732 4
                                             c-.77-1.333-2.694-1.333-3.464 0
                                             L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 animate-fade-in-up" style="animation-delay:400ms;">
            <div class="card">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                    </svg>
                    Actividad Reciente
                </h2>
                <div class="space-y-3">
                    @foreach($recentActivity as $item)
                        <div class="flex items-center justify-between p-3 rounded-lg transition-colors"
                             style="background:hsl(var(--muted)/0.5);">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full"
                                     style="background:hsl(var(--{{ $item['type'] === 'Normal' ? 'success' : 'warning' }}));"></div>
                                <div>
                                    <p class="text-sm font-medium">{{ $item['file'] }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $item['time'] }}</p>
                                </div>
                            </div>
                            <span class="badge {{ $item['type'] === 'Normal' ? 'badge-success' : 'badge-warning' }}">
                                {{ $item['type'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="animate-fade-in-up" style="animation-delay:500ms;">
            <div class="card h-full">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                    </svg>
                    Acciones Rapidas
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('upload') }}"
                       class="block p-4 rounded-lg transition-all group"
                       style="background:hsl(var(--primary)/0.1);border:1px solid hsl(var(--primary)/0.2);"
                       onmouseover="this.style.background='hsl(var(--primary)/0.2)'"
                       onmouseout="this.style.background='hsl(var(--primary)/0.1)'">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg text-primary transition-transform group-hover:scale-110"
                                 style="background:hsl(var(--primary)/0.2);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Nuevo Analisis</p>
                                <p class="text-xs text-muted-foreground">Subir archivo ECG</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('history') }}"
                       class="block p-4 rounded-lg transition-all group bg-secondary hover:bg-secondary/80">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-muted text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Ver Historial</p>
                                <p class="text-xs text-muted-foreground">Reportes anteriores</p>
                            </div>
                        </div>
                    </a>

                    <div class="p-4 rounded-lg border border-border" style="background:hsl(var(--muted)/0.3);">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-muted text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                                             M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                                             m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-muted-foreground">Multi-usuario</p>
                                <p class="text-xs text-muted-foreground">Proximamente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

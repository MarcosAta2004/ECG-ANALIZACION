@extends('plantillas.aplicacion')

@section('title', 'Reportes')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Reportes</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Descarga reportes de todos los pacientes o solo de pacientes seleccionados.
    </p>
@endsection

@section('content')
<div class="space-y-6" x-data="{ mode: 'all' }">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in-up">
        <div class="card">
            <p class="text-sm text-muted-foreground">Pacientes registrados</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['patients']) }}</p>
        </div>
        <div class="card">
            <p class="text-sm text-muted-foreground">Analisis disponibles</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['analyses']) }}</p>
        </div>
    </div>

    @if (session('error'))
        <div class="alert-error animate-fade-in">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('reportes.download') }}" class="card animate-fade-in-up" style="animation-delay:120ms;">
        @csrf

        <div class="flex flex-col gap-5">
            <div>
                <h2 class="text-lg font-semibold">Tipo de reporte</h2>
                <p class="text-sm text-muted-foreground mt-1">
                    El archivo se descargara en formato Excel (.xlsx).
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="cursor-pointer rounded-xl border border-border p-4 transition-colors hover:bg-muted/30"
                       :class="mode === 'all' ? 'border-primary shadow-glow' : ''">
                    <input type="radio" name="mode" value="all" x-model="mode" class="sr-only">
                    <span class="block font-semibold">Todos los pacientes</span>
                    <span class="block text-sm text-muted-foreground mt-1">
                        Incluye todos los registros disponibles del historial.
                    </span>
                </label>

                <label class="cursor-pointer rounded-xl border border-border p-4 transition-colors hover:bg-muted/30"
                       :class="mode === 'selected' ? 'border-primary shadow-glow' : ''">
                    <input type="radio" name="mode" value="selected" x-model="mode" class="sr-only">
                    <span class="block font-semibold">Algunos pacientes</span>
                    <span class="block text-sm text-muted-foreground mt-1">
                        Permite elegir uno o varios pacientes especificos.
                    </span>
                </label>
            </div>

            <div x-show="mode === 'selected'" x-transition class="rounded-xl border border-border p-4" style="background:hsl(var(--background)/0.55);">
                <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                    <div>
                        <h3 class="font-semibold">Seleccionar pacientes</h3>
                        <p class="text-sm text-muted-foreground">Marca los pacientes que deseas incluir.</p>
                    </div>
                </div>

                @if ($patients->isEmpty())
                    <p class="text-sm text-muted-foreground">No hay pacientes registrados para generar reportes.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach ($patients as $patient)
                            <label class="flex items-start gap-3 rounded-lg border border-border p-3 cursor-pointer hover:bg-card transition-colors">
                                <input type="checkbox"
                                       name="patients[]"
                                       value="{{ $patient['patient_identifier'] }}"
                                       class="mt-1"
                                       :disabled="mode !== 'selected'">
                                <span>
                                    <span class="block font-mono text-sm font-semibold text-primary">
                                        {{ $patient['patient_identifier'] }}
                                    </span>
                                    <span class="block text-xs text-muted-foreground mt-0.5">
                                        {{ number_format($patient['total']) }} analisis
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <p class="text-xs text-muted-foreground">
                    El reporte incluye paciente, archivo, fecha, ritmo, probabilidad, estado IA y valoracion medica.
                </p>
                <button type="submit" class="btn-primary">
                    Descargar Excel
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

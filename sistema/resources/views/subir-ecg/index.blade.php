@extends('plantillas.aplicacion')

@section('title', 'Subir ECG')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Analisis de ECG</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Sube un archivo ECG para analizarlo con la red neuronal.
    </p>
@endsection

@section('content')
<div
    x-data="ecgUpload({ pacientes: {{ Js::from($pacientes ?? []) }}, prefijos: {{ Js::from($prefijos ?? []) }} })"
    data-api-url="{{ config('app.ecg_api_url', 'http://localhost:8001') }}"
    data-analyze-url="{{ route('imagenes.analyze') }}"
    data-csrf-token="{{ csrf_token() }}"
    class="space-y-6"
>
    <div x-show="!file" class="animate-fade-in-up">
        <div
            class="dropzone"
            :class="dragOver ? 'drag-over' : ''"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop($event)"
            @click="$refs.fileInput.click()"
        >
            <input type="file" x-ref="fileInput" class="hidden" accept=".png,.jpg,.jpeg,.pdf,.csv,.txt" @change="handleFileChange($event)" />

            <div class="flex flex-col items-center gap-4">
                <div class="p-5 rounded-full" style="background:hsl(var(--primary)/0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-lg font-semibold">Arrastra tu archivo ECG aqui</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        o <span class="text-primary cursor-pointer hover:underline">haz clic para seleccionar</span>
                    </p>
                    <p class="text-xs text-muted-foreground mt-3">Formatos soportados: PNG, JPG, JPEG, PDF, CSV, TXT</p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="file && !result" class="animate-fade-in-up">
        <div class="card">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-xl shrink-0" style="background:hsl(var(--primary)/0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate" x-text="file?.name"></p>
                    <p class="text-sm text-muted-foreground" x-text="file ? (file.size / 1024).toFixed(1) + ' KB' : ''"></p>

                    <div x-show="preview" class="mt-4 relative">
                        <img :src="preview" alt="Vista previa" class="rounded-lg max-h-64 object-contain border border-border" />
                    </div>

                    <div x-show="analysisError" class="alert-error mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2" />
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                        </svg>
                        <span x-text="analysisError"></span>
                    </div>
                </div>

                <button @click="resetFile()" class="p-2 rounded-lg text-muted-foreground hover:bg-secondary transition-colors shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 space-y-4">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-sm font-semibold text-foreground">Paciente</p>
                        <p class="text-xs text-muted-foreground">Selecciona un paciente existente o crea uno nuevo.</p>
                    </div>
                    <div class="inline-flex rounded-lg border border-border bg-background p-1 text-xs font-semibold">
                        <button type="button"
                                @click="setPatientMode('existing')"
                                class="px-3 py-1.5 rounded-md transition-colors"
                                :class="patientMode === 'existing' ? 'bg-primary text-primary-foreground' : 'text-foreground'">
                            Existente
                        </button>
                        <button type="button"
                                @click="setPatientMode('new')"
                                class="px-3 py-1.5 rounded-md transition-colors"
                                :class="patientMode === 'new' ? 'bg-primary text-primary-foreground' : 'text-foreground'">
                            Nuevo
                        </button>
                    </div>
                </div>

                <div x-show="patientMode === 'existing'" class="space-y-2">
                    <label class="block">
                        <span class="block text-sm font-medium text-muted-foreground mb-1">Paciente existente</span>
                        <select x-model="selectedPatientId" @change="applySelectedPatient()" class="input-field">
                            <option value="">Seleccionar paciente</option>
                            @foreach(($pacientes ?? []) as $paciente)
                                <option value="{{ $paciente['paciente_id'] }}">{{ $paciente['codigo_generado'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <p x-show="selectedPatientLabel" class="text-xs text-muted-foreground" x-text="selectedPatientLabel"></p>
                    <p x-show="!selectedPatientLabel" class="text-xs text-muted-foreground">El ECG quedará ligado a este paciente.</p>
                </div>

                <div x-show="patientMode === 'new'" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="block">
                            <span class="block text-sm font-medium text-muted-foreground mb-1">Prefijo institucional</span>
                            <select x-model="newPatientPrefixId" class="input-field">
                                <option value="">Seleccionar</option>
                                @foreach(($prefijos ?? []) as $prefijo)
                                    <option value="{{ $prefijo->prefijo_id }}">{{ $prefijo->nombre }} - {{ $prefijo->descripcion }}</option>
                                @endforeach
                            </select>
                        </label>

                    </div>
                    <p class="text-xs text-muted-foreground">Se registrará un nuevo paciente usando el flujo del módulo Pacientes.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Fecha de Nacimiento</label>
                    <input type="date" x-model="newPatientBirthDate" class="input-field" :disabled="patientMode === 'existing'" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Sexo</label>
                    <select x-model.number="patientSex" class="input-field" :disabled="patientMode === 'existing'">
                        <option value="">Seleccionar</option>
                        <option value="0">Femenino</option>
                        <option value="1">Masculino</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Peso (kg)</label>
                    <input type="number" x-model.number="patientWeight" min="0" max="400" step="0.1" class="input-field" placeholder="Ej: 70" />
                </div>
            </div>
            </div>

            <div x-show="metaError" class="alert-error mt-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                </svg>
                <span x-text="metaError"></span>
            </div>

            <div class="mt-4 flex gap-3">
                <button @click="analyzeECG()" :disabled="isAnalyzing" class="btn-primary glow-cyan flex-1">
                    <template x-if="isAnalyzing">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                            </svg>
                            Analizando ECG...
                        </span>
                    </template>
                    <template x-if="!isAnalyzing">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                            </svg>
                            Analizar ECG
                        </span>
                    </template>
                </button>

                <button @click="resetFile()" class="px-4 py-3 rounded-lg border border-border text-muted-foreground hover:bg-secondary transition-colors">
                    Cancelar
                </button>
            </div>

            <div x-show="isAnalyzing" class="mt-4">
                <div class="progress-bar">
                    <div class="progress-bar-fill animate-pulse" :style="`width:${progressPct}%;`"></div>
                </div>
                <p class="text-xs text-muted-foreground mt-2 text-center" x-text="progressMsg"></p>
            </div>
        </div>
    </div>

    <div x-show="result" class="animate-fade-in-up space-y-6">
        <div
            class="p-6 rounded-xl border"
            :style="result?.type === 'normal' ? 'background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.3);' : 'background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.3);'"
        >
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-4 rounded-xl" :style="result?.type === 'normal' ? 'background:hsl(var(--success)/0.15);' : 'background:hsl(var(--warning)/0.15);'">
                        <template x-if="result?.type === 'normal'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="result?.type !== 'normal'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold" :class="result?.type === 'normal' ? 'text-success' : 'text-warning'" x-text="result?.type === 'normal' ? 'Ritmo Normal' : 'Arritmia Detectada'"></h2>
                        <p class="text-muted-foreground" x-text="result?.rhythm"></p>
                        <p class="text-xs text-muted-foreground mt-1" x-show="result?.patientCode" x-text="'Paciente: ' + result?.patientCode"></p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="text-center px-4 py-2 rounded-lg bg-card border border-border">
                        <p class="font-mono font-bold text-primary" x-text="result?.probability + '%'"></p>
                        <p class="text-xs text-muted-foreground">Probabilidad</p>
                    </div>
                    <div class="text-center px-4 py-2 rounded-lg bg-card border border-border">
                        <p class="font-mono font-bold text-primary" x-text="result?.confidence"></p>
                        <p class="text-xs text-muted-foreground">Confianza</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" x-show="result?.top_predictions?.length > 0">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Top 5 Predicciones del Modelo
            </h3>
            <div class="space-y-2">
                <template x-for="(pred, i) in result?.top_predictions ?? []" :key="i">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono w-8 text-muted-foreground" x-text="pred.code"></span>
                        <div class="flex-1 relative h-7 rounded-lg overflow-hidden bg-muted/40">
                            <div class="h-full rounded-lg transition-all duration-500" :style="`width:${pred.probability}%; background:hsl(var(--primary)/0.6)`"></div>
                            <span class="absolute inset-0 flex items-center px-2 text-xs font-medium" x-text="pred.label"></span>
                        </div>
                        <span class="text-xs font-mono w-14 text-right text-primary font-bold" x-text="pred.probability + '%'"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="card">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Recomendaciones Clinicas
            </h3>
            <ul class="space-y-2">
                <template x-for="(rec, i) in result?.recommendations ?? []" :key="i">
                    <li class="flex items-center gap-3 p-3 rounded-lg bg-muted/40">
                        <div class="w-2 h-2 rounded-full bg-primary shrink-0"></div>
                        <span class="text-sm" x-text="rec"></span>
                    </li>
                </template>
            </ul>
        </div>

        <div x-show="chartData.length > 0" class="card">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                </svg>
                Senal ECG - Derivaciones
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="ecgCharts"></div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button @click="resetAll()" class="flex-1 px-4 py-3 rounded-lg border border-border font-semibold text-foreground hover:bg-secondary transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Nuevo Analisis
            </button>
        </div>
    </div>
</div>
@endsection

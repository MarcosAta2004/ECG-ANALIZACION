@extends('layouts.app')

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
<div x-data="ecgUpload()" class="space-y-6">
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

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Edad (anos)</label>
                    <input type="number" x-model.number="patientAge" min="0" max="120" step="1" class="input-field" placeholder="Ej: 45" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Sexo</label>
                    <select x-model.number="patientSex" class="input-field">
                        <option value="">Seleccionar</option>
                        <option value="0">Femenino</option>
                        <option value="1">Masculino</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Peso (kg)</label>
                    <input type="number" x-model.number="patientWeight" min="1" max="300" step="0.1" class="input-field" placeholder="Ej: 70" />
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
            <button @click="downloadReport()" class="btn-primary flex-1 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Descargar Reporte
            </button>
            <button @click="resetAll()" class="flex-1 px-4 py-3 rounded-lg border border-border font-semibold text-foreground hover:bg-secondary transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Nuevo Analisis
            </button>
        </div>
    </div>
</div>

<script>
const API_BASE_URL = '{{ env("ECG_API_URL", "http://localhost:8001") }}';
const ANALYZE_URL = '{{ route("analyze") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';
const LEADS_ORDER = ['I', 'II', 'III', 'aVR', 'aVL', 'aVF', 'V1', 'V2', 'V3', 'V4', 'V5', 'V6'];
const NORMAL_RECS = [
    'Continuar con monitoreo regular',
    'Proxima revision en 6 meses',
    'Mantener habitos de vida saludables',
];
const ARRHYTHMIA_RECS = [
    'Se recomienda evaluacion cardiologica',
    'Considerar monitoreo Holter 24h',
    'Evaluar factores de riesgo cardiovascular',
];

function ecgUpload() {
    return {
        file: null,
        preview: null,
        previewUrl: null,
        dragOver: false,
        patientAge: '',
        patientSex: '',
        patientWeight: '',
        metaError: null,
        isAnalyzing: false,
        progressPct: 0,
        progressMsg: 'Iniciando analisis...',
        analysisError: null,
        result: null,
        chartData: [],

        handleFileChange(event) {
            const file = event.target.files?.[0];
            if (file) this.loadFile(file);
        },

        handleDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer.files?.[0];
            if (file) this.loadFile(file);
        },

        loadFile(file) {
            this.releasePreview();
            this.file = file;
            this.result = null;
            this.analysisError = null;
            this.metaError = null;
            this.chartData = [];

            const imageTypes = ['image/png', 'image/jpg', 'image/jpeg'];
            if (imageTypes.includes(file.type)) {
                this.previewUrl = URL.createObjectURL(file);
                this.preview = this.previewUrl;
                return;
            }

            if (file.type === 'application/pdf') {
                this.fetchPdfPreview(file);
                return;
            }

            this.preview = null;
        },

        async fetchPdfPreview(file) {
            try {
                const form = new FormData();
                form.append('file', file);

                const response = await fetch(`${API_BASE_URL}/preview`, { method: 'POST', body: form });
                if (!response.ok) return;

                const data = await response.json();
                this.preview = data.image ?? null;
            } catch {}
        },

        releasePreview() {
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }
        },

        resetFile() {
            this.releasePreview();
            this.file = null;
            this.preview = null;
            this.result = null;
            this.analysisError = null;
            this.metaError = null;
            this.chartData = [];
        },

        resetAll() {
            this.resetFile();
        },

        validateMeta() {
            if (this.patientAge === '' || this.patientAge === null) {
                this.metaError = 'Ingresa la edad del paciente.';
                return false;
            }
            if (this.patientSex === '' || this.patientSex === null) {
                this.metaError = 'Selecciona el sexo del paciente.';
                return false;
            }
            if (this.patientWeight === '' || this.patientWeight === null) {
                this.metaError = 'Ingresa el peso del paciente.';
                return false;
            }

            this.metaError = null;
            return true;
        },

        async analyzeECG() {
            if (!this.file || !this.validateMeta()) return;

            this.isAnalyzing = true;
            this.analysisError = null;
            this.progressPct = 10;
            this.progressMsg = 'Enviando archivo al servidor...';

            try {
                const extension = this.file.name.split('.').pop()?.toLowerCase();
                if (['csv', 'txt'].includes(extension)) {
                    await this.analyzeCSV();
                } else {
                    await this.analyzeImageOrPdf();
                }
            } catch (error) {
                this.analysisError = error?.message ?? 'Error al analizar el archivo.';
            } finally {
                this.isAnalyzing = false;
                this.progressPct = 0;
            }
        },

        async analyzeImageOrPdf() {
            const form = new FormData();
            form.append('file', this.file);
            form.append('age', String(this.patientAge));
            form.append('sex', String(this.patientSex));
            form.append('weight', String(this.patientWeight));
            form.append('_token', CSRF_TOKEN);

            this.progressPct = 30;
            this.progressMsg = 'Digitalizando derivaciones ECG...';

            const response = await fetch(ANALYZE_URL, {
                method: 'POST',
                body: form,
            });

            this.progressPct = 85;
            this.progressMsg = 'Ejecutando modelo de prediccion...';

            if (!response.ok) {
                throw new Error(await this.extractError(response));
            }

            const data = await response.json();
            this.progressPct = 100;
            this.setResult(data);
        },

        async analyzeCSV() {
            const signal = await this.parseCSVFile(this.file);
            const csv = this.signalToCsv(signal);
            const blob = new Blob([csv], { type: 'text/csv' });
            const form = new FormData();
            form.append('file', blob, this.file.name);
            form.append('age', String(this.patientAge));
            form.append('sex', String(this.patientSex));
            form.append('weight', String(this.patientWeight));
            form.append('_token', CSRF_TOKEN);

            this.progressPct = 50;
            this.progressMsg = 'Ejecutando modelo de prediccion...';

            const response = await fetch(ANALYZE_URL, {
                method: 'POST',
                body: form,
            });

            if (!response.ok) {
                throw new Error(await this.extractError(response));
            }

            const data = await response.json();
            this.progressPct = 100;
            const signals = data.signals?.length ? data.signals : signal;
            this.setResult(data, signals);
        },

        async extractError(response) {
            let detail = 'Error en el servidor de analisis.';

            try {
                const payload = await response.json();
                detail = payload?.error ?? payload?.detail ?? payload?.message ?? detail;
            } catch {}

            return detail;
        },

        setResult(data, signalOverride = null) {
            const isNormal = data.label?.toLowerCase().includes('normal');
            const rawConfidence = Number(
                data.confidence ?? Math.round(Math.max(...(data.scores ?? [0.82])) * 100)
            );
            const confidence = Number.isFinite(rawConfidence) ? rawConfidence : 0;
            const topPredictions = (data.top_predictions ?? []).map((prediction) => ({
                ...prediction,
                probability: Number(prediction.probability ?? 0),
            }));

            this.result = {
                rhythm: isNormal ? 'Ritmo Sinusal Normal' : (data.label ?? 'Arritmia Detectada'),
                probability: Math.round(confidence),
                type: isNormal ? 'normal' : 'arritmia',
                confidence: confidence > 90 ? 'Alta' : confidence > 75 ? 'Media' : 'Baja',
                recommendations: isNormal ? NORMAL_RECS : ARRHYTHMIA_RECS,
                metrics: data.metrics ?? null,
                top_predictions: topPredictions,
            };

            const signals = signalOverride ?? data.signals ?? null;
            if (signals?.length) {
                this.chartData = signals;
                this.$nextTick(() => this.renderCharts(signals));
            }
        },

        parseCSVFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onerror = () => reject(new Error('No se pudo leer el archivo.'));
                reader.onload = () => {
                    const rows = reader.result
                        .split(/\r?\n/)
                        .map((line) => line.trim())
                        .filter(Boolean)
                        .map((line) => line.split(/[;,\t ]+/).filter(Boolean).map(Number));

                    if (!rows.length) {
                        reject(new Error('Archivo vacio.'));
                        return;
                    }

                    if (rows[0].length === 12 && rows.length >= 1000) {
                        resolve(Array.from({ length: 12 }, (_, columnIndex) => rows.slice(0, 1000).map((row) => row[columnIndex])));
                        return;
                    }

                    if (rows.length === 12) {
                        resolve(rows.map((row) => row.slice(0, 1000)));
                        return;
                    }

                    reject(new Error('El CSV debe tener 12 columnas y al menos 1000 filas, o 12 filas de senales.'));
                };
                reader.readAsText(file);
            });
        },

        signalToCsv(signal) {
            const length = Math.max(...signal.map((channel) => channel.length));
            const rows = [];

            for (let index = 0; index < length; index++) {
                rows.push(signal.map((channel) => channel[index] ?? '').join(','));
            }

            return rows.join('\n');
        },

        renderCharts(signal) {
            const container = document.getElementById('ecgCharts');
            if (!container) return;

            container.innerHTML = '';

            const canvases = [];
            signal.forEach((channel, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'p-3 rounded-lg border border-border';
                wrapper.style.background = 'hsl(var(--muted)/0.3)';

                const label = document.createElement('p');
                label.className = 'text-xs font-mono font-bold text-primary mb-2';
                label.textContent = LEADS_ORDER[index] ?? `CH${index + 1}`;

                const canvas = document.createElement('canvas');
                canvas.style.width = '100%';
                canvas.style.display = 'block';
                canvas.height = 100;

                wrapper.appendChild(label);
                wrapper.appendChild(canvas);
                container.appendChild(wrapper);
                canvases.push({ canvas, channel });
            });

            requestAnimationFrame(() => {
                canvases.forEach(({ canvas, channel }) => {
                    const width = canvas.offsetWidth || canvas.parentElement?.offsetWidth || 400;
                    canvas.width = width;

                    const ctx = canvas.getContext('2d');
                    const points = this.downsampleSignal(channel, Math.max(width * 2, 600));
                    const bounds = this.getSignalBounds(points);
                    const range = bounds.max - bounds.min || 1;
                    const height = 100;
                    const padding = 6;

                    ctx.clearRect(0, 0, width, height);
                    ctx.beginPath();
                    ctx.strokeStyle = 'hsl(var(--primary))';
                    ctx.lineWidth = 1.2;

                    points.forEach((value, pointIndex) => {
                        const px = points.length > 1 ? (pointIndex / (points.length - 1)) * width : 0;
                        const py = height - padding - ((value - bounds.min) / range) * (height - padding * 2);
                        if (pointIndex === 0) {
                            ctx.moveTo(px, py);
                        } else {
                            ctx.lineTo(px, py);
                        }
                    });

                    ctx.stroke();
                });
            });
        },

        downsampleSignal(channel, maxPoints) {
            if (!Array.isArray(channel) || channel.length <= maxPoints) {
                return channel;
            }

            const step = channel.length / maxPoints;
            const result = [];

            for (let index = 0; index < maxPoints; index++) {
                result.push(channel[Math.floor(index * step)]);
            }

            result[result.length - 1] = channel[channel.length - 1];
            return result;
        },

        getSignalBounds(points) {
            let min = Infinity;
            let max = -Infinity;

            for (const value of points) {
                if (value < min) min = value;
                if (value > max) max = value;
            }

            if (!Number.isFinite(min) || !Number.isFinite(max)) {
                return { min: 0, max: 1 };
            }

            return { min, max };
        },

        downloadReport() {
            if (!this.result) return;

            const lines = [
                'REPORTE ECG - ECG Analyzer',
                '===========================',
                `Archivo:  ${this.file?.name ?? 'N/A'}`,
                `Fecha:    ${new Date().toLocaleString('es-PE')}`,
                `Paciente: Edad ${this.patientAge} anos | Sexo ${this.patientSex == 1 ? 'Masculino' : 'Femenino'} | Peso ${this.patientWeight} kg`,
                '',
                `Resultado:    ${this.result.type === 'normal' ? 'NORMAL' : 'ARRITMIA'}`,
                `Diagnostico:  ${this.result.rhythm}`,
                `Confianza:    ${this.result.probability}% (${this.result.confidence})`,
                '',
                ...(this.result.top_predictions?.length ? [
                    'Top predicciones:',
                    ...this.result.top_predictions.map((prediction) => `  [${prediction.code}] ${prediction.label}: ${prediction.probability}%`),
                    '',
                ] : []),
                'Recomendaciones:',
                ...this.result.recommendations.map((recommendation) => `  - ${recommendation}`),
                '',
                'ADVERTENCIA: Este analisis es orientativo. Requiere validacion medica.',
                'Sistema de diagnostico asistido por IA - ECG-Net v2.1',
            ];

            const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const anchor = document.createElement('a');
            anchor.href = url;
            anchor.download = `reporte_ecg_${Date.now()}.txt`;
            anchor.click();
            URL.revokeObjectURL(url);
        },
    };
}
</script>
@endsection

@extends('dashboard.index')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-4 border-0 shadow-sm" style="min-height: 70vh;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <div class="mb-4">
                    <div class="p-4 rounded-circle bg-primary bg-opacity-10 d-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-primary font-bold mb-3">Análisis de ECG Inteligente por Estudio</h2>
                <p class="text-muted mb-4" style="max-width: 600px; margin: 0 auto;">
                    Selecciona un estudio clínico disponible y sube su electrocardiograma. La Inteligencia Artificial analizará las 12 derivaciones para proporcionar un diagnóstico automático de arritmias.
                </p>
                <button type="button" class="btn btn-primary btn-lg px-5 py-3 shadow d-flex align-items-center gap-2 mx-auto" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Comenzar Análisis
                </button>
            </div>
        </div>
    </div>
</div>

<div
    x-data="ecgStudyAnalyzer()"
    x-init="initData({{ Js::from($estudiosDisponibles ?? []) }}, '{{ route('imagenes.store') }}', '{{ url('clinico/imagenes') }}', '{{ csrf_token() }}')"
>
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true" x-ref="uploadModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title font-bold text-primary d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Seleccionar Estudio y Archivo ECG
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="resetAll()" :disabled="isAnalyzing"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    
                    <!-- File Selection Area -->
                    <div x-show="!file" class="mb-4" style="display: none;">
                        <div
                            class="border border-dashed border-primary rounded p-5 text-center cursor-pointer transition"
                            :class="dragOver ? 'bg-light border-2' : 'border-2'"
                            style="cursor: pointer;"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.fileInput.click()"
                        >
                            <input type="file" x-ref="fileInput" class="d-none" accept=".png,.jpg,.jpeg,.pdf" @change="handleFileChange($event)" />

                            <div class="d-flex flex-column align-items-center gap-3">
                                <div class="p-4 rounded-circle bg-primary bg-opacity-10 text-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="text-center mt-2">
                                    <p class="h5 font-weight-bold mb-1">Arrastra tu archivo ECG aquí</p>
                                    <p class="text-muted">o <span class="text-primary text-decoration-underline">haz clic para explorar</span></p>
                                    <p class="small text-muted mt-3 mb-0">Formatos soportados: PDF, PNG, JPG, JPEG</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Estudio -->
                    <div x-show="file" style="display: none;">
                        <div class="card border mb-4 bg-light bg-opacity-50 shadow-sm">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2 rounded bg-white border">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="h6 font-weight-bold text-truncate mb-0" x-text="file?.name"></p>
                                        <p class="text-muted small mb-0" x-text="file ? (file.size / 1024).toFixed(1) + ' KB' : ''"></p>
                                    </div>
                                    <button @click="resetFile()" class="btn btn-sm btn-outline-danger shadow-sm" :disabled="isAnalyzing">
                                        Cambiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Estudio Data -->
                        <div class="border-top pt-4">
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-muted mb-1">Seleccionar Estudio Clínico</label>
                                <select x-model="selectedEstudioId" class="form-select form-select-lg" :disabled="isAnalyzing">
                                    <option value="">-- Seleccione un estudio disponible --</option>
                                    <template x-for="estudio in estudios" :key="estudio.estudio_id">
                                        <option :value="estudio.estudio_id" x-text="`Estudio #${estudio.estudio_id} - Paciente: ${estudio.paciente?.codigo_generado ?? 'Desconocido'}`"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="row g-3" x-show="selectedEstudio" style="display: none;">
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <p class="small text-muted mb-1 text-uppercase fw-bold">Paciente Asignado</p>
                                        <p class="font-weight-bold mb-0" x-text="selectedEstudio?.paciente?.codigo_generado"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <p class="small text-muted mb-1 text-uppercase fw-bold">Detalles de Estudio</p>
                                        <p class="mb-0">Edad: <span x-text="selectedEstudio?.edad"></span> años</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="metaError" class="alert alert-danger mt-4 py-2 small mb-0 shadow-sm" style="display: none;">
                            <span x-text="metaError"></span>
                        </div>

                        <!-- Loading State -->
                        <div x-show="isAnalyzing" class="mt-4 pt-4 border-top" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="font-weight-bold text-primary">Subiendo y Analizando ECG...</span>
                            </div>
                            <div class="progress shadow-sm" style="height: 12px; border-radius: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-100" role="progressbar"></div>
                            </div>
                            <p class="small text-muted mt-2 text-center font-italic" x-text="progressMsg"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0" x-show="file" style="display: none;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" @click="resetAll()" :disabled="isAnalyzing">Cancelar</button>
                    <button type="button" class="btn btn-primary px-4 shadow d-flex align-items-center gap-2" @click="uploadAndAnalyze()" :disabled="isAnalyzing || !selectedEstudioId">
                        <template x-if="isAnalyzing">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </template>
                        <template x-if="!isAnalyzing">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </template>
                        <span x-text="isAnalyzing ? 'Procesando...' : 'Analizar ECG'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" x-ref="resultModal">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title font-bold d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Resultados del Análisis ECG
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light p-4">
                    
                    <div x-show="result" class="mb-2" style="display: none;">
                        <!-- Diagnostic Banner -->
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-body p-4" :class="result?.is_normal ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10'" style="border-radius: 12px;">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="p-3 rounded-circle shadow-sm bg-white" :class="result?.is_normal ? 'border border-success' : 'border border-warning'">
                                            <template x-if="result?.is_normal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="48" height="48">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </template>
                                            <template x-if="!result?.is_normal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="48" height="48">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <div>
                                            <h2 class="font-weight-bold mb-1" :class="result?.is_normal ? 'text-success' : 'text-warning'" x-text="result?.is_normal ? 'Ritmo Normal Detectado' : 'Posible Arritmia Detectada'"></h2>
                                            <p class="h4 text-dark mb-0 opacity-75" x-text="result?.ritmo"></p>
                                            <p class="text-muted mt-2 mb-0 fw-bold" x-show="result?.patient_code" x-text="'ID Paciente: ' + result?.patient_code"></p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <div class="text-center px-4 py-3 rounded bg-white shadow-sm border border-light">
                                            <p class="display-6 font-monospace font-weight-bold text-primary mb-0" x-text="(result?.probabilidad * 100).toFixed(1) + '%'"></p>
                                            <p class="small text-muted mb-0 fw-bold text-uppercase">Probabilidad</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Left Column: Top Predictions -->
                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                        <h6 class="font-bold text-primary text-uppercase mb-0 d-flex align-items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            Top Predicciones
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-3">
                                            <template x-for="(pred, i) in result?.top_predictions ?? []" :key="i">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="small font-monospace text-muted fw-bold" style="width: 35px;" x-text="pred.code"></span>
                                                    <div class="flex-grow-1 position-relative rounded overflow-hidden bg-light" style="height: 36px;">
                                                        <div class="h-100 rounded transition" :style="`width:${pred.probability}%; background-color: var(--bs-primary); opacity: ${1 - (i * 0.15)};`"></div>
                                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-3 small font-weight-bold text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);" x-text="pred.label"></span>
                                                    </div>
                                                    <span class="font-monospace text-primary font-weight-bold" style="width: 55px; font-size: 1.1rem;" x-text="pred.probability + '%'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <a href="{{ route('predicciones.index') }}" class="btn btn-light px-4 shadow-sm">Ver Resumen</a>
                    <button type="button" class="btn btn-primary px-4 shadow d-flex align-items-center gap-2" 
                        @click="startNew()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Comenzar Nuevo Análisis
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inline Script to ensure it runs immediately without requiring JS recompile -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
window.ecgStudyAnalyzer = () => ({
        estudios: [],
        storeUrl: '',
        analyzeBaseUrl: '',
        csrfToken: '',
        
        file: null,
        dragOver: false,
        isAnalyzing: false,
        progressMsg: '',
        metaError: '',
        selectedEstudioId: '',
        
        result: null,

        initData(estudios, storeUrl, analyzeBaseUrl, csrfToken) {
            this.estudios = estudios;
            this.storeUrl = storeUrl;
            this.analyzeBaseUrl = analyzeBaseUrl;
            this.csrfToken = csrfToken;
        },

        get selectedEstudio() {
            return this.estudios.find(e => e.estudio_id == this.selectedEstudioId);
        },

        handleDrop(e) {
            this.dragOver = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.file = files[0];
                this.metaError = '';
            }
        },

        handleFileChange(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.file = files[0];
                this.metaError = '';
            }
        },

        resetFile() {
            this.file = null;
            if(this.$refs.fileInput) this.$refs.fileInput.value = '';
            this.metaError = '';
        },

        resetAll() {
            this.resetFile();
            this.selectedEstudioId = '';
            this.result = null;
            this.isAnalyzing = false;
            this.metaError = '';
        },
        
        startNew() {
            let resM = bootstrap.Modal.getInstance(document.getElementById('resultModal'));
            if(resM) resM.hide();
            
            if (this.selectedEstudioId) {
                this.estudios = this.estudios.filter(e => e.estudio_id != this.selectedEstudioId);
            }
            
            this.resetAll();
            setTimeout(() => {
                let uploadM = new bootstrap.Modal(document.getElementById('uploadModal'));
                uploadM.show();
            }, 500);
        },

        async uploadAndAnalyze() {
            if (!this.file || !this.selectedEstudioId) {
                this.metaError = 'Por favor, selecciona un estudio y un archivo.';
                return;
            }

            this.isAnalyzing = true;
            this.metaError = '';
            this.progressMsg = 'Subiendo archivo al servidor...';

            try {
                // Paso 1: Subir Archivo
                const formData = new FormData();
                formData.append('archivo', this.file);
                formData.append('estudio_id', this.selectedEstudioId);

                const uploadResp = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const uploadData = await uploadResp.json();
                
                if (!uploadResp.ok) {
                    let errMsg = uploadData.message || uploadData.error || 'Error al subir el archivo';
                    if (uploadData.errors) {
                        errMsg = Object.values(uploadData.errors)[0][0];
                    }
                    throw new Error(errMsg);
                }

                if (!uploadData.success || !uploadData.imagen_id) {
                    throw new Error('No se obtuvo el ID de la imagen.');
                }

                this.progressMsg = 'Enviando imagen al modelo de IA para análisis...';

                // Paso 2: Analizar Archivo
                const analyzeUrl = `${this.analyzeBaseUrl}/${uploadData.imagen_id}/ejecutar-analisis`;
                
                const analyzeResp = await fetch(analyzeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const analyzeData = await analyzeResp.json();

                if (!analyzeResp.ok) {
                    throw new Error(analyzeData.error || analyzeData.message || 'Error durante el análisis');
                }

                // Éxito
                this.result = analyzeData;
                
                // Mostrar resultados
                let uploadM = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                if (uploadM) uploadM.hide();
                setTimeout(() => {
                    let resM = new bootstrap.Modal(document.getElementById('resultModal'));
                    resM.show();
                }, 500);

            } catch (err) {
                console.error(err);
                this.metaError = err.message;
            } finally {
                this.isAnalyzing = false;
            }
        }
    });
</script>
@endsection


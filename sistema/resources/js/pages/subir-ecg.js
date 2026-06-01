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

export function ecgUpload(config = {}) {
    const root = document.querySelector('[data-api-url]');
    const apiBaseUrl = root?.dataset.apiUrl ?? 'http://localhost:8001';
    const analyzeUrl = root?.dataset.analyzeUrl ?? '/analyze';
    const csrfToken = root?.dataset.csrfToken ?? '';
    const patients = config.pacientes ?? [];
    const prefijos = config.prefijos ?? [];

    return {
        apiBaseUrl,
        analyzeUrl,
        csrfToken,
        patients,
        prefijos,
        file: null,
        preview: null,
        previewUrl: null,
        dragOver: false,
        patientMode: patients.length ? 'existing' : 'new',
        selectedPatientId: '',
        selectedPatientLabel: '',
        newPatientPrefixId: prefijos[0]?.prefijo_id ?? '',
        newPatientBirthDate: '',
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

                const response = await fetch(`${this.apiBaseUrl}/preview`, { method: 'POST', body: form });
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
            this.patientMode = this.patients.length ? 'existing' : 'new';
            this.selectedPatientId = '';
            this.selectedPatientLabel = '';
            this.newPatientPrefixId = this.prefijos[0]?.prefijo_id ?? '';
            this.newPatientBirthDate = '';
            this.patientAge = '';
            this.patientSex = '';
            this.patientWeight = '';
            this.progressPct = 0;
            this.progressMsg = 'Iniciando analisis...';
            this.isAnalyzing = false;
        },

        setPatientMode(mode) {
            this.patientMode = mode;
            this.metaError = null;

            if (mode === 'existing') {
                this.newPatientBirthDate = '';
            } else {
                this.selectedPatientId = '';
                this.selectedPatientLabel = '';
            }
        },

        applySelectedPatient() {
            const patient = this.patients.find((item) => String(item.paciente_id) === String(this.selectedPatientId));

            if (!patient) {
                this.selectedPatientLabel = '';
                return;
            }

            this.selectedPatientLabel = `${patient.codigo_generado}${patient.edad ? ` · ${patient.edad} años` : ''}`;
            this.newPatientBirthDate = patient.fecha_nacimiento ? patient.fecha_nacimiento.split('T')[0] : '';
            this.patientSex = patient.sexo === 'M' ? 1 : patient.sexo === 'F' ? 0 : '';
            this.patientWeight = patient.peso ?? '';
        },

        validateMeta() {
            if (this.newPatientBirthDate === '' || this.newPatientBirthDate === null) {
                this.metaError = 'Ingresa la fecha de nacimiento del paciente.';
                return false;
            }
            const birthDate = new Date(this.newPatientBirthDate);
            if (birthDate >= new Date()) {
                this.metaError = 'La fecha de nacimiento no puede ser futura.';
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
            if (this.patientWeight < 0 || this.patientWeight > 400) {
                this.metaError = 'El peso es invalido. Debe estar entre 0 y 400 kg.';
                return false;
            }
            if (this.patientMode === 'existing' && !this.selectedPatientId) {
                this.metaError = 'Selecciona un paciente existente.';
                return false;
            }
            if (this.patientMode === 'new' && !this.newPatientPrefixId) {
                this.metaError = 'Selecciona el prefijo del nuevo paciente.';
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
            const calculatedAge = this.getCalculatedAge();
            const form = new FormData();
            form.append('file', this.file);
            form.append('age', String(calculatedAge));
            form.append('sex', String(this.patientSex));
            form.append('weight', String(this.patientWeight));
            form.append('patient_mode', this.patientMode);
            form.append('patient_id', String(this.selectedPatientId || ''));
            form.append('prefijo_id', String(this.newPatientPrefixId || ''));
            form.append('fecha_nacimiento', String(this.newPatientBirthDate || ''));
            form.append('_token', this.csrfToken);

            this.progressPct = 30;
            this.progressMsg = 'Digitalizando derivaciones ECG...';

            const response = await fetch(this.analyzeUrl, {
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
            
            const calculatedAge = this.getCalculatedAge();
            const form = new FormData();
            form.append('file', blob, this.file.name);
            form.append('age', String(calculatedAge));
            form.append('sex', String(this.patientSex));
            form.append('weight', String(this.patientWeight));
            form.append('patient_mode', this.patientMode);
            form.append('patient_id', String(this.selectedPatientId || ''));
            form.append('prefijo_id', String(this.newPatientPrefixId || ''));
            form.append('fecha_nacimiento', String(this.newPatientBirthDate || ''));
            form.append('_token', this.csrfToken);

            this.progressPct = 50;
            this.progressMsg = 'Ejecutando modelo de prediccion...';

            const response = await fetch(this.analyzeUrl, {
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

        getCalculatedAge() {
            if (!this.newPatientBirthDate) return 0;
            const birthDate = new Date(this.newPatientBirthDate);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        },

        setResult(data, signalOverride = null) {
            const label = String(data.label ?? '').trim().toUpperCase();
            const isNormal = Boolean(
                data.is_normal ||
                data.type === 'normal' ||
                label === 'NORM' ||
                label.includes('NORMAL')
            );
            const rawConfidence = Number(
                data.confidence ?? Math.round(Math.max(...(data.scores ?? [0.82])) * 100)
            );
            const confidence = Number.isFinite(rawConfidence) ? rawConfidence : 0;
            const topPredictions = (data.top_predictions ?? []).map((prediction) => ({
                ...prediction,
                probability: Number(prediction.probability ?? 0),
            }));

            // Usar la probabilidad del top 1 para el badge (coincide con la barra del Top 5)
            const topProb = topPredictions.length > 0 ? topPredictions[0].probability : confidence;

            this.result = {
                rhythm: isNormal ? 'Ritmo Sinusal Normal' : (data.label ?? 'Arritmia Detectada'),
                probability: Math.round(topProb * 10) / 10,   // 1 decimal, ej: 87.1
                type: isNormal ? 'normal' : 'arritmia',
                confidence: confidence > 90 ? 'Alta' : confidence > 75 ? 'Media' : 'Baja',
                patientCode: data.patient_code ?? null,
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
                wrapper.className = 'p-3 rounded-lg border border-border surface-muted-subtle';

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
                'REPORTE ECG - ECG Analizacion',
                '===========================',
                `Archivo:  ${this.file?.name ?? 'N/A'}`,
                `Fecha:    ${new Date().toLocaleString('es-PE')}`,
                `Paciente: Edad ${this.getCalculatedAge()} anos | Sexo ${this.patientSex == 1 ? 'Masculino' : 'Femenino'} | Peso ${this.patientWeight} kg`,
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

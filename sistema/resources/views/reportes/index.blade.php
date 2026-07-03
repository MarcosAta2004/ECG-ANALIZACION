@extends('dashboard.index')

@section('content')

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </symbol>
        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
    </svg>

    @if(session('message') || session('error'))
        @php
            $isError = session('error') || session('alert') === 'danger';
            $isWarning = session('alert') === 'warning';
            $alertClass = $isError ? 'alert-danger' : ($isWarning ? 'alert-warning' : 'alert-success');
            $icon = $isError || $isWarning ? 'exclamation-triangle-fill' : 'check-circle-fill';
        @endphp

        <div class="bd-example mb-3">
            <div class="alert {{ $alertClass }} alert-dismissible fade show d-flex align-items-center" role="alert">
                <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                    <use xlink:href="#{{ $icon }}" />
                </svg>
                <div>{{ session('message') ?? session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Pacientes registrados</p>
                    <h3 class="mb-0">{{ number_format($reportStats['patients']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Analisis disponibles</p>
                    <h3 class="mb-0">{{ number_format($reportStats['analyses']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Reportes oficiales</p>
                    <h3 class="mb-0">{{ number_format($reportStats['official_reports']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="header-title">
                        <h4 class="card-title mb-0">Reportes Oficiales</h4>
                    </div>
                    <form method="GET" action="{{ route('reportes.index') }}" class="d-flex gap-2">
                        <input type="text"
                               class="form-control"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Buscar paciente, estudio o ritmo"
                               autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </form>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" role="grid">
                            <thead>
                                <tr class="ligth">
                                    <th class="text-center">#</th>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Diagnostico</th>
                                    <th>IA</th>
                                    <th>Estado</th>
                                    <th style="min-width: 150px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estudios as $estudio)
                                    @php
                                        $diagnostico = $estudio->diagnostico;
                                        $ritmoMedico = $diagnostico?->ritmoCardiaco;
                                        $prediccion = $estudio->imagen?->prediccion;
                                        $ritmoIa = $prediccion?->ritmoCardiaco ?? $prediccion?->ritmo;
                                        $paciente = $estudio->paciente?->codigo_generado ?? 'N/A';
                                    @endphp
                                    <tr>
                                        <td class="text-center"><span class="badge bg-dark">#{{ $estudio->estudio_id }}</span></td>
                                        <td><span class="badge bg-primary">{{ $paciente }}</span></td>
                                        <td>{{ $estudio->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            <strong>{{ $ritmoMedico?->nombre ?? 'N/A' }}</strong>
                                            <div class="small text-muted">{{ $ritmoMedico?->label ?? '' }}</div>
                                        </td>
                                        <td>
                                            {{ $ritmoIa?->nombre ?? 'SIN PROCESAR' }}
                                            <div class="small text-muted">
                                                {{ isset($prediccion->probabilidad) ? number_format($prediccion->probabilidad * 100, 2) . '%' : '' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($estudio->reporte?->ruta_pdf)
                                                <span class="badge bg-success">Generado</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Listo para generar</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-ver-reporte"
                                                    data-url="{{ route('reportes.verPDF', $estudio->estudio_id) }}"
                                                    data-estudio="#{{ $estudio->estudio_id }}"
                                                    data-paciente="{{ $paciente }}"
                                                    title="Ver reporte PDF">
                                                <svg width="18" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" class="me-1">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor"
                                                        stroke-width="1.6" />
                                                </svg>
                                                Ver reporte
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No hay estudios con diagnostico final para generar reportes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        {{ $estudios->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="header-title">
                        <h4 class="card-title mb-0">Reporte consolidado Excel</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reportes.download') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo de reporte</label>
                                <select name="mode" id="excelMode" class="form-select">
                                    <option value="all">Todos los pacientes</option>
                                    <option value="selected">Algunos pacientes</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Pacientes</label>
                                <div class="border rounded p-3" style="max-height: 170px; overflow:auto;">
                                    @forelse($patients as $patient)
                                        <label class="d-inline-flex align-items-center gap-2 me-3 mb-2">
                                            <input type="checkbox"
                                                   name="patients[]"
                                                   value="{{ $patient['patient_identifier'] }}"
                                                   class="excel-patient-check"
                                                   disabled>
                                            <span>
                                                <strong>{{ $patient['patient_identifier'] }}</strong>
                                                <small class="text-muted">({{ number_format($patient['total']) }})</small>
                                            </span>
                                        </label>
                                    @empty
                                        <span class="text-muted">No hay pacientes registrados para exportar.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success">Descargar Excel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reporteViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Reporte oficial
                        <span id="reporteViewerPaciente" class="badge bg-primary ms-2"></span>
                        <span id="reporteViewerEstudio" class="text-muted small ms-2"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="background:#1a1a2e; height: 800px;">
                    <iframe
                        id="reporteIframe"
                        src=""
                        width="100%"
                        height="100%"
                        style="border:none;">
                    </iframe>
                </div>
                <div class="modal-footer">
                    <a id="btnDescargarPdf" href="#" class="btn btn-primary d-none">Descargar PDF</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar visor</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            'use strict';

            const modalElement = document.getElementById('reporteViewerModal');
            const iframe = document.getElementById('reporteIframe');
            const paciente = document.getElementById('reporteViewerPaciente');
            const estudio = document.getElementById('reporteViewerEstudio');
            const btnDescargarPdf = document.getElementById('btnDescargarPdf');

            if (modalElement && iframe) {
                document.querySelectorAll('.btn-ver-reporte').forEach((button) => {
                    button.addEventListener('click', function () {
                        const url = this.dataset.url;
                        iframe.src = url;
                        paciente.textContent = this.dataset.paciente || 'N/A';
                        estudio.textContent = this.dataset.estudio || '';

                        if (btnDescargarPdf) {
                            btnDescargarPdf.href = url + '/descargar';
                            btnDescargarPdf.classList.remove('d-none');
                        }

                        bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    });
                });

                modalElement.addEventListener('hidden.bs.modal', function () {
                    iframe.src = '';
                    paciente.textContent = '';
                    estudio.textContent = '';
                    if (btnDescargarPdf) btnDescargarPdf.classList.add('d-none');
                });
            }

            const excelMode = document.getElementById('excelMode');
            const checks = document.querySelectorAll('.excel-patient-check');

            if (excelMode) {
                const syncChecks = function () {
                    checks.forEach((check) => {
                        check.disabled = excelMode.value !== 'selected';
                        if (check.disabled) {
                            check.checked = false;
                        }
                    });
                };

                excelMode.addEventListener('change', syncChecks);
                syncChecks();
            }
        })();
    </script>

@endsection

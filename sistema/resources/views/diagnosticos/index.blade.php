@extends('dashboard.index')

@section('content')

{{-- SVGs ocultos --}}
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
    </symbol>
    <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
    </symbol>
    <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
    </symbol>
</svg>

{{-- Alertas de sesión --}}
@if(session('message'))
    @php
        $alertClass = 'alert-info'; $icon = 'info-fill';
        if (session('alert') === 'success')     { $alertClass = 'alert-success'; $icon = 'check-circle-fill'; }
        elseif (session('alert') === 'warning') { $alertClass = 'alert-warning'; $icon = 'exclamation-triangle-fill'; }
        elseif (session('alert') === 'danger')  { $alertClass = 'alert-danger';  $icon = 'exclamation-triangle-fill'; }
        elseif (session('alert') === 'primary') { $alertClass = 'alert-primary'; $icon = 'info-fill'; }
    @endphp
    <div class="bd-example mb-3">
        <div class="alert {{ $alertClass }} alert-dismissible fade show d-flex align-items-center" role="alert">
            <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#{{ $icon }}"/></svg>
            <div>{{ session('message') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- ================================================================
     TABLA PRINCIPAL
     ================================================================ --}}
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="header-title">
                    <h4 class="card-title">Lista de Diagnósticos</h4>
                    <p class="mb-0 text-muted small">Valoraciones registradas por el cardiólogo</p>
                </div>
                @can('diagnosticos.store')
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn {{ $estudios->count() > 0 ? 'btn-warning text-dark fw-semibold' : 'btn-primary' }} position-relative" data-bs-toggle="modal" data-bs-target="#createDiagnosticoModal">
                        @if($estudios->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="z-index: 10;">
                                {{ $estudios->count() }}
                            </span>
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-1">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pendientes por Valorar
                        @else
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-1">
                                <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Nuevo Diagnóstico
                        @endif
                    </button>
                </div>
                @endcan
            </div>

            {{-- Barra de búsqueda --}}
            <div class="card-body border-bottom pb-3">
                <form method="GET" action="{{ route('diagnosticos.index') }}" class="d-flex gap-2 align-items-center">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 15.4183 19 11Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por código de paciente..."
                            autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('diagnosticos.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
            </div>

            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle" role="grid">
                        <thead>
                            <tr class="ligth">
                                <th class="text-center">#</th>
                                <th>Paciente</th>
                                <th>Ritmo Diagnosticado</th>
                                <th>Label</th>
                                <th>Concordancia IA</th>
                                <th>Registrado por</th>
                                <th>Fecha Revisión</th>
                                <th>Observación</th>
                                <th>Estado</th>
                                <th class="text-center" style="min-width:110px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($diagnosticos as $diagnostico)
                                @php
                                    $ritmo       = $diagnostico->ritmoCardiaco;
                                    $registrador = $diagnostico->registrador;
                                    $paciente    = $diagnostico->estudio?->paciente;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-dark">#{{ $diagnostico->diagnostico_id }}</span>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $paciente?->codigo_generado ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td class="fw-semibold text-nowrap">
                                        {{ $ritmo?->nombre ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <code class="badge bg-dark">{{ $ritmo?->label ?? '—' }}</code>
                                    </td>

                                    <td>
                                        @if(is_null($diagnostico->concordancia))
                                            <span class="badge bg-secondary">Sin evaluar</span>
                                        @elseif($diagnostico->concordancia)
                                            <span class="badge bg-success">✓ Sí</span>
                                        @else
                                            <span class="badge bg-danger">✗ No</span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap small">
                                        {{ $registrador?->nombres ?? $registrador?->login ?? '—' }}
                                    </td>

                                    <td class="text-nowrap small">
                                        {{ $diagnostico->fecha_revision?->format('d/m/Y H:i') ?? '—' }}
                                    </td>

                                    <td style="max-width:180px">
                                        @if($diagnostico->observacion)
                                            <span class="d-inline-block text-truncate" style="max-width:160px"
                                                  title="{{ $diagnostico->observacion }}">
                                                {{ $diagnostico->observacion }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($diagnostico->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1 list-user-action">
                                            {{-- Editar --}}
                                            @can('diagnosticos.update')
                                            <button class="btn btn-sm btn-icon btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editDiagnosticoModal{{ $diagnostico->diagnostico_id }}"
                                                    title="Editar">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            </button>
                                            @endcan

                                            @can('diagnosticos.activar')
                                            @if($diagnostico->estado == 1)
                                                <button type="button" class="btn btn-sm btn-icon btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $diagnostico->diagnostico_id }}"
                                                        title="Desactivar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-icon btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activateModal{{ $diagnostico->diagnostico_id }}"
                                                        title="Activar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3345 2.75024H7.66549C4.64449 2.75024 2.75049 4.88924 2.75049 7.91624V16.0842C2.75049 19.1112 4.63549 21.2502 7.66549 21.2502H16.3335C19.3645 21.2502 21.2505 19.1112 21.2505 16.0842V7.91624C21.2505 4.88924 19.3645 2.75024 16.3345 2.75024Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8.43994 12.0002L10.8139 14.3732L15.5599 9.6272" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        No hay diagnósticos registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $diagnosticos->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL ECG VIEWER (superpuesto sobre formulario)
     ================================================================ --}}
<div class="modal fade" id="ecgViewerModal" tabindex="-2" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                        <path d="M9 12h2l2-4 2 8 2-4h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    ECG del Estudio — <span id="ecgViewerPaciente" class="text-primary ms-1"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background:#1a1a2e; height: 800px;">
                <iframe
                    id="ecgIframe"
                    src=""
                    width="100%"
                    height="100%"
                    style="border:none;">
                </iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar visor</button>
            </div>
        </div>
    </div>
</div>
<div id="ecgViewerBackdrop" class="modal-backdrop fade" style="display:none; z-index:1055;"></div>

{{-- ================================================================
     MODAL: CREAR DIAGNÓSTICO
     ================================================================ --}}
<div class="modal fade" id="createDiagnosticoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('diagnosticos.store') }}" method="POST" id="formCreateDiagnostico">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Diagnóstico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    {{-- Estudio + botón ver ECG --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estudio (Paciente) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-select estudio-select" name="estudio_id"
                                    id="create_estudio_id" required
                                    data-infourl="{{ url('/clinico/diagnosticos/estudio') }}"
                                    data-prefix="create">
                                <option value="">Seleccionar estudio sin diagnóstico...</option>
                                @foreach($estudios as $estudio)
                                    <option value="{{ $estudio->estudio_id }}">
                                        Estudio #{{ $estudio->estudio_id }} — {{ $estudio->paciente?->codigo_generado ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button"
                                    class="btn btn-outline-info btn-ver-ecg"
                                    id="create_btnVerEcg"
                                    disabled
                                    title="Ver ECG del estudio seleccionado">
                                <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                                Ver ECG
                            </button>
                        </div>
                        <small class="text-muted">Solo estudios activos sin diagnóstico previo.</small>
                    </div>

                    {{-- Panel de predicción IA (solo lectura) --}}
                    <div class="card border-0 bg-light mb-3 ia-panel" id="create_iaPanel" style="display:none;">
                        <div class="card-body py-2 px-3">
                            <p class="mb-2 small fw-semibold text-muted text-uppercase" style="letter-spacing:.05em;">
                                <svg width="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-1">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Resultado de la IA (solo lectura)
                            </p>
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <label class="form-label small mb-1">Ritmo detectado</label>
                                    <input type="text" class="form-control form-control-sm"
                                           id="create_iaRitmo" disabled placeholder="Sin predicción">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small mb-1">Label (código)</label>
                                    <input type="text" class="form-control form-control-sm"
                                           id="create_iaLabel" disabled placeholder="—">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small mb-1">Probabilidad</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm"
                                               id="create_iaProb" disabled placeholder="—">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sin predicción IA --}}
                    <div id="create_noIaAlert" style="display:none;">
                        <div class="alert alert-warning py-2 mb-3 small">
                            <svg class="bi flex-shrink-0 me-1" width="16" height="16"><use xlink:href="#exclamation-triangle-fill"/></svg>
                            Este estudio aún no tiene predicción de la IA registrada.
                        </div>
                    </div>

                    {{-- Ritmo diagnosticado --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ritmo Cardíaco Diagnosticado <span class="text-danger">*</span></label>
                        <select class="form-select" name="ritmo_id" required>
                            <option value="">Seleccionar ritmo...</option>
                            @foreach($ritmos as $r)
                                <option value="{{ $r->ritmo_id }}">[{{ $r->label }}] {{ $r->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Concordancia --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Concordancia con IA</label>
                        <select class="form-select" name="concordancia">
                            <option value="">Sin evaluar</option>
                            <option value="1">Sí — Confirma la predicción</option>
                            <option value="0">No — Discrepa de la predicción</option>
                        </select>
                    </div>

                    {{-- Observación --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Observación</label>
                        <textarea class="form-control" name="observacion" rows="3"
                                  placeholder="Notas clínicas adicionales..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Diagnóstico</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODALES POR REGISTRO: Editar / Desactivar / Activar
     ================================================================ --}}
@foreach($diagnosticos as $diagnostico)
    @php
        $ritmo       = $diagnostico->ritmoCardiaco;
        $paciente    = $diagnostico->estudio?->paciente;
        $pacCodigo   = $paciente?->codigo_generado ?? 'N/A';
        $ritmoNombre = $ritmo?->nombre ?? 'N/A';
        $eid         = $diagnostico->estudio_id;
    @endphp

    {{-- Modal Editar --}}
    <div class="modal fade" id="editDiagnosticoModal{{ $diagnostico->diagnostico_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('diagnosticos.update', $diagnostico->diagnostico_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Editar Diagnóstico —
                            <span class="badge bg-primary ms-1">{{ $pacCodigo }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Estudio (solo lectura) + botón ver ECG --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Estudio</label>
                            <div class="input-group">
                                <input type="text" class="form-control"
                                       value="Estudio #{{ $eid }} — {{ $pacCodigo }}"
                                       disabled>
                                <button type="button"
                                        class="btn btn-outline-info btn-ver-ecg-edit"
                                        data-estudio="{{ $eid }}"
                                        data-paciente="{{ $pacCodigo }}"
                                        data-infourl="{{ url('/clinico/diagnosticos/estudio/' . $eid . '/info') }}"
                                        title="Ver ECG del estudio">
                                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>
                                    </svg>
                                    Ver ECG
                                </button>
                            </div>
                        </div>

                        {{-- Panel IA precargado (readonly) --}}
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body py-2 px-3">
                                <p class="mb-2 small fw-semibold text-muted text-uppercase" style="letter-spacing:.05em;">
                                    <svg width="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-1">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Resultado de la IA (solo lectura)
                                </p>
                                <div class="row g-2">
                                    @php
                                        $imgEdit  = $diagnostico->estudio?->imagen;
                                        $predEdit = $imgEdit?->prediccion;
                                        $riaEdit  = $predEdit?->ritmoCardiaco;
                                    @endphp
                                    <div class="col-sm-5">
                                        <label class="form-label small mb-1">Ritmo detectado</label>
                                        <input type="text" class="form-control form-control-sm" disabled
                                               value="{{ $riaEdit?->nombre ?? 'Sin predicción' }}">
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="form-label small mb-1">Label</label>
                                        <input type="text" class="form-control form-control-sm" disabled
                                               value="{{ $riaEdit?->label ?? '—' }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small mb-1">Probabilidad</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm" disabled
                                                   value="{{ $predEdit ? round($predEdit->probabilidad * 100, 2) : '—' }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Ritmo diagnosticado --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ritmo Cardíaco Diagnosticado <span class="text-danger">*</span></label>
                            <select class="form-select" name="ritmo_id" required>
                                @foreach($ritmos as $r)
                                    <option value="{{ $r->ritmo_id }}"
                                        {{ $r->ritmo_id == $diagnostico->ritmo_id ? 'selected' : '' }}>
                                        [{{ $r->label }}] {{ $r->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Concordancia --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Concordancia con IA</label>
                            <select class="form-select" name="concordancia">
                                <option value=""  {{ is_null($diagnostico->concordancia)  ? 'selected' : '' }}>Sin evaluar</option>
                                <option value="1" {{ $diagnostico->concordancia === true  ? 'selected' : '' }}>Sí — Confirma la predicción</option>
                                <option value="0" {{ $diagnostico->concordancia === false ? 'selected' : '' }}>No — Discrepa de la predicción</option>
                            </select>
                        </div>

                        {{-- Observación --}}
                        <div class="mb-1">
                            <label class="form-label fw-semibold">Observación</label>
                            <textarea class="form-control" name="observacion" rows="3">{{ $diagnostico->observacion }}</textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Desactivar --}}
    <div class="modal fade" id="deleteModal{{ $diagnostico->diagnostico_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('diagnosticos.destroy', $diagnostico->diagnostico_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Desactivación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                            <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#exclamation-triangle-fill"/></svg>
                            <div>
                                ¿Deseas <strong>desactivar</strong> el diagnóstico
                                <strong>{{ $ritmoNombre }}</strong> del paciente
                                <strong>{{ $pacCodigo }}</strong>?
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Sí, Desactivar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Activar --}}
    <div class="modal fade" id="activateModal{{ $diagnostico->diagnostico_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('diagnosticos.activar', $diagnostico->diagnostico_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Activación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                            <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#info-fill"/></svg>
                            <div>
                                ¿Deseas <strong>activar</strong> el diagnóstico
                                <strong>{{ $ritmoNombre }}</strong> del paciente
                                <strong>{{ $pacCodigo }}</strong>?
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Sí, Activar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- ================================================================
     JAVASCRIPT — lógica interactiva del formulario
     ================================================================ --}}
<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * abrirEcgViewer — Abre el modal visor cargando el ECG/PDF en un <iframe>.
     * Es el método más sencillo y compatible: idéntico al ejemplo de referencia.
     */
    function abrirEcgViewer(ecgUrl, paciente, modalOrigenElement) {
        const titleSpan = document.getElementById('ecgViewerPaciente');
        const iframe    = document.getElementById('ecgIframe');

        titleSpan.textContent = paciente ?? '';
        iframe.src            = ecgUrl; // Asignar URL al iframe directo

        // Ocultar el modal de origen (Crear o Editar)
        let modalOrigen = null;
        if (modalOrigenElement) {
            modalOrigen = bootstrap.Modal.getInstance(modalOrigenElement);
            if (modalOrigen) modalOrigen.hide();
        }

        const viewerModalElement = document.getElementById('ecgViewerModal');
        const viewerModal        = new bootstrap.Modal(viewerModalElement);

        viewerModal.show();

        // Al cerrar el visor: limpiar iframe y volver al modal original
        viewerModalElement.addEventListener('hidden.bs.modal', function onHidden() {
            iframe.src = ''; // Limpiar para que no se quede cargado
            viewerModalElement.removeEventListener('hidden.bs.modal', onHidden);

            if (modalOrigen) {
                setTimeout(() => { modalOrigen.show(); }, 150);
            }
        });
    }

    /** fetchEstudioInfo — Llama al endpoint AJAX y devuelve Promise con los datos del estudio. */
    async function fetchEstudioInfo(baseUrl, estudioId) {
        const resp = await fetch(`${baseUrl}/${estudioId}/info`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error('Error al obtener datos del estudio');
        return resp.json();
    }

    // ── Modal CREAR: lógica al seleccionar estudio ────────────────────────────
    const createSelect  = document.getElementById('create_estudio_id');
    const createBtnEcg  = document.getElementById('create_btnVerEcg');
    const createIaPanel = document.getElementById('create_iaPanel');
    const createNoIa    = document.getElementById('create_noIaAlert');
    const createIaRitmo = document.getElementById('create_iaRitmo');
    const createIaLabel = document.getElementById('create_iaLabel');
    const createIaProb  = document.getElementById('create_iaProb');

    let currentEcgUrl   = null;
    let currentPaciente = null;

    if (createSelect) {
        createSelect.addEventListener('change', async function () {
            const estudioId = this.value;
            const baseUrl   = this.dataset.infourl;

            // Reset visual
            createBtnEcg.disabled       = true;
            createIaPanel.style.display = 'none';
            createNoIa.style.display    = 'none';
            currentEcgUrl   = null;
            currentPaciente = null;

            if (!estudioId) return;

            try {
                const data = await fetchEstudioInfo(baseUrl, estudioId);
                currentPaciente = data.paciente;

                if (data.tiene_ecg) {
                    currentEcgUrl         = data.ecg_url;
                    createBtnEcg.disabled = false;
                }

                if (data.tiene_ia) {
                    createIaRitmo.value         = data.ia_ritmo_nombre ?? '—';
                    createIaLabel.value         = data.ia_ritmo_label  ?? '—';
                    createIaProb.value          = data.ia_probabilidad ?? '—';
                    createIaPanel.style.display = 'block';
                } else {
                    createNoIa.style.display = 'block';
                }
            } catch (e) {
                console.error(e);
            }
        });

        createBtnEcg.addEventListener('click', function () {
            if (currentEcgUrl) {
                abrirEcgViewer(currentEcgUrl, currentPaciente,
                               document.getElementById('createDiagnosticoModal'));
            }
        });
    }

    // ── Modales EDITAR: botón Ver ECG ─────────────────────────────────────────
    document.querySelectorAll('.btn-ver-ecg-edit').forEach(btn => {
        btn.addEventListener('click', async function () {
            const estudioId = this.dataset.estudio;
            const paciente  = this.dataset.paciente;
            const infoUrl   = this.dataset.infourl;
            const baseUrl   = infoUrl.replace(`/${estudioId}/info`, '');

            try {
                const data = await fetchEstudioInfo(baseUrl, estudioId);
                if (data.tiene_ecg && data.ecg_url) {
                    abrirEcgViewer(data.ecg_url, paciente, this.closest('.modal'));
                } else {
                    alert('Este estudio no tiene una imagen ECG registrada.');
                }
            } catch (e) {
                console.error(e);
                alert('Error al cargar el ECG.');
            }
        });
    });

    // ── Reset del modal Crear al cerrarlo ────────────────────────────────────
    const createModal = document.getElementById('createDiagnosticoModal');
    if (createModal) {
        createModal.addEventListener('hidden.bs.modal', function () {
            // No resetear si el visor ECG está abierto (vendrá de vuelta)
            if (document.getElementById('ecgViewerModal').classList.contains('show')) return;

            document.getElementById('formCreateDiagnostico').reset();
            createBtnEcg.disabled       = true;
            createIaPanel.style.display = 'none';
            createNoIa.style.display    = 'none';
            currentEcgUrl   = null;
            currentPaciente = null;
        });
    }

})();
</script>

@endsection

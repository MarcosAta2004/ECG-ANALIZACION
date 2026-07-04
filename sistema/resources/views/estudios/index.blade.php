@extends('dashboard.index')

@section('content')

{{-- SVGs para Iconos de Alertas --}}
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
    </symbol>
    <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
    </symbol>
    <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
    </symbol>
</svg>

{{-- Alertas de Éxito, Advertencia o Información --}}
@if(session('message'))
    @php
        $status = session('status', 'info');
        $alertClass = 'alert-info';
        $icon = 'info-fill';

        if($status === 'success' || session('alert') === 'success') {
            $alertClass = 'alert-success';
            $icon = 'check-circle-fill';
        } elseif($status === 'warning' || session('alert') === 'warning') {
            $alertClass = 'alert-warning';
            $icon = 'exclamation-triangle-fill';
        } elseif($status === 'error' || session('alert') === 'danger' || $status === 'danger') {
            $alertClass = 'alert-danger';
            $icon = 'exclamation-triangle-fill';
        } elseif(session('alert') === 'primary') {
            $alertClass = 'alert-primary';
            $icon = 'info-fill';
        }
    @endphp

    <div class="bd-example mb-3">
        <div class="alert {{ $alertClass }} alert-dismissible fade show d-flex align-items-center" role="alert">
            <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                <use xlink:href="#{{ $icon }}" />
            </svg>
            <div>
                {{ session('message') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- Alertas de Errores de Validación --}}
@if ($errors->any())
    <div class="bd-example mb-3">
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
            <div>
                <strong>¡Hubo un problema!</strong> Revisa los siguientes errores de validación:<br>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="header-title">
                    <h4 class="card-title">Lista de Estudios</h4>
                </div>
                @can('estudios.store')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEstudioModal">
                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Añadir Estudio
                </button>
                @endcan
            </div>

            {{-- Barra de búsqueda --}}
            <div class="card-body border-bottom pb-3">
                <form method="GET" action="{{ route('estudios.index') }}" class="d-flex gap-2 align-items-center">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 15.4183 19 11Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por código de paciente u observaciones..."
                            autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('estudios.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
                @if(request('search'))
                    <div class="mt-2">
                        <small class="text-muted">
                            Mostrando resultados para: <strong>"{{ request('search') }}"</strong>
                            — {{ $estudios->total() }} resultado(s) encontrado(s).
                        </small>
                    </div>
                @endif
            </div>

            <div class="card-body px-0">

                <div class="table-responsive">
                    <table class="table table-striped mb-0" role="grid">
                        <thead>
                            <tr class="ligth">
                                <th class="text-center">#</th>
                                <th>Paciente</th>
                                <th>Registrado Por</th>
                                <th>Edad (Años)</th>
                                {{-- <th>Observaciones</th> --}}
                                <th>Estado</th>
                                <th class="text-center">Reporte Oficial</th> <th style="min-width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($estudios as $estudio)
                                <tr>
                                    <td class="text-center"><span class="badge bg-dark">#{{ $estudio->estudio_id }}</span></td>
                                    <td><span class="badge bg-primary">{{ $estudio->paciente->codigo_generado ?? 'N/A' }}</span></td>
                                    <td>
                                        @if($estudio->registradoPor)
                                            {{ $estudio->registradoPor->nombres }} {{ $estudio->registradoPor->apellido_paterno }} {{ $estudio->registradoPor->apellido_materno }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $estudio->edad ? $estudio->edad . ' años' : '-' }}</td>
                                    {{-- <td>
                                        @if($estudio->observaciones)
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="verObservacion({{ $estudio->estudio_id }})">
                                                Ver Nota
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td> --}}
                                    <td>
                                        @if($estudio->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($estudio->tieneDiagnosticoFinal())
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-ver-reporte"
                                                data-url="{{ route('reportes.verPDF', $estudio->estudio_id) }}"
                                                data-estudio="#{{ $estudio->estudio_id }}"
                                                data-paciente="{{ $estudio->paciente->codigo_generado ?? 'N/A' }}"
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
                                        @elseif($estudio->imagen && $estudio->imagen->prediccion)
                                            <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">
                                                ⏳ Esperando Diagnóstico
                                            </span>
                                        @else
                                            <span class="badge bg-info text-white" style="font-size: 0.75rem;">
                                                ⏳ Esperando Predicción
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="flex align-items-center list-user-action">
                                            @can('estudios.update')
                                            <button class="btn btn-sm btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#editEstudioModal{{ $estudio->estudio_id }}" title="Editar">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </button>
                                            @endcan

                                            @can('estudios.activar')
                                            @if($estudio->estado == 1)
                                                <button type="button" class="btn btn-sm btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#deleteEstudioModal{{ $estudio->estudio_id }}" title="Desactivar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-icon btn-success" data-bs-toggle="modal" data-bs-target="#activateEstudioModal{{ $estudio->estudio_id }}" title="Activar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3345 2.75024H7.66549C4.64449 2.75024 2.75049 4.88924 2.75049 7.91624V16.0842C2.75049 19.1112 4.63549 21.2502 7.66549 21.2502H16.3335C19.3645 21.2502 21.2505 19.1112 21.2505 16.0842V7.91624C21.2505 4.88924 19.3645 2.75024 16.3345 2.75024Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.43994 12.0002L10.8139 14.3732L15.5599 9.6272" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editEstudioModal{{ $estudio->estudio_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('estudios.update', $estudio->estudio_id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Estudio</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-12 form-group">
                                                            <label class="form-label">Paciente</label>
                                                            <input type="text" class="form-control" value="{{ $estudio->paciente->codigo_generado ?? 'N/A' }}" disabled>
                                                        </div>
                                                        <div class="col-md-12 form-group">
                                                            <label class="form-label">Registrado Por</label>
                                                            <input type="text" class="form-control" value="{{ $estudio->registradoPor ? $estudio->registradoPor->nombres.' '.$estudio->registradoPor->apellido_paterno.' '.$estudio->registradoPor->apellido_materno : 'N/A' }}" disabled>
                                                        </div>
                                                        <div class="col-md-12 form-group">
                                                            <label class="form-label">Edad del Paciente (al momento del estudio)</label>
                                                            <input type="number" class="form-control" name="edad" value="{{ $estudio->edad }}" min="0" max="120" placeholder="Ej. 45">
                                                        </div>
                                                        <div class="col-md-12 form-group">
                                                            <label class="form-label">Observaciones</label>
                                                            <textarea class="form-control" name="observaciones" rows="3">{{ $estudio->observaciones }}</textarea>
                                                        </div>

                                                        <div class="col-md-12 form-group">
                                                            <div class="form-check form-switch mt-2">
                                                                <input class="form-check-input" type="checkbox" id="estadoEdit{{ $estudio->estudio_id }}" name="estado" value="1" {{ $estudio->estado == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="estadoEdit{{ $estudio->estudio_id }}">Estudio Activo</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteEstudioModal{{ $estudio->estudio_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('estudios.destroy', $estudio->estudio_id) }}" method="POST">
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
                                                            ¿Estás seguro que deseas <strong>desactivar</strong> este estudio del paciente <strong>{{ $estudio->paciente->codigo_generado ?? 'N/A' }}</strong>?
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

                                <!-- Activate Modal -->
                                <div class="modal fade" id="activateEstudioModal{{ $estudio->estudio_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('estudios.activar', $estudio->estudio_id) }}" method="POST">
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
                                                            ¿Estás seguro que deseas <strong>activar</strong> este estudio del paciente <strong>{{ $estudio->paciente->codigo_generado ?? 'N/A' }}</strong>?
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
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay estudios registrados</td>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar visor</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createEstudioModal" tabindex="-1" aria-labelledby="createEstudioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('estudios.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createEstudioModalLabel">Añadir Nuevo Estudio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Paciente <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="pacienteInputDisplay" placeholder="Seleccione un paciente..." readonly required>
                                <input type="hidden" name="paciente_id" id="pacienteIdInput" required>
                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#buscarPacienteModal" onclick="cargarPacientes(1)">Buscar persona</button>
                            </div>
                        </div>

                        <div class="col-md-12 form-group">
                            <label class="form-label">Edad del Paciente (al momento del estudio)</label>
                            <input type="number" class="form-control" name="edad" id="edadInput" value="{{ old('edad') }}" min="0" max="120" placeholder="Ej. 45">
                        </div>

                        <div class="col-md-12 form-group">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="estadoCreate" name="estado" value="1" checked>
                                <label class="form-check-label" for="estadoCreate">Estudio Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Registrar Estudio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Buscar Paciente -->
<div class="modal fade" id="buscarPacienteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <div class="input-group flex-grow-1">
                        <input type="text" id="buscarPacienteInput" class="form-control" placeholder="Buscar por código..." autocomplete="off">
                        <button class="btn btn-primary" type="button" onclick="cargarPacientes(1)">Buscar</button>
                    </div>
                    @can('pacientes.store')
                    <button class="btn btn-success text-nowrap" type="button" data-bs-toggle="modal" data-bs-target="#createPacienteModal">
                        Registrar
                    </button>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPacientesBody">
                            <!-- Llenado por AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="infoResultadosPacientes" class="text-muted small"></div>
                    <div id="paginacionPacientes" class="d-flex gap-2">
                        <!-- Paginación AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Paciente Modal -->
<div class="modal fade" id="createPacienteModal" tabindex="-1" aria-labelledby="createPacienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pacientes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createPacienteModalLabel">Añadir Nuevo Paciente Anónimo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#info-fill"/></svg>
                        <div>
                            El código del paciente se generará automáticamente usando el prefijo seleccionado.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Prefijo <span class="text-danger">*</span></label>
                            <select class="form-control" name="prefijo_id" required>
                                <option value="">Seleccione un prefijo...</option>
                                @foreach($prefijos as $prefijo)
                                    <option value="{{ $prefijo->prefijo_id }}" {{ old('prefijo_id') == $prefijo->prefijo_id ? 'selected' : '' }}>{{ $prefijo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Sexo</label>
                            <select class="form-control" name="sexo">
                                <option value="">Seleccione...</option>
                                <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" class="form-control" name="peso" value="{{ old('peso') }}" step="0.01" min="0" max="300" placeholder="Ej. 70.5">
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="estadoCreatePaciente" name="estado" value="1" checked>
                                <label class="form-check-label" for="estadoCreatePaciente">Paciente Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Registrar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Observaciones -->
<div class="modal fade" id="verObservacionModal" tabindex="-1" aria-labelledby="verObservacionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verObservacionModalLabel">Observaciones del Estudio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="observacionContent" class="p-3 bg-light rounded text-dark" style="min-height: 80px;">
                    <!-- Contenido cargado por AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function cargarPacientes(page = 1) {
        let search = document.getElementById('buscarPacienteInput').value;
        let url = `{{ route('pacientes.searchJson') }}?page=${page}&search=${encodeURIComponent(search)}`;

        let tbody = document.getElementById('tablaPacientesBody');
        let infoDiv = document.getElementById('infoResultadosPacientes');
        tbody.innerHTML = '<tr><td colspan="2" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        fetch(url)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if(data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" class="text-center">No se encontraron pacientes</td></tr>';
                    document.getElementById('paginacionPacientes').innerHTML = '';
                    infoDiv.innerHTML = '';
                    return;
                }

                data.data.forEach(paciente => {
                    let tr = document.createElement('tr');
                    let fechaNac = paciente.fecha_nacimiento ? paciente.fecha_nacimiento : '';
                    tr.innerHTML = `
                        <td>${paciente.codigo_generado}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-success"
                                onclick="seleccionarPaciente(${paciente.paciente_id}, '${paciente.codigo_generado}', '${fechaNac}')">
                                Seleccionar
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                infoDiv.innerHTML = `${data.from} a ${data.to} de ${data.total} resultados`;

                // Generar paginación simple
                let pagination = '';
                if(data.prev_page_url) {
                    pagination += `<button type="button" class="btn btn-sm btn-outline-secondary" onclick="cargarPacientes(${data.current_page - 1})">Anterior</button>`;
                }
                if(data.next_page_url) {
                    pagination += `<button type="button" class="btn btn-sm btn-outline-secondary" onclick="cargarPacientes(${data.current_page + 1})">Siguiente</button>`;
                }
                document.getElementById('paginacionPacientes').innerHTML = pagination;
            });
    }

    function seleccionarPaciente(id, codigo, fechaNacimiento) {
        document.getElementById('pacienteIdInput').value = id;
        document.getElementById('pacienteInputDisplay').value = codigo;

        // Auto-calcular edad
        var edadInput = document.getElementById('edadInput');
        if (fechaNacimiento && fechaNacimiento !== 'null') {
            var dob = new Date(fechaNacimiento);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            edadInput.value = age;
        } else {
            edadInput.value = '';
        }

        // Cerrar modal de búsqueda
        bootstrap.Modal.getInstance(document.getElementById('buscarPacienteModal')).hide();

        // Abrir/asegurar que el modal de creación de estudio esté visible
        bootstrap.Modal.getOrCreateInstance(document.getElementById('createEstudioModal')).show();
    }

    function verObservacion(estudioId) {
        var modal = new bootstrap.Modal(document.getElementById('verObservacionModal'));
        var contentDiv = document.getElementById('observacionContent');

        contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0">Cargando observaciones...</p></div>';
        modal.show();

        fetch(`/clinico/estudios/${estudioId}/observacion`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                contentDiv.innerHTML = data.observaciones;
            })
            .catch(error => {
                contentDiv.innerHTML = '<span class="text-danger">Error al cargar las observaciones.</span>';
                console.error('Error:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var reporteModalElement = document.getElementById('reporteViewerModal');
        var reporteIframe = document.getElementById('reporteIframe');
        var reportePaciente = document.getElementById('reporteViewerPaciente');
        var reporteEstudio = document.getElementById('reporteViewerEstudio');

        if (reporteModalElement && reporteIframe) {
            document.querySelectorAll('.btn-ver-reporte').forEach(function (button) {
                button.addEventListener('click', function () {
                    reporteIframe.src = this.dataset.url;
                    reportePaciente.textContent = this.dataset.paciente || 'N/A';
                    reporteEstudio.textContent = this.dataset.estudio || '';

                    bootstrap.Modal.getOrCreateInstance(reporteModalElement).show();
                });
            });

            reporteModalElement.addEventListener('hidden.bs.modal', function () {
                reporteIframe.src = '';
                reportePaciente.textContent = '';
                reporteEstudio.textContent = '';
            });
        }

        @if(session('open_buscar_paciente_modal'))
            var bsBuscarModal = new bootstrap.Modal(document.getElementById('buscarPacienteModal'));
            bsBuscarModal.show();
            document.getElementById('buscarPacienteModal').addEventListener('shown.bs.modal', function () {
                cargarPacientes(1);
            }, {once:true});
        @endif
    });
</script>

@endsection

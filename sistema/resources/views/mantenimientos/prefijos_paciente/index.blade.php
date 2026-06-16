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
                    <h4 class="card-title">Lista de Prefijos de Paciente</h4>
                </div>
                @can('prefijos-paciente.store')
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPrefijoModal">
                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Añadir Prefijo
                </button>
@endcan
            </div>

            {{-- Barra de búsqueda --}}
            <div class="card-body border-bottom pb-3">
                <form method="GET" action="{{ route('prefijos-paciente.index') }}" class="d-flex gap-2 align-items-center">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 15.4183 19 11Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por nombre del prefijo..."
                            autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('prefijos-paciente.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
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
                            — {{ $prefijos->total() }} resultado(s) encontrado(s).
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
                                <th>Nombre del Prefijo</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prefijos as $prefijo)
                                <tr>
                                    <td class="text-center"><span class="badge bg-dark">#{{ $prefijo->prefijo_id }}</span></td>
                                    <td>{{ $prefijo->nombre }}</td>
                                    <td>{{ $prefijo->descripcion ?: 'Sin descripción' }}</td>
                                    <td>
                                        @if($prefijo->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex align-items-center list-user-action">
                                            @can('prefijos-paciente.update')
<button class="btn btn-sm btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#editPrefijoModal{{ $prefijo->prefijo_id }}" title="Editar">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </button>
@endcan
                                            
                                            @if($prefijo->estado == 1)
                                                <button type="button" class="btn btn-sm btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#deletePrefijoModal{{ $prefijo->prefijo_id }}" title="Desactivar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-icon btn-success" data-bs-toggle="modal" data-bs-target="#activatePrefijoModal{{ $prefijo->prefijo_id }}" title="Activar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3345 2.75024H7.66549C4.64449 2.75024 2.75049 4.88924 2.75049 7.91624V16.0842C2.75049 19.1112 4.63549 21.2502 7.66549 21.2502H16.3335C19.3645 21.2502 21.2505 19.1112 21.2505 16.0842V7.91624C21.2505 4.88924 19.3645 2.75024 16.3345 2.75024Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.43994 12.0002L10.8139 14.3732L15.5599 9.6272" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal for Prefijo -->
                                <div class="modal fade" id="editPrefijoModal{{ $prefijo->prefijo_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('prefijos-paciente.update', $prefijo->prefijo_id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Prefijo: {{ $prefijo->nombre }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-12 form-group">
                                                            <label class="form-label">Nombre del Prefijo <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nombre" value="{{ $prefijo->nombre }}" required>
                                                        </div>
                                                        <div class="col-md-12 form-group">
                                                            <label class="form-label">Descripción</label>
                                                            <textarea class="form-control" name="descripcion" rows="3">{{ $prefijo->descripcion }}</textarea>
                                                        </div>
                                                        <div class="col-md-12 form-group">
                                                            <div class="form-check form-switch mt-2">
                                                                <input class="form-check-input" type="checkbox" id="estadoEdit{{ $prefijo->prefijo_id }}" name="estado" value="1" {{ $prefijo->estado == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="estadoEdit{{ $prefijo->prefijo_id }}">Activo</label>
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

                                <!-- Delete/Deactivate Modal -->
                                <div class="modal fade" id="deletePrefijoModal{{ $prefijo->prefijo_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('prefijos-paciente.destroy', $prefijo->prefijo_id) }}" method="POST">
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
                                                            ¿Estás seguro que deseas <strong>desactivar</strong> el prefijo <strong>{{ $prefijo->nombre }}</strong>?
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
                                <div class="modal fade" id="activatePrefijoModal{{ $prefijo->prefijo_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('prefijos-paciente.activar', $prefijo->prefijo_id) }}" method="POST">
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
                                                            ¿Estás seguro que deseas <strong>activar</strong> el prefijo <strong>{{ $prefijo->nombre }}</strong>?
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
                                    <td colspan="5" class="text-center text-muted py-4">No hay prefijos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $prefijos->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createPrefijoModal" tabindex="-1" aria-labelledby="createPrefijoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('prefijos-paciente.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createPrefijoModalLabel">Añadir Nuevo Prefijo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Nombre del Prefijo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" value="1" checked>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Crear Prefijo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

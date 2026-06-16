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

        if($status === 'success') {
            $alertClass = 'alert-success';
            $icon = 'check-circle-fill';
        } elseif($status === 'warning') {
            $alertClass = 'alert-warning';
            $icon = 'exclamation-triangle-fill';
        } elseif($status === 'error' || $status === 'danger') {
            $alertClass = 'alert-danger';
            $icon = 'exclamation-triangle-fill';
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
                    <h4 class="card-title">Lista de Usuarios</h4>
                </div>
                @can('usuarios.store')
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUsuarioModal">
                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Añadir Usuario
                </button>
@endcan
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" role="grid">
                        <thead>
                            <tr class="ligth">
                                <th class="text-center">#</th>
                                <th>Nombres y Apellidos</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td class="text-center"><span class="badge bg-dark">#{{ $usuario->id }}</span></td>
                                    <td>{{ $usuario->nombres }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</td>
                                    <td><span class="badge bg-secondary">{{ $usuario->rolesa->name ?? 'Sin Rol' }}</span></td>
                                    <td>
                                        @if($usuario->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex align-items-center list-user-action">
                                            @can('usuarios.update')
<button class="btn btn-sm btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#editUsuarioModal{{ $usuario->id }}" title="Editar">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </button>
@endcan
                                            
                                            @if($usuario->estado == 1)
                                                <button type="button" class="btn btn-sm btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUsuarioModal{{ $usuario->id }}" title="Desactivar">
                                                    <span class="btn-inner">
                                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                            <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-icon btn-success" data-bs-toggle="modal" data-bs-target="#activateUsuarioModal{{ $usuario->id }}" title="Activar">
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

                                <!-- Edit Modal for User -->
                                <div class="modal fade" id="editUsuarioModal{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Usuario: {{ $usuario->login }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nombres" value="{{ $usuario->nombres }}" required>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Apellido Paterno</label>
                                                            <input type="text" class="form-control" name="apellido_paterno" value="{{ $usuario->apellido_paterno }}">
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Apellido Materno</label>
                                                            <input type="text" class="form-control" name="apellido_materno" value="{{ $usuario->apellido_materno }}">
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Usuario (Login) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="login" value="{{ $usuario->login }}" required>
                                                        </div>
                                                        
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Tipo Documento</label>
                                                            <select class="form-control" name="tipo_documento_identidad_id">
                                                                <option value="">Seleccione...</option>
                                                                @foreach($tiposDoc as $doc)
                                                                    <option value="{{ $doc->id }}" {{ $usuario->tipo_documento_identidad_id == $doc->id ? 'selected' : '' }}>{{ $doc->descripcion }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Nro Documento</label>
                                                            <input type="text" class="form-control" name="numero_documento" value="{{ $usuario->numero_documento }}">
                                                        </div>
                                                        
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                                                            <select class="form-control" name="rol_id" required>
                                                                <option value="">Seleccione Rol</option>
                                                                @foreach($rolesActivos as $rol)
                                                                    <option value="{{ $rol->id }}" {{ $usuario->rol_id == $rol->id ? 'selected' : '' }}>{{ $rol->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="form-label">Contraseña</label>
                                                            <input type="password" class="form-control" name="password" minlength="6" placeholder="Dejar en blanco si no desea cambiarla">
                                                        </div>
                                                        
                                                        <div class="col-md-12 mt-3">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" id="estadoEdit{{ $usuario->id }}" name="estado" value="1" {{ $usuario->estado == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="estadoEdit{{ $usuario->id }}">Usuario Activo</label>
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
                                <div class="modal fade" id="deleteUsuarioModal{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST">
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
                                                            ¿Estás seguro que deseas <strong>desactivar</strong> al usuario <strong>{{ $usuario->nombres }}</strong>?
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
                                <div class="modal fade" id="activateUsuarioModal{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('usuarios.activar', $usuario->id) }}" method="POST">
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
                                                            ¿Estás seguro que deseas <strong>activar</strong> al usuario <strong>{{ $usuario->nombres }}</strong>?
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
                                    <td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $usuarios->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createUsuarioModal" tabindex="-1" aria-labelledby="createUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createUsuarioModalLabel">Añadir Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombres" value="{{ old('nombres') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Usuario (Login) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="login" value="{{ old('login') }}" required>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="form-label">Tipo Documento</label>
                            <select class="form-control" name="tipo_documento_identidad_id">
                                <option value="">Seleccione...</option>
                                @foreach($tiposDoc as $doc)
                                    <option value="{{ $doc->id }}" {{ old('tipo_documento_identidad_id') == $doc->id ? 'selected' : '' }}>{{ $doc->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Nro Documento</label>
                            <input type="text" class="form-control" name="numero_documento" value="{{ old('numero_documento') }}">
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-control" name="rol_id" required>
                                <option value="">Seleccione Rol</option>
                                @foreach($rolesActivos as $rol)
                                    <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" minlength="6" required>
                        </div>
                        
                        <div class="col-md-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="estadoCreate" name="estado" value="1" checked>
                                <label class="form-check-label" for="estadoCreate">Usuario Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const tiposDoc = @json($tiposDoc->keyBy('id'));

        function updateDocumentInput(selectElement, isInit = false) {
            const form = selectElement.closest('form');
            if (!form) return;
            const input = form.querySelector('input[name="numero_documento"]');
            if (!input) return;

            const val = selectElement.value;

            if (!val || val === "") {
                // No hay tipo de documento seleccionado
                input.disabled = true;
                if (!isInit) {
                    input.value = '';
                }
                input.removeAttribute('maxlength');
                input.removeAttribute('minlength');
                input.placeholder = "";
            } else {
                // Tipo de documento seleccionado
                input.disabled = false;
                input.placeholder = "";
                if (tiposDoc[val]) {
                    if (tiposDoc[val].maximo) {
                        input.setAttribute('maxlength', tiposDoc[val].maximo);
                    } else {
                        input.removeAttribute('maxlength');
                    }
                    if (tiposDoc[val].minimo) {
                        input.setAttribute('minlength', tiposDoc[val].minimo);
                    } else {
                        input.removeAttribute('minlength');
                    }
                }
            }
        }

        // Aplicar a todos los selects de tipo de documento (Crear y Editar)
        const selects = document.querySelectorAll('select[name="tipo_documento_identidad_id"]');
        selects.forEach(select => {
            // Inicializar al cargar
            updateDocumentInput(select, true);

            // Escuchar cambios
            select.addEventListener('change', function() {
                updateDocumentInput(this, false);
            });
        });

        // Opcional: restringir caracteres extraños (permite letras y números)
        const inputs = document.querySelectorAll('input[name="numero_documento"]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                // Removemos espacios o caracteres especiales no deseados
                this.value = this.value.replace(/[^0-9a-zA-Z-]/g, '');
            });
        });
    })();
</script>

@endsection

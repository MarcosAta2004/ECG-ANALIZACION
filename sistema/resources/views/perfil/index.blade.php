@extends('dashboard.index')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Actualizar Contraseña</h4>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Alertas de Éxito o Error --}}
                    @if(session('message'))
                        @php
                            $status = session('status', 'info');
                            $alertClass = ($status === 'success' || session('alert') === 'success') ? 'alert-success' : 'alert-danger';
                        @endphp
                        <div class="alert {{ $alertClass }} alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>¡Hubo un problema!</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <p class="text-muted mb-4">Asegúrate de usar una contraseña segura con al menos 8 caracteres.</p>

                    <form action="{{ route('perfil.actualizarContrasena') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4 mb-3">
                                <label class="form-label" for="current_password">Contraseña Actual <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual" required>
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label class="form-label" for="password">Nueva Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 8 caracteres" required minlength="8">
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label class="form-label" for="password_confirmation">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repite la nueva contraseña" required minlength="8">
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="reset" class="btn btn-danger me-2">Limpiar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
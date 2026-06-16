@extends('dashboard.index')

@section('content')

    {{-- SVGs para Iconos de Alertas --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </symbol>
        <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
        </symbol>
        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
    </svg>

    {{-- Alertas de Éxito, Advertencia o Información --}}
    @if(session('message'))
        @php
            $status = session('status', 'info');
            $alertClass = 'alert-info';
            $icon = 'info-fill';

            if ($status === 'success' || session('alert') === 'success') {
                $alertClass = 'alert-success';
                $icon = 'check-circle-fill';
            } elseif ($status === 'warning' || session('alert') === 'warning') {
                $alertClass = 'alert-warning';
                $icon = 'exclamation-triangle-fill';
            } elseif ($status === 'error' || session('alert') === 'danger' || $status === 'danger') {
                $alertClass = 'alert-danger';
                $icon = 'exclamation-triangle-fill';
            } elseif (session('alert') === 'primary') {
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

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="header-title">
                        <h4 class="card-title">Lista de Imágenes</h4>
                    </div>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" role="grid">
                            <thead>
                                <tr class="ligth">
                                    <th class="text-center">#</th>
                                    <th>Estudio</th>
                                    <th>Archivo</th>
                                    <th>Formato</th>
                                    <th>Resolución</th>
                                    <th>Tamaño</th>
                                    <th>Estado</th>
                                    <th style="min-width: 100px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($imagenes as $imagen)
                                    <tr>
                                        <td class="text-center"><span class="badge bg-dark">#{{ $imagen->imagen_id }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-primary">{{ $imagen->estudio->paciente->codigo_generado ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-info btn-ver-archivo"
                                                data-url="{{ route('imagenes.ecg.ver', $imagen->imagen_id) }}"
                                                data-archivo="{{ basename($imagen->ruta) }}"
                                                data-paciente="{{ $imagen->estudio->paciente->codigo_generado ?? 'N/A' }}"
                                                title="Ver archivo">
                                                <svg width="18" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" class="me-1">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor"
                                                        stroke-width="1.6" />
                                                </svg>
                                                Ver archivo
                                            </button>
                                            <div class="small text-muted mt-1">{{ basename($imagen->ruta) }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary text-uppercase">{{ $imagen->formato }}</span></td>
                                        <td>{{ $imagen->resolucion ?? '-' }}</td>
                                        <td>{{ $imagen->tamano_kb }} KB</td>
                                        <td>
                                            @if($imagen->estado == 1)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex align-items-center list-user-action">
                                                @if($imagen->estado == 1)
                                                    <button type="button" class="btn btn-sm btn-icon btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteImagenModal{{ $imagen->imagen_id }}"
                                                        title="Desactivar">
                                                        <span class="btn-inner">
                                                            <svg width="20" viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                                                <path
                                                                    d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826"
                                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round"></path>
                                                                <path d="M20.708 6.23975H3.75" stroke="currentColor"
                                                                    stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round"></path>
                                                                <path
                                                                    d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973"
                                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round"></path>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-icon btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activateImagenModal{{ $imagen->imagen_id }}"
                                                        title="Activar">
                                                        <span class="btn-inner">
                                                            <svg width="20" viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                                    d="M16.3345 2.75024H7.66549C4.64449 2.75024 2.75049 4.88924 2.75049 7.91624V16.0842C2.75049 19.1112 4.63549 21.2502 7.66549 21.2502H16.3335C19.3645 21.2502 21.2505 19.1112 21.2505 16.0842V7.91624C21.2505 4.88924 19.3645 2.75024 16.3345 2.75024Z"
                                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round"></path>
                                                                <path d="M8.43994 12.0002L10.8139 14.3732L15.5599 9.6272"
                                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round"></path>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteImagenModal{{ $imagen->imagen_id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('imagenes.destroy', $imagen->imagen_id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Desactivación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning d-flex align-items-center mb-0"
                                                            role="alert">
                                                            <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                                                <use xlink:href="#exclamation-triangle-fill" />
                                                            </svg>
                                                            <div>
                                                                ¿Estás seguro que deseas <strong>desactivar</strong> esta imagen
                                                                del paciente
                                                                <strong>{{ $imagen->estudio->paciente->codigo_generado ?? 'N/A' }}</strong>?
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger">Sí, Desactivar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Activate Modal -->
                                    <div class="modal fade" id="activateImagenModal{{ $imagen->imagen_id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('imagenes.activar', $imagen->imagen_id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Activación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info d-flex align-items-center mb-0"
                                                            role="alert">
                                                            <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                                                <use xlink:href="#info-fill" />
                                                            </svg>
                                                            <div>
                                                                ¿Estás seguro que deseas <strong>activar</strong> esta imagen
                                                                del paciente
                                                                <strong>{{ $imagen->estudio->paciente->codigo_generado ?? 'N/A' }}</strong>?
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success">Sí, Activar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No hay imágenes registradas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        {{ $imagenes->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="archivoViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Archivo ECG
                        <span id="archivoViewerPaciente" class="badge bg-primary ms-2"></span>
                        <span id="archivoViewerNombre" class="text-muted small ms-2"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="background:#1a1a2e; height: 800px;">
                    <iframe
                        id="archivoIframe"
                        src=""
                        width="100%"
                        height="100%"
                        style="border:none;">
                    </iframe>
                </div>
                <div class="modal-footer">
                    <a id="btnDescargarEcg" href="#" class="btn btn-primary d-none">Descargar Archivo</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar visor</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            'use strict';

            const modalElement = document.getElementById('archivoViewerModal');
            const iframe = document.getElementById('archivoIframe');
            const paciente = document.getElementById('archivoViewerPaciente');
            const nombre = document.getElementById('archivoViewerNombre');
            const btnDescargar = document.getElementById('btnDescargarEcg');

            if (!modalElement || !iframe) return;

            document.querySelectorAll('.btn-ver-archivo').forEach((button) => {
                button.addEventListener('click', function () {
                    const url = this.dataset.url;

                    iframe.src = url;
                    paciente.textContent = this.dataset.paciente || 'N/A';
                    nombre.textContent = this.dataset.archivo || '';

                    if (btnDescargar) {
                        btnDescargar.href = url.replace('/ver-ecg', '/descargar-ecg');
                        btnDescargar.classList.remove('d-none');
                    }

                    bootstrap.Modal.getOrCreateInstance(modalElement).show();
                });
            });

            modalElement.addEventListener('hidden.bs.modal', function () {
                iframe.src = '';
                paciente.textContent = '';
                nombre.textContent = '';
                if (btnDescargar) btnDescargar.classList.add('d-none');
            });

            // Auto Open feature
            @if(session('autoOpenImageId'))
                const autoButton = document.querySelector(`.btn-ver-archivo[data-url*="/${{ session('autoOpenImageId') }}/ver-ecg"]`);
                if(autoButton){
                    autoButton.click();
                }
            @endif
        })();
    </script>

@endsection

@extends('dashboard.index')

@section('content')

{{-- SVGs ocultos para iconos --}}
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
        if (session('alert') === 'success')       { $alertClass = 'alert-success'; $icon = 'check-circle-fill'; }
        elseif (session('alert') === 'warning')   { $alertClass = 'alert-warning'; $icon = 'exclamation-triangle-fill'; }
        elseif (session('alert') === 'danger')    { $alertClass = 'alert-danger';  $icon = 'exclamation-triangle-fill'; }
        elseif (session('alert') === 'primary')   { $alertClass = 'alert-primary'; $icon = 'info-fill'; }
    @endphp
    <div class="bd-example mb-3">
        <div class="alert {{ $alertClass }} alert-dismissible fade show d-flex align-items-center" role="alert">
            <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#{{ $icon }}"/></svg>
            <div>{{ session('message') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="header-title">
                    <h4 class="card-title">Lista de Predicciones</h4>
                    <p class="mb-0 text-muted small">Resultados generados automáticamente por el modelo CNN-LSTM</p>
                </div>
            </div>

            {{-- Barra de búsqueda --}}
            <div class="card-body border-bottom pb-3">
                <form method="GET" action="{{ route('predicciones.index') }}" class="d-flex gap-2 align-items-center">
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
                            <a href="{{ route('predicciones.index') }}" class="btn btn-outline-secondary" title="Limpiar búsqueda">
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
                            — {{ $predicciones->total() }} resultado(s) encontrado(s).
                        </small>
                    </div>
                @endif
            </div>

            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle" role="grid">
                        <thead>
                            <tr class="ligth">
                                <th class="text-center">#</th>
                                <th>Paciente</th>
                                <th>Ritmo Detectado</th>
                                <th>Label</th>
                                <th>Grupo</th>
                                <th>Nivel</th>
                                <th>Clasificación</th>
                                <th>Probabilidad</th>
                                <th>Top Predicciones</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-center" style="min-width:90px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($predicciones as $prediccion)
                                @php
                                    $ritmo         = $prediccion->ritmoCardiaco;
                                    $grupo         = $ritmo?->grupoCardiaco;
                                    $nivel         = $ritmo?->nivelGravedad;
                                    $clasificacion = $ritmo?->clasificacionArritmia;

                                    // Decodificar top_predicciones (puede llegar como string o array)
                                    $topPreds = $prediccion->top_predicciones ?? [];
                                    if (is_string($topPreds)) {
                                        $topPreds = json_decode($topPreds, true) ?? [];
                                    }

                                    $pct = round($prediccion->probabilidad * 100, 2);

                                    $nivelBadge = match($nivel?->nombre) {
                                        'Alta'     => 'bg-danger',
                                        'Moderada' => 'bg-warning text-dark',
                                        'Baja'     => 'bg-success',
                                        default    => 'bg-secondary',
                                    };
                                    $clasifBadge = match($clasificacion?->nombre) {
                                        'ARRITMIA' => 'bg-danger',
                                        'NORMAL'   => 'bg-success',
                                        default    => 'bg-secondary',
                                    };
                                    $probBar = $pct >= 90 ? 'bg-success' : ($pct >= 75 ? 'bg-warning' : 'bg-danger');
                                @endphp
                                <tr>
                                    {{-- # --}}
                                    <td class="text-center">
                                        <span class="badge bg-dark">#{{ $prediccion->prediccion_id }}</span>
                                    </td>

                                    {{-- Paciente --}}
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $prediccion->imagen?->estudio?->paciente?->codigo_generado ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- Ritmo Detectado --}}
                                    <td class="fw-semibold text-nowrap">
                                        {{ $ritmo?->nombre ?? 'N/A' }}
                                    </td>

                                    {{-- Label (código SCP-ECG) --}}
                                    <td>
                                        <code class="badge bg-dark">{{ $ritmo?->label ?? '—' }}</code>
                                    </td>

                                    {{-- Grupo cardíaco --}}
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $grupo?->nombre ?? '—' }}</span>
                                    </td>

                                    {{-- Nivel de gravedad --}}
                                    <td>
                                        <span class="badge {{ $nivelBadge }}">{{ $nivel?->nombre ?? '—' }}</span>
                                    </td>

                                    {{-- Clasificación --}}
                                    <td>
                                        <span class="badge {{ $clasifBadge }}">{{ $clasificacion?->nombre ?? '—' }}</span>
                                    </td>

                                    {{-- Probabilidad --}}
                                    <td style="min-width:130px">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:7px;">
                                                <div class="progress-bar {{ $probBar }}"
                                                     role="progressbar"
                                                     style="width:{{ $pct }}%"
                                                     aria-valuenow="{{ $pct }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-nowrap fw-semibold">{{ $pct }}%</small>
                                        </div>
                                    </td>

                                    {{-- Top Predicciones: badges inline --}}
                                    <td style="min-width:160px">
                                        @if(!empty($topPreds))
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($topPreds as $i => $tp)
                                                    <div class="d-flex align-items-center gap-1">
                                                        <code class="badge {{ $i === 0 ? 'bg-dark' : 'bg-secondary' }} text-nowrap" style="font-size:.7rem;">
                                                            {{ $tp['code'] ?? '?' }}
                                                        </code>
                                                        <small class="text-muted text-nowrap" style="font-size:.72rem;">
                                                            {{ round($tp['probability'] ?? 0, 1) }}%
                                                        </small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Fecha --}}
                                    <td class="text-nowrap small">
                                        {{ \Carbon\Carbon::parse($prediccion->created_at)->format('d/m/Y H:i') }}
                                    </td>

                                    {{-- Estado --}}
                                    <td>
                                        @if($prediccion->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="text-center">
                                        @if($prediccion->estado == 1)
                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $prediccion->prediccion_id }}"
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
                                            <button type="button"
                                                    class="btn btn-sm btn-icon btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#activateModal{{ $prediccion->prediccion_id }}"
                                                    title="Activar">
                                                <span class="btn-inner">
                                                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3345 2.75024H7.66549C4.64449 2.75024 2.75049 4.88924 2.75049 7.91624V16.0842C2.75049 19.1112 4.63549 21.2502 7.66549 21.2502H16.3335C19.3645 21.2502 21.2505 19.1112 21.2505 16.0842V7.91624C21.2505 4.88924 19.3645 2.75024 16.3345 2.75024Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M8.43994 12.0002L10.8139 14.3732L15.5599 9.6272" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        No hay predicciones registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $predicciones->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     MODALES — fuera de la tabla para no romper el layout
     ================================================================ --}}
@foreach($predicciones as $prediccion)
    @php
        $pacienteCodigo = $prediccion->imagen?->estudio?->paciente?->codigo_generado ?? 'N/A';
        $ritmoNombre    = $prediccion->ritmoCardiaco?->nombre ?? 'N/A';
    @endphp

    {{-- Modal Desactivar --}}
    <div class="modal fade" id="deleteModal{{ $prediccion->prediccion_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('predicciones.destroy', $prediccion->prediccion_id) }}" method="POST">
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
                                ¿Deseas <strong>desactivar</strong> la predicción
                                <strong>{{ $ritmoNombre }}</strong> del paciente
                                <strong>{{ $pacienteCodigo }}</strong>?
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
    <div class="modal fade" id="activateModal{{ $prediccion->prediccion_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('predicciones.activar', $prediccion->prediccion_id) }}" method="POST">
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
                                ¿Deseas <strong>activar</strong> la predicción
                                <strong>{{ $ritmoNombre }}</strong> del paciente
                                <strong>{{ $pacienteCodigo }}</strong>?
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

@endsection

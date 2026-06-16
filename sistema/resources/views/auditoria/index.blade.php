@extends('dashboard.index')

@section('content')

@php
    $formatText = function (?string $value): string {
        if (!$value) {
            return '-';
        }

        return str($value)
            ->replace(['_', '-'], ' ')
            ->lower()
            ->title()
            ->toString();
    };

    $severityClass = [
        'CRITICA' => 'bg-danger',
        'IMPORTANTE' => 'bg-warning text-dark',
        'MENOR' => 'bg-success',
    ];

    $hasFilters = ($filters['search'] ?? '')
        || ($filters['usuario'] ?? '')
        || ($filters['modulo'] ?? '')
        || ($filters['accion'] ?? '')
        || ($filters['desde'] ?? '')
        || ($filters['hasta'] ?? '');
@endphp

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="header-title">
                    <h4 class="card-title mb-1">Bitacora de auditoria</h4>
                    <p class="mb-0 text-muted small">Historial de acciones importantes realizadas en el sistema.</p>
                </div>
            </div>

            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Total</div>
                            <div class="h4 mb-0">{{ $auditoriaStats['total'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Registradas hoy</div>
                            <div class="h4 mb-0">{{ $auditoriaStats['hoy'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Criticas</div>
                            <div class="h4 mb-0">{{ $auditoriaStats['criticas'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Validaciones medicas</div>
                            <div class="h4 mb-0">{{ $auditoriaStats['validaciones'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('auditoria.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" name="search"
                                value="{{ $filters['search'] ?? '' }}"
                                placeholder="Usuario, accion, modulo o registro">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Usuario</label>
                            <select class="form-select" name="usuario">
                                <option value="">Todos</option>
                                @foreach($usuarios as $usuario)
                                    @php
                                        $nombreUsuario = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellido_paterno ?? '') . ' ' . ($usuario->apellido_materno ?? ''));
                                    @endphp
                                    <option value="{{ $usuario->id }}" {{ (string) ($filters['usuario'] ?? '') === (string) $usuario->id ? 'selected' : '' }}>
                                        {{ $nombreUsuario !== '' ? $nombreUsuario : 'Usuario #' . $usuario->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Modulo</label>
                            <select class="form-select" name="modulo">
                                <option value="">Todos</option>
                                @foreach($modulos as $modulo)
                                    <option value="{{ $modulo }}" {{ ($filters['modulo'] ?? '') === $modulo ? 'selected' : '' }}>
                                        {{ $formatText($modulo) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Accion</label>
                            <select class="form-select" name="accion">
                                <option value="">Todas</option>
                                @foreach($acciones as $accion)
                                    <option value="{{ $accion }}" {{ ($filters['accion'] ?? '') === $accion ? 'selected' : '' }}>
                                        {{ $formatText($accion) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <label class="form-label">Desde</label>
                            <input type="date" class="form-control" name="desde" value="{{ $filters['desde'] ?? '' }}">
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <label class="form-label">Hasta</label>
                            <input type="date" class="form-control" name="hasta" value="{{ $filters['hasta'] ?? '' }}">
                        </div>
                        <div class="col-lg-1 col-md-12">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </div>

                    @if($hasFilters)
                        <div class="mt-3">
                            <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle" role="grid">
                        <thead>
                            <tr class="ligth">
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Accion</th>
                                <th>Modulo</th>
                                <th>Registro afectado</th>
                                <th>Resumen</th>
                                <th>Importancia</th>
                                <th class="text-center">Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditorias as $auditoria)
                                @php
                                    $usuario = $auditoria->usuario;
                                    $nombreUsuario = $usuario
                                        ? trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellido_paterno ?? '') . ' ' . ($usuario->apellido_materno ?? ''))
                                        : '';
                                    $modalId = 'auditoriaDetalle' . $auditoria->auditoria_id;
                                    $registro = $auditoria->entidad
                                        ? $formatText($auditoria->entidad) . ($auditoria->entidad_id ? ' #' . $auditoria->entidad_id : '')
                                        : '-';
                                    $severidad = $auditoria->severidad ?: 'SIN NIVEL';
                                @endphp
                                <tr>
                                    <td class="text-nowrap small">{{ $auditoria->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $nombreUsuario !== '' ? $nombreUsuario : 'Sistema' }}</td>
                                    <td><span class="badge bg-primary">{{ $formatText($auditoria->accion) }}</span></td>
                                    <td>{{ $formatText($auditoria->modulo) }}</td>
                                    <td class="text-nowrap">{{ $registro }}</td>
                                    <td style="max-width: 360px;">
                                        <span class="d-inline-block text-truncate" style="max-width: 340px;" title="{{ $auditoria->descripcion }}">
                                            {{ $auditoria->descripcion ?: 'Evento registrado sin descripcion.' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $severityClass[$auditoria->severidad] ?? 'bg-secondary' }}">
                                            {{ $formatText($severidad) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-info"
                                            data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" title="Ver detalle">
                                            <span class="btn-inner">
                                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Auditoria #{{ $auditoria->auditoria_id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Cuando ocurrio</label>
                                                        <div class="fw-semibold">{{ $auditoria->created_at?->format('d/m/Y H:i:s') ?? '-' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Quien lo hizo</label>
                                                        <div class="fw-semibold">{{ $nombreUsuario !== '' ? $nombreUsuario : 'Sistema' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Accion realizada</label>
                                                        <div>{{ $formatText($auditoria->accion) }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Modulo</label>
                                                        <div>{{ $formatText($auditoria->modulo) }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Registro afectado</label>
                                                        <div>{{ $registro }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Importancia</label>
                                                        <div>
                                                            <span class="badge {{ $severityClass[$auditoria->severidad] ?? 'bg-secondary' }}">
                                                                {{ $formatText($severidad) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small text-muted">Resumen</label>
                                                        <div class="border rounded p-2">{{ $auditoria->descripcion ?: 'Evento registrado sin descripcion.' }}</div>
                                                    </div>

                                                    @if($auditoria->nivel_confianza || $auditoria->razon_cambio || $auditoria->valoracion_medico)
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted">Confianza</label>
                                                            <div>{{ $formatText($auditoria->nivel_confianza) }}</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted">Motivo</label>
                                                            <div>{{ $formatText($auditoria->razon_cambio) }}</div>
                                                        </div>
                                                        @if($auditoria->valoracion_medico)
                                                            <div class="col-md-12">
                                                                <label class="form-label small text-muted">Valoracion medica</label>
                                                                <div class="border rounded p-2">{{ $auditoria->valoracion_medico }}</div>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if($auditoria->valores_anteriores || $auditoria->valores_nuevos)
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted">Antes</label>
                                                            <pre class="border rounded bg-light p-2 mb-0 small" style="max-height: 220px; overflow:auto;">{{ $auditoria->valores_anteriores ? json_encode($auditoria->valores_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</pre>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted">Despues</label>
                                                            <pre class="border rounded bg-light p-2 mb-0 small" style="max-height: 220px; overflow:auto;">{{ $auditoria->valores_nuevos ? json_encode($auditoria->valores_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</pre>
                                                        </div>
                                                    @endif

                                                    <div class="col-md-12">
                                                        <label class="form-label small text-muted">Dato tecnico</label>
                                                        <div class="small text-muted">IP: {{ $auditoria->ip_address ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay registros de auditoria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $auditorias->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

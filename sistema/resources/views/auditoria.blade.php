@extends('plantillas.aplicacion')

@section('title', 'Auditoria')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Auditoria</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Consulta eventos operativos y cambios registrados en el sistema.
    </p>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up">
        <div class="card">
            <p class="text-sm text-muted-foreground">Eventos</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.2);">
            <p class="text-sm text-muted-foreground">Hoy</p>
            <p class="text-2xl font-bold text-success mt-1">{{ number_format($stats['hoy']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--primary)/0.05);border-color:hsl(var(--primary)/0.2);">
            <p class="text-sm text-muted-foreground">Usuarios/Roles</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ number_format($stats['usuarios']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.2);">
            <p class="text-sm text-muted-foreground">Clinicos</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ number_format($stats['clinico']) }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('auditoria.index') }}" class="card animate-fade-in-up" style="animation-delay:100ms;">
        <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-6 gap-4 items-end">
            <label class="filter-field xl:col-span-2">
                <span class="filter-label">Buscar</span>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Descripcion, entidad o usuario" class="input-field" />
            </label>
            <label class="filter-field">
                <span class="filter-label">Usuario</span>
                <select name="user" class="input-field">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected((string) $filters['user'] === (string) $usuario->id)>
                            {{ $usuario->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="filter-field">
                <span class="filter-label">Modulo</span>
                <select name="module" class="input-field">
                    <option value="">Todos</option>
                    @foreach($modulos as $modulo)
                        <option value="{{ $modulo }}" @selected($filters['module'] === $modulo)>{{ $modulo }}</option>
                    @endforeach
                </select>
            </label>
            <label class="filter-field">
                <span class="filter-label">Accion</span>
                <select name="action" class="input-field">
                    <option value="">Todas</option>
                    @foreach($acciones as $accion)
                        <option value="{{ $accion }}" @selected($filters['action'] === $accion)>{{ $accion }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1">Aplicar</button>
                <a href="{{ route('auditoria.index') }}" class="filter-clear-btn flex-1">Limpiar</a>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <label class="filter-field">
                <span class="filter-label">Desde</span>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="input-field" />
            </label>
            <label class="filter-field">
                <span class="filter-label">Hasta</span>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="input-field" />
            </label>
        </div>
    </form>

    <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up" style="animation-delay:200ms;">
        <div class="hidden md:block overflow-x-auto">
            <table class="table-ecg">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Modulo</th>
                        <th>Accion</th>
                        <th>Entidad</th>
                        <th>Descripcion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditorias as $evento)
                        <tr>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-mono text-xs">{{ $evento->created_at?->format('Y-m-d') }}</span>
                                    <span class="font-mono text-xs text-muted-foreground">{{ $evento->created_at?->format('H:i:s') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $evento->usuario?->name ?? 'Sistema' }}</span>
                                    <span class="text-xs text-muted-foreground">{{ $evento->usuario?->email ?? 'Sin usuario' }}</span>
                                </div>
                            </td>
                            <td><span class="badge badge-success">{{ $evento->modulo }}</span></td>
                            <td><span class="badge badge-warning">{{ $evento->accion }}</span></td>
                            <td>
                                <div class="flex flex-col">
                                    <span>{{ $evento->entidad ?? '-' }}</span>
                                    <span class="font-mono text-xs text-muted-foreground">{{ $evento->entidad_id ?? '' }}</span>
                                </div>
                            </td>
                            <td class="max-w-md">
                                <p class="text-sm">{{ $evento->descripcion ?? 'Sin descripcion' }}</p>
                                @if($evento->valores_anteriores || $evento->valores_nuevos)
                                    <details class="mt-2 text-xs text-muted-foreground">
                                        <summary class="cursor-pointer text-primary font-medium">Ver cambios</summary>
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mt-2">
                                            <pre class="overflow-auto rounded-lg p-3" style="background:hsl(var(--muted)/0.5);">{{ json_encode($evento->valores_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            <pre class="overflow-auto rounded-lg p-3" style="background:hsl(var(--muted)/0.5);">{{ json_encode($evento->valores_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y border-border">
            @foreach($auditorias as $evento)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $evento->modulo }} - {{ $evento->accion }}</p>
                            <p class="text-xs text-muted-foreground">{{ $evento->created_at?->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <span class="badge badge-success">{{ $evento->usuario?->name ?? 'Sistema' }}</span>
                    </div>
                    <p class="text-sm">{{ $evento->descripcion ?? 'Sin descripcion' }}</p>
                    <p class="text-xs text-muted-foreground">{{ $evento->entidad ?? '-' }} {{ $evento->entidad_id ? '#' . $evento->entidad_id : '' }}</p>
                </div>
            @endforeach
        </div>

        @if ($auditorias->isEmpty())
            <div class="p-12 text-center">
                <p class="text-muted-foreground">No se encontraron eventos de auditoria.</p>
            </div>
        @endif
    </div>

    @if ($auditorias->hasPages())
        <div class="card">
            {{ $auditorias->links() }}
        </div>
    @endif
</div>
@endsection

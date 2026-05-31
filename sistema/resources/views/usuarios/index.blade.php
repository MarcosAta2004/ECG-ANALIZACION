@extends('plantillas.aplicacion')

@section('title', 'Usuarios')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Gestion de Usuarios</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Administra accesos, estados y roles del sistema.
    </p>
@endsection

@section('content')
<div
    class="space-y-6"
    x-data="usersPage({
        roles: {{ Js::from($rolesActivos->map(fn ($rol) => ['id' => $rol->id, 'nombre' => $rol->name])->values()) }},
        tab: '{{ $filters['tab'] }}',
    })"
>
    @if (session('status'))
        <div class="rounded-xl px-4 py-3 text-sm font-medium animate-fade-in-up"
             style="background:hsl(var(--success)/0.1);color:hsl(var(--success));border:1px solid hsl(var(--success)/0.2);">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error animate-fade-in-up">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up">
        <div class="card">
            <p class="text-sm text-muted-foreground">Usuarios</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['usuarios']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.2);">
            <p class="text-sm text-muted-foreground">Activos</p>
            <p class="text-2xl font-bold text-success mt-1">{{ number_format($stats['activos']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.2);">
            <p class="text-sm text-muted-foreground">Inactivos</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ number_format($stats['inactivos']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--primary)/0.05);border-color:hsl(var(--primary)/0.2);">
            <p class="text-sm text-muted-foreground">Roles</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ number_format($stats['roles']) }}</p>
        </div>
    </div>

    <div class="card animate-fade-in-up" style="animation-delay:100ms;">
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="tab = 'usuarios'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                    :class="tab === 'usuarios' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground'">
                Usuarios
            </button>
            <button type="button" @click="tab = 'roles'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                    :class="tab === 'roles' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground'">
                Roles
            </button>
        </div>
    </div>

    <section x-show="tab === 'usuarios'" class="space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
            <div class="space-y-6">
                <form method="GET" action="{{ route('usuarios.index') }}" class="card animate-fade-in-up" style="animation-delay:150ms;">
                    <input type="hidden" name="tab" value="usuarios">
                    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_180px_170px_auto] gap-4 items-end">
                        <label class="filter-field">
                            <span class="filter-label">Buscar</span>
                            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nombre o correo" class="input-field" />
                        </label>
                        <label class="filter-field">
                            <span class="filter-label">Rol</span>
                            <select name="role" class="input-field">
                                <option value="">Todos</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected((string) $filters['role'] === (string) $rol->id)>{{ $rol->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span class="filter-label">Estado</span>
                            <select name="status" class="input-field">
                                <option value="all" @selected($filters['status'] === 'all')>Todos</option>
                                <option value="active" @selected($filters['status'] === 'active')>Activos</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactivos</option>
                            </select>
                        </label>
                        <div class="flex gap-3">
                            <button type="submit" class="btn-primary flex-1">Aplicar</button>
                            <a href="{{ route('usuarios.index') }}" class="filter-clear-btn flex-1">Limpiar</a>
                        </div>
                    </div>
                </form>

                <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up" style="animation-delay:200ms;">
                    <div class="overflow-x-auto">
                        <table class="table-ecg">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                    <tr>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-semibold">{{ $usuario->name }}</span>
                                                <span class="text-xs text-muted-foreground">{{ $usuario->email }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $usuario->rolesa?->name ?? 'Sin rol' }}</td>
                                        <td>
                                            <span class="badge {{ $usuario->estado ? 'badge-success' : 'badge-warning' }}">
                                                {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex justify-end">
                                                <button type="button"
                                                        @click="openUserEdit({{ Js::from([
                                                            'id' => $usuario->id,
                                                            'name' => $usuario->name,
                                                            'email' => $usuario->email,
                                                            'role_id' => $usuario->role_id,
                                                            'estado' => $usuario->estado,
                                                            'action' => route('usuarios.update', $usuario),
                                                        ]) }})"
                                                        class="p-2 rounded-lg hover:bg-secondary transition-colors text-primary"
                                                        title="Editar usuario">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($usuarios->isEmpty())
                        <div class="p-12 text-center">
                            <p class="text-muted-foreground">No se encontraron usuarios con los filtros actuales.</p>
                        </div>
                    @endif
                </div>

                @if ($usuarios->hasPages())
                    <div class="card">
                        {{ $usuarios->links() }}
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('usuarios.store') }}" class="card h-fit animate-fade-in-up" style="animation-delay:250ms;">
                @csrf
                <h2 class="text-lg font-semibold mb-1">Nuevo usuario</h2>
                <p class="text-sm text-muted-foreground mb-5">Crea una cuenta con rol asignado.</p>

                <div class="space-y-4">
                    <label class="block">
                        <span class="filter-label">Nombre</span>
                        <input name="name" value="{{ old('name') }}" class="input-field" required>
                    </label>
                    <label class="block">
                        <span class="filter-label">Correo</span>
                        <input type="email" name="email" value="{{ old('email') }}" class="input-field" required>
                    </label>
                    <label class="block">
                        <span class="filter-label">Rol</span>
                        <select name="role_id" class="input-field" required>
                            @foreach($rolesActivos as $rol)
                                <option value="{{ $rol->id }}" @selected((string) old('role_id') === (string) $rol->id)>{{ $rol->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="filter-label">Contrasena</span>
                        <input type="password" name="password" class="input-field" required>
                    </label>
                    <label class="block">
                        <span class="filter-label">Confirmar contrasena</span>
                        <input type="password" name="password_confirmation" class="input-field" required>
                    </label>
                    <label class="flex items-center gap-3 text-sm">
                        <input type="checkbox" name="estado" value="1" checked class="h-4 w-4">
                        Usuario activo
                    </label>
                    <button type="submit" class="btn-primary w-full">Crear usuario</button>
                </div>
            </form>
        </div>
    </section>

    <section x-show="tab === 'roles'" class="space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
            <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up">
                <div class="overflow-x-auto">
                    <table class="table-ecg">
                        <thead>
                            <tr>
                                <th>Rol</th>
                                <th>Usuarios</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $rol)
                                <tr>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ $rol->name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ $rol->descripcion ?: 'Sin descripcion' }}</span>
                                        </div>
                                    </td>
                                    <td><span class="font-mono text-sm">{{ number_format($rol->usuarios_count) }}</span></td>
                                    <td>
                                        <span class="badge {{ $rol->estado ? 'badge-success' : 'badge-warning' }}">
                                            {{ $rol->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-end">
                                            <button type="button"
                                                    @click="openRoleEdit({{ Js::from([
                                                        'id' => $rol->id,
                                                        'nombre' => $rol->name,
                                                        'descripcion' => $rol->descripcion,
                                                        'estado' => $rol->estado,
                                                        'action' => route('roles.update', $rol),
                                                    ]) }})"
                                                    class="p-2 rounded-lg hover:bg-secondary transition-colors text-primary"
                                                    title="Editar rol">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <form method="POST" action="{{ route('roles.store') }}" class="card h-fit animate-fade-in-up" style="animation-delay:100ms;">
                @csrf
                <h2 class="text-lg font-semibold mb-1">Nuevo rol</h2>
                <p class="text-sm text-muted-foreground mb-5">Define perfiles para clasificar usuarios.</p>

                <div class="space-y-4">
                    <label class="block">
                        <span class="filter-label">Nombre</span>
                        <input name="nombre" value="{{ old('nombre') }}" class="input-field" required>
                    </label>
                    <label class="block">
                        <span class="filter-label">Descripcion</span>
                        <textarea name="descripcion" rows="3" class="input-field resize-none">{{ old('descripcion') }}</textarea>
                    </label>
                    <label class="flex items-center gap-3 text-sm">
                        <input type="checkbox" name="estado" value="1" checked class="h-4 w-4">
                        Rol activo
                    </label>
                    <button type="submit" class="btn-primary w-full">Crear rol</button>
                </div>
            </form>
        </div>
    </section>

    <div x-show="userModal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none; background:rgba(0,0,0,0.4); backdrop-filter:blur(4px);" @click.self="userModal.open = false">
        <form method="POST" :action="userModal.action" class="w-full max-w-md rounded-2xl shadow-2xl p-6"
              style="background:hsl(var(--card)); border:1px solid hsl(var(--border));">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" value="usuarios">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-semibold">Editar usuario</h3>
                    <p class="text-xs text-muted-foreground">Actualiza perfil, rol o estado.</p>
                </div>
                <button type="button" @click="userModal.open = false" class="p-1.5 rounded-lg hover:bg-secondary transition-colors text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <label class="block">
                    <span class="filter-label">Nombre</span>
                    <input name="name" x-model="userModal.name" class="input-field" required>
                </label>
                <label class="block">
                    <span class="filter-label">Correo</span>
                    <input type="email" name="email" x-model="userModal.email" class="input-field" required>
                </label>
                <label class="block">
                    <span class="filter-label">Rol</span>
                    <select name="role_id" x-model="userModal.role_id" class="input-field" required>
                        <template x-for="role in roles" :key="role.id">
                            <option :value="role.id" x-text="role.nombre"></option>
                        </template>
                    </select>
                </label>
                <label class="block">
                    <span class="filter-label">Nueva contrasena</span>
                    <input type="password" name="password" class="input-field" placeholder="Dejar vacio para conservar">
                </label>
                <label class="block">
                    <span class="filter-label">Confirmar nueva contrasena</span>
                    <input type="password" name="password_confirmation" class="input-field">
                </label>
                <label class="flex items-center gap-3 text-sm">
                    <input type="checkbox" name="estado" value="1" x-model="userModal.estado" class="h-4 w-4">
                    Usuario activo
                </label>
                <button type="submit" class="btn-primary w-full">Guardar cambios</button>
            </div>
        </form>
    </div>

    <div x-show="roleModal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none; background:rgba(0,0,0,0.4); backdrop-filter:blur(4px);" @click.self="roleModal.open = false">
        <form method="POST" :action="roleModal.action" class="w-full max-w-md rounded-2xl shadow-2xl p-6"
              style="background:hsl(var(--card)); border:1px solid hsl(var(--border));">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-semibold">Editar rol</h3>
                    <p class="text-xs text-muted-foreground">Ajusta nombre, descripcion o estado.</p>
                </div>
                <button type="button" @click="roleModal.open = false" class="p-1.5 rounded-lg hover:bg-secondary transition-colors text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <label class="block">
                    <span class="filter-label">Nombre</span>
                    <input name="nombre" x-model="roleModal.nombre" class="input-field" required>
                </label>
                <label class="block">
                    <span class="filter-label">Descripcion</span>
                    <textarea name="descripcion" x-model="roleModal.descripcion" rows="3" class="input-field resize-none"></textarea>
                </label>
                <label class="flex items-center gap-3 text-sm">
                    <input type="checkbox" name="estado" value="1" x-model="roleModal.estado" class="h-4 w-4">
                    Rol activo
                </label>
                <button type="submit" class="btn-primary w-full">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

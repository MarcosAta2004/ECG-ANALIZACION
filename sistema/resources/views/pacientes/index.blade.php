@extends('plantillas.aplicacion')

@section('title', 'Pacientes')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Pacientes</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Administra el registro y codigo de identificacion de los pacientes.
    </p>
@endsection

@section('content')
    <div class="space-y-6" x-data="patientsPage">
        @if (session('ok'))
            <div class="rounded-xl px-4 py-3 text-sm font-medium animate-fade-in-up"
                style="background:hsl(var(--success)/0.1);color:hsl(var(--success));border:1px solid hsl(var(--success)/0.2);">
                {{ session('message') }} {{ session('data') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
            <div class="space-y-6">
                {{-- Buscador --}}
                <form method="GET" action="{{ route('pacientes.index') }}" class="card animate-fade-in-up">
                    <div class="flex gap-4 items-end">
                        <label class="filter-field flex-1">
                            <span class="filter-label">Buscar Paciente</span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: HOSP-2024-001"
                                class="input-field" />
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary px-6">Buscar</button>
                            @if(request('search'))
                                <a href="{{ route('pacientes.index') }}" class="filter-clear-btn">Limpiar</a>
                            @endif
                        </div>
                    </div>
                </form>

                {{-- Tabla --}}
                <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up"
                    style="animation-delay:100ms;">
                    <div class="overflow-x-auto">
                        <table class="table-ecg">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Genero</th>
                                    <th>Edad</th>
                                    <th>Peso</th>
                                    <th>Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pacientes as $paciente)
                                    <tr>
                                        <td>
                                            <span
                                                class="font-mono text-sm font-bold text-primary">{{ $paciente->codigo_generado }}</span>
                                        </td>
                                        <td>{{ $paciente->sexo === 'M' ? 'Masculino' : ($paciente->sexo === 'F' ? 'Femenino' : 'N/A') }}
                                        </td>
                                        <td>
                                            {{ $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' anos' : 'N/D' }}
                                        </td>
                                        <td>{{ $paciente->peso ? $paciente->peso . ' kg' : 'N/D' }}</td>
                                        <td>
                                            <span class="badge {{ $paciente->estado ? 'badge-success' : 'badge-warning' }}">
                                                {{ $paciente->estado ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex justify-end gap-2">
                                                <button @click="openEdit({{ Js::from($paciente) }})"
                                                    class="p-2 rounded-lg hover:bg-secondary text-primary transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-12 text-center text-muted-foreground">
                                            No hay pacientes registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($pacientes->hasPages())
                    <div class="mt-4">
                        {{ $pacientes->links() }}
                    </div>
                @endif
            </div>

            {{-- Formulario Lateral --}}
            <div class="space-y-6">
                <form method="POST" action="{{ route('pacientes.store') }}" class="card animate-fade-in-up"
                    style="animation-delay:200ms;">
                    @csrf
                    <h2 class="text-lg font-semibold mb-1">Nuevo Paciente</h2>
                    <p class="text-xs text-muted-foreground mb-5">El codigo se generara automaticamente.</p>

                    <div class="space-y-4">
                        <label class="block">
                            <span class="filter-label">Prefijo Institucional</span>
                            <select name="prefijo_id" class="input-field" required>
                                @foreach($prefijos as $prefijo)
                                    <option value="{{ $prefijo->prefijo_id }}">{{ $prefijo->nombre }} -
                                        {{ $prefijo->descripcion }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="filter-label">Fecha de Nacimiento</span>
                            <input type="date" name="fecha_nacimiento" class="input-field">
                        </label>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="block">
                                <span class="filter-label">Sexo</span>
                                <select name="sexo" class="input-field">
                                    <option value="M">Masc.</option>
                                    <option value="F">Fem.</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="filter-label">Peso (kg)</span>
                                <input type="number" step="0.1" name="peso" class="input-field" placeholder="0.0">
                            </label>
                        </div>

                        <button type="submit" class="btn-primary w-full mt-4">Registrar Paciente</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edicion --}}
        <div x-show="editModal.open"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm"
            style="display:none;">
            <form :action="'{{ url('clinico/pacientes/update') }}/' + editModal.data.paciente_id" method="POST"
                class="card w-full max-w-md shadow-2xl">
                @csrf
                @method('PUT')
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Editar Paciente</h3>
                    <button type="button" @click="editModal.open = false"
                        class="text-muted-foreground hover:text-foreground">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="filter-label">Codigo Generado</label>
                        <input type="text" :value="editModal.data.codigo_generado" class="input-field bg-muted" disabled>
                    </div>
                    <div>
                        <label class="filter-label">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" x-model="editModal.data.fecha_nacimiento"
                            class="input-field">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="filter-label">Sexo</label>
                            <select name="sexo" x-model="editModal.data.sexo" class="input-field">
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Peso (kg)</label>
                            <input type="number" step="0.1" name="peso" x-model="editModal.data.peso" class="input-field">
                        </div>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="editModal.open = false" class="btn-secondary flex-1">Cancelar</button>
                        <button type="submit" class="btn-primary flex-1">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

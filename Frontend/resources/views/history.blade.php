@extends('layouts.app')

@section('title', 'Historial')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Historial de Análisis</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Consulta análisis anteriores y añade valoraciones médicas
    </p>
@endsection

@section('content')
@php
    $total     = count($history);
    $normales  = collect($history)->where('type', 'normal')->count();
    $arritmias = $total - $normales;
    $revisados = collect($history)->whereNotNull('doctor_result')->count();
@endphp

<div class="space-y-6"
     x-data="{
        search: '',
        filter: 'all',
        history: {{ Js::from($history) }},
        CSRF: '{{ csrf_token() }}',

        reviewModal: {
            open:   false,
            id:     null,
            result: '',
            label:  '',
            notes:  '',
            saving: false,
            error:  ''
        },

        get filtered() {
            return this.history.filter(item => {
                const matchSearch = !this.search ||
                    item.filename.toLowerCase().includes(this.search.toLowerCase()) ||
                    item.rhythm.toLowerCase().includes(this.search.toLowerCase());
                let matchFilter = true;
                if (this.filter === 'normal' || this.filter === 'arritmia') {
                    matchFilter = item.type === this.filter;
                } else if (this.filter === 'reviewed') {
                    matchFilter = item.doctor_result !== null;
                } else if (this.filter === 'unreviewed') {
                    matchFilter = item.doctor_result === null;
                }
                return matchSearch && matchFilter;
            });
        },

        openReview(item) {
            this.reviewModal = {
                open:   true,
                id:     item.id,
                result: item.doctor_result || '',
                label:  item.doctor_label  || '',
                notes:  item.doctor_notes  || '',
                saving: false,
                error:  ''
            };
        },

        async submitReview() {
            if (!this.reviewModal.result) {
                this.reviewModal.error = 'Selecciona un resultado médico.';
                return;
            }
            this.reviewModal.saving = true;
            this.reviewModal.error  = '';
            try {
                const resp = await fetch(`/history/${this.reviewModal.id}/review`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.CSRF },
                    body:    JSON.stringify({
                        doctor_result: this.reviewModal.result,
                        doctor_label:  this.reviewModal.label,
                        doctor_notes:  this.reviewModal.notes,
                    })
                });
                if (!resp.ok) throw new Error();
                const data = await resp.json();
                const idx = this.history.findIndex(h => h.id === this.reviewModal.id);
                if (idx >= 0) {
                    this.history[idx].doctor_result = this.reviewModal.result;
                    this.history[idx].doctor_label  = this.reviewModal.label;
                    this.history[idx].doctor_notes  = this.reviewModal.notes;
                    this.history[idx].reviewed_at   = data.reviewed_at;
                }
                this.reviewModal.open = false;
            } catch(e) {
                this.reviewModal.error = 'No se pudo guardar. Intenta de nuevo.';
            } finally {
                this.reviewModal.saving = false;
            }
        },

        async removeReview() {
            this.reviewModal.saving = true;
            this.reviewModal.error  = '';
            try {
                await fetch(`/history/${this.reviewModal.id}/review`, {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.CSRF }
                });
                const idx = this.history.findIndex(h => h.id === this.reviewModal.id);
                if (idx >= 0) {
                    this.history[idx].doctor_result = null;
                    this.history[idx].doctor_label  = null;
                    this.history[idx].doctor_notes  = null;
                    this.history[idx].reviewed_at   = null;
                }
                this.reviewModal.open = false;
            } catch(e) {
                this.reviewModal.error = 'No se pudo eliminar la valoración.';
            } finally {
                this.reviewModal.saving = false;
            }
        }
     }">

    {{-- Resumen rápido --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up">
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Total Análisis</p>
                    <p class="text-2xl font-bold">{{ $total }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8"
                     style="color:hsl(var(--primary)/0.5);" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                             a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <div class="card" style="background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.2);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Normales</p>
                    <p class="text-2xl font-bold text-success">{{ $normales }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8"
                     style="color:hsl(var(--success)/0.5);" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="card" style="background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.2);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Arritmias</p>
                    <p class="text-2xl font-bold text-warning">{{ $arritmias }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8"
                     style="color:hsl(var(--warning)/0.5);" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3
                             L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
        <div class="card" style="background:hsl(var(--primary)/0.05);border-color:hsl(var(--primary)/0.2);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Revisados</p>
                    <p class="text-2xl font-bold text-primary">{{ $revisados }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8"
                     style="color:hsl(var(--primary)/0.5);" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Búsqueda y filtros --}}
    <div class="flex flex-col sm:flex-row gap-4 animate-fade-in-up" style="animation-delay:100ms;">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" x-model="search"
                   placeholder="Buscar por nombre de archivo o ritmo..."
                   class="input-field pl-10 bg-card" />
        </div>
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-muted-foreground shrink-0"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19
                         a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <select x-model="filter" class="input-field bg-card" style="width:auto;">
                <option value="all">Todos</option>
                <option value="normal">Solo Normales</option>
                <option value="arritmia">Solo Arritmias</option>
                <option value="reviewed">Con Valoración</option>
                <option value="unreviewed">Sin Valoración</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up"
         style="animation-delay:200ms;">

        {{-- Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="table-ecg">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Ritmo Detectado</th>
                        <th>Probabilidad</th>
                        <th>Estado IA</th>
                        <th>Valoración Médica</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in filtered" :key="item.id">
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg" style="background:hsl(var(--primary)/0.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                                     a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium" x-text="item.filename"></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1 text-muted-foreground text-xs">
                                    <span x-text="item.date"></span>
                                    <span>•</span>
                                    <span x-text="item.time"></span>
                                </div>
                            </td>
                            <td x-text="item.rhythm"></td>
                            <td>
                                <span class="font-mono text-sm" x-text="item.probability + '%'"></span>
                            </td>
                            <td>
                                <span class="badge"
                                      :class="item.type === 'normal' ? 'badge-success' : 'badge-warning'"
                                      x-text="item.result"></span>
                            </td>
                            {{-- Valoración médica --}}
                            <td>
                                <template x-if="item.doctor_result">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="badge"
                                              :class="item.doctor_result === 'normal' ? 'badge-success' : 'badge-warning'"
                                              x-text="item.doctor_result === 'normal' ? 'Normal' : 'Arritmia'"></span>
                                        <span class="text-xs text-muted-foreground"
                                              x-text="item.doctor_label || ''"
                                              x-show="item.doctor_label"></span>
                                    </div>
                                </template>
                                <template x-if="!item.doctor_result">
                                    <span class="text-xs text-muted-foreground italic">Sin valorar</span>
                                </template>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Botón valorar / editar --}}
                                    <button @click="openReview(item)"
                                            class="p-2 rounded-lg transition-colors"
                                            :class="item.doctor_result
                                                ? 'hover:bg-primary/10 text-primary'
                                                : 'hover:bg-secondary text-muted-foreground'"
                                            :title="item.doctor_result ? 'Editar valoración' : 'Añadir valoración médica'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Móvil --}}
        <div class="md:hidden divide-y border-border">
            <template x-for="item in filtered" :key="item.id">
                <div class="p-4 hover:bg-muted/30 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" style="background:hsl(var(--primary)/0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                             a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-sm" x-text="item.filename"></p>
                                <p class="text-xs text-muted-foreground"
                                   x-text="item.date + ' • ' + item.time"></p>
                            </div>
                        </div>
                        <span class="badge"
                              :class="item.type === 'normal' ? 'badge-success' : 'badge-warning'"
                              x-text="item.result"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs text-muted-foreground"
                               x-text="'Ritmo: ' + item.rhythm"></p>
                            <p class="text-xs font-mono text-muted-foreground"
                               x-text="'Prob.: ' + item.probability + '%'"></p>
                            {{-- Valoración médica en móvil --}}
                            <template x-if="item.doctor_result">
                                <p class="text-xs font-medium"
                                   :class="item.doctor_result === 'normal' ? 'text-success' : 'text-warning'"
                                   x-text="'Med.: ' + (item.doctor_result === 'normal' ? 'Normal' : 'Arritmia') + (item.doctor_label ? ' · ' + item.doctor_label : '')">
                                </p>
                            </template>
                            <template x-if="!item.doctor_result">
                                <p class="text-xs text-muted-foreground italic">Sin valoración médica</p>
                            </template>
                        </div>
                        <button @click="openReview(item)"
                                class="p-2 rounded-lg hover:bg-secondary transition-colors"
                                :class="item.doctor_result ? 'text-primary' : 'text-muted-foreground'"
                                :title="item.doctor_result ? 'Editar valoración' : 'Añadir valoración'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Sin resultados --}}
        <div x-show="filtered.length === 0" class="p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-muted-foreground mx-auto mb-4"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                         a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-muted-foreground">No se encontraron resultados</p>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────────────────
         MODAL DE VALORACIÓN MÉDICA
    ────────────────────────────────────────────────────────────── --}}
    <div x-show="reviewModal.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.4); backdrop-filter:blur(4px);"
         @click.self="reviewModal.open = false">

        <div x-show="reviewModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md rounded-2xl shadow-2xl p-6"
             style="background:hsl(var(--card)); border:1px solid hsl(var(--border));">

            {{-- Header del modal --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl" style="background:hsl(var(--primary)/0.1);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Valoración Médica</h3>
                        <p class="text-xs text-muted-foreground">Resultado del especialista</p>
                    </div>
                </div>
                <button @click="reviewModal.open = false"
                        class="p-1.5 rounded-lg hover:bg-secondary transition-colors text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Resultado --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">
                    Diagnóstico médico <span class="text-destructive">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="reviewModal.result" value="normal" class="sr-only peer">
                        <div class="p-3 rounded-xl border-2 text-center transition-all
                                    peer-checked:border-success peer-checked:bg-success/10 peer-checked:text-success
                                    border-border hover:border-success/50"
                             style="background:hsl(var(--card));">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-semibold">Normal</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="reviewModal.result" value="arritmia" class="sr-only peer">
                        <div class="p-3 rounded-xl border-2 text-center transition-all
                                    peer-checked:border-warning peer-checked:bg-warning/10 peer-checked:text-warning
                                    border-border hover:border-warning/50"
                             style="background:hsl(var(--card));">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3
                                         L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm font-semibold">Arritmia</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Diagnóstico específico --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1.5">
                    Diagnóstico específico
                    <span class="text-muted-foreground font-normal">(opcional)</span>
                </label>
                <input type="text"
                       x-model="reviewModal.label"
                       placeholder="Ej: Fibrilación auricular, Taquicardia sinusal..."
                       class="input-field w-full" />
            </div>

            {{-- Notas --}}
            <div class="mb-5">
                <label class="block text-sm font-medium mb-1.5">
                    Notas clínicas
                    <span class="text-muted-foreground font-normal">(opcional)</span>
                </label>
                <textarea x-model="reviewModal.notes"
                          rows="3"
                          placeholder="Observaciones adicionales del médico..."
                          class="input-field w-full resize-none"></textarea>
            </div>

            {{-- Error --}}
            <p x-show="reviewModal.error"
               x-text="reviewModal.error"
               class="text-sm text-destructive mb-4 px-3 py-2 rounded-lg"
               style="background:hsl(var(--destructive)/0.08);"></p>

            {{-- Acciones --}}
            <div class="flex gap-3">
                <button @click="submitReview()"
                        :disabled="reviewModal.saving"
                        class="flex-1 py-2.5 rounded-xl font-semibold text-sm text-white transition-opacity
                               hover:opacity-90 disabled:opacity-60"
                        style="background:hsl(var(--primary));">
                    <span x-show="!reviewModal.saving">Guardar Valoración</span>
                    <span x-show="reviewModal.saving">Guardando...</span>
                </button>
                <template x-if="history.find(h => h.id === reviewModal.id)?.doctor_result">
                    <button @click="removeReview()"
                            :disabled="reviewModal.saving"
                            class="px-4 py-2.5 rounded-xl font-medium text-sm text-destructive
                                   hover:bg-destructive/10 transition-colors disabled:opacity-60"
                            style="border:1px solid hsl(var(--destructive)/0.3);">
                        Quitar
                    </button>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection

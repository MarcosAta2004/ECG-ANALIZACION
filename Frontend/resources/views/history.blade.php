@extends('layouts.app')

@section('title', 'Historial')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Historial de Analisis</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Consulta analisis anteriores y anade valoraciones medicas.
    </p>
@endsection

@section('content')
<div
    class="space-y-6"
    x-data="historyPage({
        history: {{ Js::from($history->items()) }},
        csrf: '{{ csrf_token() }}',
    })"
>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up">
        <div class="card">
            <p class="text-sm text-muted-foreground">Total Analisis</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.2);">
            <p class="text-sm text-muted-foreground">Normales</p>
            <p class="text-2xl font-bold text-success mt-1">{{ number_format($stats['normales']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.2);">
            <p class="text-sm text-muted-foreground">Arritmias</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ number_format($stats['arritmias']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--primary)/0.05);border-color:hsl(var(--primary)/0.2);">
            <p class="text-sm text-muted-foreground">Revisados</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ number_format($stats['revisados']) }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('history') }}" class="card animate-fade-in-up" style="animation-delay:100ms;">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_220px_auto] gap-4 items-end">
            <label class="filter-field">
                <span class="filter-label">Buscar</span>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Archivo, ritmo o diagnostico medico"
                    class="input-field"
                />
            </label>
            <label class="filter-field">
                <span class="filter-label">Filtro</span>
                <select name="filter" class="input-field">
                    <option value="all" @selected($filters['filter'] === 'all')>Todos</option>
                    <option value="normal" @selected($filters['filter'] === 'normal')>Solo normales</option>
                    <option value="arritmia" @selected($filters['filter'] === 'arritmia')>Solo arritmias</option>
                    <option value="reviewed" @selected($filters['filter'] === 'reviewed')>Con valoracion</option>
                    <option value="unreviewed" @selected($filters['filter'] === 'unreviewed')>Sin valoracion</option>
                </select>
            </label>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1">Aplicar</button>
                <a href="{{ route('history') }}" class="filter-clear-btn flex-1">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up" style="animation-delay:200ms;">
        <div class="hidden md:block overflow-x-auto">
            <table class="table-ecg">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Ritmo Detectado</th>
                        <th>Probabilidad</th>
                        <th>Estado IA</th>
                        <th>Valoracion Medica</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in history" :key="item.id">
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg" style="background:hsl(var(--primary)/0.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium" x-text="item.filename"></span>
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-xs font-semibold text-primary" x-text="item.patient"></span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1 text-muted-foreground text-xs">
                                    <span x-text="item.date"></span>
                                    <span>&bull;</span>
                                    <span x-text="item.time"></span>
                                </div>
                            </td>
                            <td x-text="item.rhythm"></td>
                            <td><span class="font-mono text-sm" x-text="item.probability + '%'"></span></td>
                            <td>
                                <span class="badge" :class="item.type === 'normal' ? 'badge-success' : 'badge-warning'" x-text="item.result"></span>
                            </td>
                            <td>
                                <template x-if="item.doctor_result">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="badge" :class="item.doctor_result === 'normal' ? 'badge-success' : 'badge-warning'" x-text="item.doctor_result === 'normal' ? 'Normal' : 'Arritmia'"></span>
                                        <span class="text-xs text-muted-foreground" x-show="item.doctor_label" x-text="item.doctor_label || ''"></span>
                                    </div>
                                </template>
                                <template x-if="!item.doctor_result">
                                    <span class="text-xs text-muted-foreground italic">Sin valorar</span>
                                </template>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        @click="openReview(item)"
                                        class="p-2 rounded-lg transition-colors"
                                        :class="item.doctor_result ? 'hover:bg-primary/10 text-primary' : 'hover:bg-secondary text-muted-foreground'"
                                        :title="item.doctor_result ? 'Editar valoracion' : 'Anadir valoracion medica'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y border-border">
            <template x-for="item in history" :key="item.id">
                <div class="p-4 hover:bg-muted/30 transition-colors">
                    <div class="flex items-start justify-between mb-3 gap-3">
                        <div>
                            <p class="font-medium text-sm" x-text="item.filename"></p>
                            <p class="text-xs font-mono text-primary" x-text="item.patient"></p>
                            <p class="text-xs text-muted-foreground" x-text="item.date + ' • ' + item.time"></p>
                        </div>
                        <span class="badge" :class="item.type === 'normal' ? 'badge-success' : 'badge-warning'" x-text="item.result"></span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs text-muted-foreground" x-text="'Ritmo: ' + item.rhythm"></p>
                        <p class="text-xs font-mono text-muted-foreground" x-text="'Prob.: ' + item.probability + '%'"></p>
                        <template x-if="item.doctor_result">
                            <p class="text-xs font-medium" :class="item.doctor_result === 'normal' ? 'text-success' : 'text-warning'" x-text="'Med.: ' + (item.doctor_result === 'normal' ? 'Normal' : 'Arritmia') + (item.doctor_label ? ' • ' + item.doctor_label : '')"></p>
                        </template>
                        <template x-if="!item.doctor_result">
                            <p class="text-xs text-muted-foreground italic">Sin valoracion medica</p>
                        </template>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button
                            type="button"
                            @click="openReview(item)"
                            class="p-2 rounded-lg hover:bg-secondary transition-colors"
                            :class="item.doctor_result ? 'text-primary' : 'text-muted-foreground'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        @if ($history->isEmpty())
            <div class="p-12 text-center">
                <p class="text-muted-foreground">No se encontraron resultados con los filtros actuales.</p>
            </div>
        @endif
    </div>

    @if ($history->hasPages())
        <div class="card">
            {{ $history->links() }}
        </div>
    @endif

    <div
        x-show="reviewModal.open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none; background:rgba(0,0,0,0.4); backdrop-filter:blur(4px);"
        @click.self="reviewModal.open = false"
    >
        <div
            x-show="reviewModal.open"
            x-transition
            class="w-full max-w-md rounded-2xl shadow-2xl p-6"
            style="background:hsl(var(--card)); border:1px solid hsl(var(--border));"
        >
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-semibold">Valoracion Medica</h3>
                    <p class="text-xs text-muted-foreground">Resultado del especialista</p>
                </div>
                <button type="button" @click="reviewModal.open = false" class="p-1.5 rounded-lg hover:bg-secondary transition-colors text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Diagnostico medico <span class="text-destructive">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="reviewModal.result" value="normal" class="sr-only peer">
                        <div class="p-3 rounded-xl border-2 text-center transition-all peer-checked:border-success peer-checked:bg-success/10 peer-checked:text-success border-border hover:border-success/50">
                            <p class="text-sm font-semibold">Normal</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="reviewModal.result" value="arritmia" class="sr-only peer">
                        <div class="p-3 rounded-xl border-2 text-center transition-all peer-checked:border-warning peer-checked:bg-warning/10 peer-checked:text-warning border-border hover:border-warning/50">
                            <p class="text-sm font-semibold">Arritmia</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1.5">Diagnostico especifico</label>
                <input type="text" x-model="reviewModal.label" placeholder="Ej: Fibrilacion auricular" class="input-field w-full" />
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium mb-1.5">Notas clinicas</label>
                <textarea x-model="reviewModal.notes" rows="3" placeholder="Observaciones adicionales del medico" class="input-field w-full resize-none"></textarea>
            </div>

            <p x-show="reviewModal.error" x-text="reviewModal.error" class="text-sm text-destructive mb-4 px-3 py-2 rounded-lg" style="background:hsl(var(--destructive)/0.08);"></p>

            <div class="flex gap-3">
                <button type="button" @click="submitReview()" :disabled="reviewModal.saving" class="flex-1 py-2.5 rounded-xl font-semibold text-sm text-white transition-opacity hover:opacity-90 disabled:opacity-60" style="background:hsl(var(--primary));">
                    <span x-show="!reviewModal.saving">Guardar Valoracion</span>
                    <span x-show="reviewModal.saving">Guardando...</span>
                </button>
                <template x-if="findHistoryItem(reviewModal.id)?.doctor_result">
                    <button type="button" @click="removeReview()" :disabled="reviewModal.saving" class="px-4 py-2.5 rounded-xl font-medium text-sm text-destructive hover:bg-destructive/10 transition-colors disabled:opacity-60" style="border:1px solid hsl(var(--destructive)/0.3);">
                        Quitar
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function historyPage(config) {
    return {
        history: config.history ?? [],
        csrf: config.csrf,
        reviewModal: {
            open: false,
            id: null,
            result: '',
            label: '',
            notes: '',
            saving: false,
            error: '',
        },

        findHistoryItem(id) {
            return this.history.find((item) => item.id === id) ?? null;
        },

        openReview(item) {
            this.reviewModal = {
                open: true,
                id: item.id,
                result: item.doctor_result || '',
                label: item.doctor_label || '',
                notes: item.doctor_notes || '',
                saving: false,
                error: '',
            };
        },

        async submitReview() {
            if (!this.reviewModal.result) {
                this.reviewModal.error = 'Selecciona un resultado medico.';
                return;
            }

            this.reviewModal.saving = true;
            this.reviewModal.error = '';

            try {
                const resp = await fetch(`/history/${this.reviewModal.id}/review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        doctor_result: this.reviewModal.result,
                        doctor_label: this.reviewModal.label,
                        doctor_notes: this.reviewModal.notes,
                    }),
                });

                const payload = await resp.json().catch(() => ({}));
                if (!resp.ok) {
                    throw new Error(payload.message || payload.error || 'No se pudo guardar la valoracion.');
                }

                const item = this.findHistoryItem(this.reviewModal.id);
                if (item) {
                    item.doctor_result = this.reviewModal.result;
                    item.doctor_label = this.reviewModal.label;
                    item.doctor_notes = this.reviewModal.notes;
                    item.reviewed_at = payload.reviewed_at ?? null;
                }

                this.reviewModal.open = false;
            } catch (error) {
                this.reviewModal.error = error.message || 'No se pudo guardar. Intenta de nuevo.';
            } finally {
                this.reviewModal.saving = false;
            }
        },

        async removeReview() {
            this.reviewModal.saving = true;
            this.reviewModal.error = '';

            try {
                const resp = await fetch(`/history/${this.reviewModal.id}/review`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                });

                const payload = await resp.json().catch(() => ({}));
                if (!resp.ok) {
                    throw new Error(payload.message || payload.error || 'No se pudo eliminar la valoracion.');
                }

                const item = this.findHistoryItem(this.reviewModal.id);
                if (item) {
                    item.doctor_result = null;
                    item.doctor_label = null;
                    item.doctor_notes = null;
                    item.reviewed_at = null;
                }

                this.reviewModal.open = false;
            } catch (error) {
                this.reviewModal.error = error.message || 'No se pudo eliminar la valoracion.';
            } finally {
                this.reviewModal.saving = false;
            }
        },
    };
}
</script>
@endsection

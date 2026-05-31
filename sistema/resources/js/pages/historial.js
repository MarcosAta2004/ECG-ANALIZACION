export function historyPage(config = {}) {
    return {
        history: config.history ?? [],
        csrf: config.csrf ?? '',
        canReview: Boolean(config.canReview),
        reviewUrlTemplate: config.reviewUrlTemplate ?? '/clinico/diagnosticos/__ID__/review',
        reviewDeleteUrlTemplate: config.reviewDeleteUrlTemplate ?? '/clinico/diagnosticos/__ID__/review',
        reviewModal: {
            open: false,
            id: null,
            result: '',
            label: '',
            notes: '',
            saving: false,
            error: '',
        },

        buildUrl(template, id) {
            return String(template || '').replace('__ID__', encodeURIComponent(id));
        },

        findHistoryItem(id) {
            return this.history.find((item) => item.id === id) ?? null;
        },

        openReview(item) {
            if (!this.canReview) {
                return;
            }

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
                const resp = await fetch(this.buildUrl(this.reviewUrlTemplate, this.reviewModal.id), {
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
                const resp = await fetch(this.buildUrl(this.reviewDeleteUrlTemplate, this.reviewModal.id), {
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
                this.reviewModal.error = error.message || 'No se pudo eliminar. Intenta de nuevo.';
            } finally {
                this.reviewModal.saving = false;
            }
        },
    };
}

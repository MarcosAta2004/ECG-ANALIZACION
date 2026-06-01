export function historyPage(config = {}) {
    return {
        history: config.history ?? [],
        ritmos: config.ritmos ?? [],
        csrf: config.csrf ?? '',
        canReview: Boolean(config.canReview),
        reviewUrlTemplate: config.reviewUrlTemplate ?? '/clinico/diagnosticos/__ID__/review',
        reviewDeleteUrlTemplate: config.reviewDeleteUrlTemplate ?? '/clinico/diagnosticos/__ID__/review',
        reviewModal: {
            open: false,
            id: null,
            result: '',
            ritmo_id: '',
            notes: '',
            saving: false,
            error: '',
        },

        buildUrl(template, id) {
            return String(template || '').replace('__ID__', encodeURIComponent(id));
        },

        isNormalValue(value) {
            return ['normal', 'sano'].includes(String(value || '').trim().toLowerCase());
        },

        isNormalRhythmCode(value) {
            return String(value || '').trim().toUpperCase() === 'NORM';
        },

        normalRitmoId() {
            const ritmo = this.ritmos.find((item) => this.isNormalRhythmCode(item.label));
            return ritmo ? String(ritmo.ritmo_id) : '';
        },

        arrhythmiaRitmos() {
            return this.ritmos.filter((item) => !this.isNormalRhythmCode(item.label));
        },

        findHistoryItem(id) {
            return this.history.find((item) => item.id === id) ?? null;
        },

        openReview(item) {
            if (!this.canReview) {
                return;
            }

            const ritmo = item.doctor_ritmo_id
                ? this.ritmos.find((r) => String(r.ritmo_id) === String(item.doctor_ritmo_id))
                : null;
            const result = ritmo
                ? (this.isNormalRhythmCode(ritmo.label) ? 'normal' : 'arritmia')
                : (item.doctor_result || '');

            this.reviewModal = {
                open: true,
                id: item.id,
                result,
                ritmo_id: item.doctor_ritmo_id ? String(item.doctor_ritmo_id) : (result === 'normal' ? this.normalRitmoId() : ''),
                notes: item.doctor_notes || '',
                saving: false,
                error: '',
            };
        },

        async submitReview() {
            if (!this.reviewModal.result) {
                this.reviewModal.error = 'Selecciona Normal o Arritmia.';
                return;
            }

            if (this.reviewModal.result === 'arritmia' && !this.reviewModal.ritmo_id) {
                this.reviewModal.error = 'Selecciona un ritmo cardiaco.';
                return;
            }

            this.reviewModal.saving = true;
            this.reviewModal.error = '';

            const requestPayload = {
                doctor_result: this.reviewModal.result,
                doctor_notes: this.reviewModal.notes,
            };

            if (this.reviewModal.result === 'arritmia') {
                requestPayload.doctor_ritmo_id = this.reviewModal.ritmo_id;
            } else {
                requestPayload.doctor_ritmo_id = this.normalRitmoId();
            }

            try {
                const resp = await fetch(this.buildUrl(this.reviewUrlTemplate, this.reviewModal.id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(requestPayload),
                });

                const responsePayload = await resp.json().catch(() => ({}));
                if (!resp.ok) {
                    throw new Error(responsePayload.message || responsePayload.error || 'No se pudo guardar la valoracion.');
                }

                const item = this.findHistoryItem(this.reviewModal.id);
                if (item) {
                    const ritmoId = this.reviewModal.result === 'arritmia'
                        ? this.reviewModal.ritmo_id
                        : this.normalRitmoId();
                    const ritmo = this.ritmos.find((r) => String(r.ritmo_id) === String(ritmoId));
                    const isNormal = this.isNormalRhythmCode(ritmo?.label);
                    item.doctor_ritmo_id = ritmoId;
                    item.doctor_result = isNormal ? 'normal' : 'arritmia';
                    item.doctor_label = ritmo?.nombre ?? '';
                    item.doctor_notes = this.reviewModal.notes;
                    item.reviewed_at = responsePayload.reviewed_at ?? null;
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

                const responsePayload = await resp.json().catch(() => ({}));
                if (!resp.ok) {
                    throw new Error(responsePayload.message || responsePayload.error || 'No se pudo eliminar la valoracion.');
                }

                const item = this.findHistoryItem(this.reviewModal.id);
                if (item) {
                    item.doctor_result = null;
                    item.doctor_ritmo_id = null;
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

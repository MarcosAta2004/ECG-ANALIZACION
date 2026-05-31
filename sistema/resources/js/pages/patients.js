export function patientsPage() {
    return {
        editModal: {
            open: false,
            data: {},
        },

        openEdit(paciente) {
            this.editModal.data = { ...paciente };
            this.editModal.open = true;
        },
    };
}

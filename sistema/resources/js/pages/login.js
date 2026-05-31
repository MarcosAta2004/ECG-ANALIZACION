export function loginForm() {
    return {
        loading: false,
        handleSubmit(e) {
            this.loading = true;
            e.target.submit();
        },
    };
}

export function usersPage(config = {}) {
    return {
        roles: config.roles ?? [],
        tab: config.tab || 'usuarios',
        userModal: {
            open: false,
            action: '',
            name: '',
            email: '',
            role_id: '',
            estado: true,
        },
        roleModal: {
            open: false,
            action: '',
            nombre: '',
            descripcion: '',
            estado: true,
        },

        openUserEdit(user) {
            this.userModal = {
                open: true,
                action: user.action,
                name: user.name,
                email: user.email,
                role_id: String(user.role_id ?? ''),
                estado: Boolean(user.estado),
            };
        },

        openRoleEdit(role) {
            this.roleModal = {
                open: true,
                action: role.action,
                nombre: role.nombre,
                descripcion: role.descripcion || '',
                estado: Boolean(role.estado),
            };
        },
    };
}

export function usersPage(config = {}) {
    return {
        roles: config.roles ?? [],
        tab: config.tab || 'usuarios',
        userModal: {
            open: false,
            action: '',
            login: '',
            nombres: '',
            apellido_paterno: '',
            apellido_materno: '',
            tipo_documento_identidad_id: '',
            numero_documento: '',
            rol_id: '',
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
                login: user.login || '',
                nombres: user.nombres || '',
                apellido_paterno: user.apellido_paterno || '',
                apellido_materno: user.apellido_materno || '',
                tipo_documento_identidad_id: user.tipo_documento_identidad_id || '',
                numero_documento: user.numero_documento || '',
                rol_id: String(user.rol_id ?? ''),
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

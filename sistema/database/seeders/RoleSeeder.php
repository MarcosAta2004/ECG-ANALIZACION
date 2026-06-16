<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ─── ROLES ───────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        $cardiologo = Role::firstOrCreate(['name' => 'CARDIOLOGO']);
        $licenciado = Role::firstOrCreate(['name' => 'LICENCIADO']);

        // ─── SEGURIDAD — solo ADMINISTRADOR ──────────────────────────

        // Usuarios
        Permission::firstOrCreate(['name' => 'usuarios.index'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.create'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.store'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.edit'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.update'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.destroy'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.activar'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.roles_edit'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'usuarios.roles_update'])->syncRoles([$admin]);

        // Roles
        Permission::firstOrCreate(['name' => 'roles.index'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'roles.create'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'roles.store'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'roles.show'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'roles.edit'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'roles.update'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'roles.destroy'])->syncRoles([$admin]);

        // Auditoría
        Permission::firstOrCreate(['name' => 'auditoria.index'])->syncRoles([$admin]);

        // ─── MÓDULO CLÍNICO ───────────────────────────────────────────

        // Pacientes
        Permission::firstOrCreate(['name' => 'pacientes.index'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'pacientes.store'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'pacientes.show'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'pacientes.update'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'pacientes.destroy'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'pacientes.activar'])->syncRoles([$admin, $licenciado]);

        // Estudios
        Permission::firstOrCreate(['name' => 'estudios.index'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.store'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.show'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.update'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.destroy'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.activar'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.observacion'])->syncRoles([$admin, $licenciado]);

        // Imágenes ECG
        Permission::firstOrCreate(['name' => 'imagenes.index'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'imagenes.store'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'imagenes.destroy'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'imagenes.activar'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'imagenes.ecg.ver'])->syncRoles([$admin, $licenciado, $cardiologo]);
        Permission::firstOrCreate(['name' => 'imagenes.ecg.download'])->syncRoles([$admin, $licenciado, $cardiologo]);
        Permission::firstOrCreate(['name' => 'imagenes.analyze'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'imagenes.analizar'])->syncRoles([$admin, $licenciado]);

        // Ritmos Cardíacos
        Permission::firstOrCreate(['name' => 'ritmos-cardiacos.index'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'ritmos-cardiacos.store'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'ritmos-cardiacos.update'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'ritmos-cardiacos.destroy'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'ritmos-cardiacos.activar'])->syncRoles([$admin, $cardiologo]);


        // Predicciones IA — solo ADMINISTRADOR
        Permission::firstOrCreate(['name' => 'predicciones.index'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'predicciones.destroy'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'predicciones.activar'])->syncRoles([$admin, $licenciado]);

        // Diagnósticos
        Permission::firstOrCreate(['name' => 'diagnosticos.index'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'diagnosticos.store'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'diagnosticos.update'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'diagnosticos.destroy'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'diagnosticos.activar'])->syncRoles([$admin]);
        Permission::firstOrCreate(['name' => 'diagnosticos.estudio.info'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'diagnosticos.review'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'diagnosticos.deleteReview'])->syncRoles([$admin, $cardiologo]);

        // ─── MÓDULO MANTENIMIENTOS — ADMINISTRADOR y LICENCIADO ──────
        // Grupos Cardíacos
        Permission::firstOrCreate(['name' => 'grupos-cardiacos.index'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'grupos-cardiacos.store'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'grupos-cardiacos.update'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'grupos-cardiacos.destroy'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'grupos-cardiacos.activar'])->syncRoles([$admin, $cardiologo]);

        // Niveles de Gravedad
        Permission::firstOrCreate(['name' => 'niveles-gravedad.index'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'niveles-gravedad.store'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'niveles-gravedad.update'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'niveles-gravedad.destroy'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'niveles-gravedad.activar'])->syncRoles([$admin, $cardiologo]);

        // Clasificaciones de Arritmia
        Permission::firstOrCreate(['name' => 'clasificaciones-arritmia.index'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'clasificaciones-arritmia.store'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'clasificaciones-arritmia.update'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'clasificaciones-arritmia.destroy'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'clasificaciones-arritmia.activar'])->syncRoles([$admin, $cardiologo]);

        // Prefijos de Paciente
        Permission::firstOrCreate(['name' => 'prefijos-paciente.index'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'prefijos-paciente.store'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'prefijos-paciente.update'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'prefijos-paciente.destroy'])->syncRoles([$admin, $cardiologo]);
        Permission::firstOrCreate(['name' => 'prefijos-paciente.activar'])->syncRoles([$admin, $cardiologo]);

        // ─── MÓDULO REPORTES ──────────────────────────────────────────

        Permission::firstOrCreate(['name' => 'reportes.index'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.download'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.store'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.update'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.destroy'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.activar'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.estudio.pdf'])->syncRoles([$admin, $licenciado]);
        Permission::firstOrCreate(['name' => 'reportes.estudio.pdf.download'])->syncRoles([$admin, $licenciado]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Roles de forma segura
        $admin = Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        $cardiologo = Role::firstOrCreate(['name' => 'CARDIOLOGO']);
        $licenciado = Role::firstOrCreate(['name' => 'LICENCIADO']);

        // Crear Permisos Básicos (Ejemplo)
        Permission::firstOrCreate(['name' => 'usuarios.index'])->assignRole($admin);
        Permission::firstOrCreate(['name' => 'pacientes.index'])->assignRole([$admin, $cardiologo, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.index'])->assignRole([$admin, $cardiologo, $licenciado]);
        Permission::firstOrCreate(['name' => 'diagnosticos.store'])->assignRole($cardiologo);
    }
}

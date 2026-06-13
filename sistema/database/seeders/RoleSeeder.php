<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Roles de forma segura
        $admin      = Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        $cardiologo = Role::firstOrCreate(['name' => 'CARDIOLOGO']);
        $licenciado = Role::firstOrCreate(['name' => 'LICENCIADO']);

        // Crear Permisos Básicos (Ejemplo)
        Permission::firstOrCreate(['name' => 'usuarios.index'])->assignRole($admin);
        // Permission::firstOrCreate(['name' => 'usuarios.create'])->assignRole($admin);
        // Permission::firstOrCreate(['name' => 'usuarios.edit'])->assignRole($admin);
        // Permission::firstOrCreate(['name' => 'usuarios.destroy'])->assignRole($admin);

        Permission::create(['name' => 'niveles.index'])->syncRoles([$admin]);
        Permission::create(['name' => 'niveles.create'])->syncRoles([$admin]);
        Permission::create(['name' => 'niveles.edit'])->syncRoles([$admin]);
        Permission::create(['name' => 'niveles.destroy'])->syncRoles([$admin]);
        Permission::create(['name' => 'niveles.show'])->syncRoles([$admin]);

        Permission::firstOrCreate(['name' => 'pacientes.index'])->assignRole([$admin, $cardiologo, $licenciado]);
        Permission::firstOrCreate(['name' => 'estudios.index'])->assignRole([$admin, $cardiologo, $licenciado]);

        Permission::firstOrCreate(['name' => 'diagnosticos.store'])->assignRole($cardiologo);

    }
}

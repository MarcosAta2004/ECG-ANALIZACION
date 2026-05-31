<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de roles dinámicamente para evitar errores de FK
        $rolAdmin = Role::where('name', 'ADMINISTRADOR')->first()?->id ?? 1;
        $rolCardio = Role::where('name', 'CARDIOLOGO')->first()?->id ?? 2;
        $rolLicen = Role::where('name', 'LICENCIADO')->first()?->id ?? 3;

        // 1. Administrador (ID 1)
        $admin = User::updateOrCreate(
            ['usuario' => 'admin'],
            [
                'name' => 'admin',
                'password' => '12345678',
                'nombres' => 'P',
                'apellido_paterno' => 'P',
                'apellido_materno' => '',
                'tipo_documento_identidad_id' => 1,
                'rol_id' => $rolAdmin,
                'numero_documento' => 12345678,
                'estado' => 1,
            ]
        );
        $admin->assignRole('ADMINISTRADOR');

        // 2. Cardiólogo (ID 2/3)
        $cardiologo = User::updateOrCreate(
            ['usuario' => 'cardiologo'],
            [
                'name' => 'Dr. Ronald',
                'password' => '987654321',
                'nombres' => 'Ronald',
                'apellido_paterno' => 'May',
                'apellido_materno' => 'Apasestegui',
                'tipo_documento_identidad_id' => 1,
                'rol_id' => $rolCardio, 
                'numero_documento' => 76122785,
                'estado' => 1,
            ]
        );
        $cardiologo->assignRole('CARDIOLOGO');

        // 3. Licenciado (ID 3/4)
        $licenciado = User::updateOrCreate(
            ['usuario' => 'licenciado'],
            [
                'name' => 'Lic. Fermin',
                'password' => '123456',
                'nombres' => 'Fermin',
                'apellido_paterno' => '',
                'apellido_materno' => '',
                'tipo_documento_identidad_id' => 1,
                'rol_id' => $rolLicen,
                'numero_documento' => 87654321,
                'estado' => 1,
            ]
        );
        $licenciado->assignRole('LICENCIADO');
    }
}

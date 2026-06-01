<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Sembrar datos base de prueba antes de cada test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    /**
     * Crea y autentica un usuario con el rol indicado.
     * Devuelve el usuario autenticado y la respuesta se hace con session.
     */
    protected function loginComo(string $rol = 'administrador'): User
    {
        $roleModel = Role::where('name', $rol)->first();

        $user = User::factory()->create([
            'login'    => 'test_' . $rol,
            'email'    => 'test_' . $rol . '@ecg.test',
            'password' => 'password',
            'estado'   => 1,
        ]);

        if ($roleModel) {
            $user->assignRole($roleModel);
            $user->rol_id = $roleModel->id;
            $user->save();
        }

        $this->actingAs($user);

        // Simular sesión que usa el middleware custom
        session([
            'user' => [
                'id'      => $user->id,
                'login'   => $user->login,
                'role_id' => $roleModel?->id,
                'role'    => $roleModel?->name ?? $rol,
            ],
        ]);

        return $user;
    }
}

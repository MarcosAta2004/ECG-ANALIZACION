<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests de feature para el módulo de Usuarios.
 */
class UserTest extends TestCase
{
    /** @test */
    public function solo_administrador_puede_acceder_al_modulo_usuarios(): void
    {
        $this->loginComo('administrador');
        $response = $this->get(route('usuarios.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function tecnico_no_puede_acceder_al_modulo_usuarios(): void
    {
        $this->loginComo('tecnico');
        $response = $this->get(route('usuarios.index'));
        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function cardiologo_no_puede_acceder_al_modulo_usuarios(): void
    {
        $this->loginComo('cardiologo');
        $response = $this->get(route('usuarios.index'));
        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function store_crea_usuario_con_password_hasheado(): void
    {
        $this->loginComo('administrador');

        // Obtener el ID del rol administrador (sembrado en TestDatabaseSeeder)
        $rolAdmin = Role::where('name', 'administrador')->first();
        $this->assertNotNull($rolAdmin, 'Rol administrador debe existir en la BD de testing');

        $response = $this->post(route('usuarios.store'), [
            'login'            => 'nuevo_usuario_test',
            'nombres'          => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'García',
            'password'         => 'Seguro123!',
            'numero_documento' => '12345678',
            'rol_id'           => $rolAdmin->id,
            'estado'           => 1,
        ]);

        $response->assertRedirect();

        $user = User::where('login', 'nuevo_usuario_test')->first();
        $this->assertNotNull($user, 'El usuario debió haberse creado en la BD');

        // El password debe estar hasheado, nunca en texto plano
        $this->assertNotEquals('Seguro123!', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Seguro123!', $user->password));
    }

    /** @test */
    public function destroy_desactiva_usuario_sin_borrarlo(): void
    {
        $this->loginComo('administrador');
        $usuario = User::factory()->create(['estado' => 1]);

        $response = $this->delete(route('usuarios.destroy', $usuario));

        $response->assertRedirect();
        $usuario->refresh();
        $this->assertEquals(0, $usuario->estado);
        $this->assertDatabaseHas('users', ['id' => $usuario->id]);
    }

    /** @test */
    public function activar_reactiva_un_usuario_desactivado(): void
    {
        $this->loginComo('administrador');
        $usuario = User::factory()->inactivo()->create();

        $response = $this->put(route('usuarios.activar', $usuario));

        $response->assertRedirect();
        $usuario->refresh();
        $this->assertEquals(1, $usuario->estado);
    }

    /** @test */
    public function usuario_desactivado_no_puede_iniciar_sesion(): void
    {
        User::factory()->create([
            'login'    => 'usuario_bloqueado',
            'password' => 'password123',
            'estado'   => 0,
        ]);

        $response = $this->post(route('login.post'), [
            'login'    => 'usuario_bloqueado',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('desactivada', session('error'));
    }
}

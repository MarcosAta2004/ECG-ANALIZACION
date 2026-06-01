<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Tests de autenticación: login, logout y protección de rutas.
 */
class AuthTest extends TestCase
{
    /** @test */
    public function login_con_credenciales_correctas_redirige_al_dashboard(): void
    {
        $user = User::factory()->create([
            'login'    => 'tecnico01',
            'password' => 'password123',
            'estado'   => 1,
        ]);

        $response = $this->post(route('login.post'), [
            'login'    => 'tecnico01',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function login_con_password_incorrecto_devuelve_error(): void
    {
        User::factory()->create([
            'login'    => 'tecnico02',
            'password' => 'password123',
            'estado'   => 1,
        ]);

        $response = $this->post(route('login.post'), [
            'login'    => 'tecnico02',
            'password' => 'contraseña_erronea',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function login_con_usuario_desactivado_muestra_error_cuenta_desactivada(): void
    {
        User::factory()->create([
            'login'    => 'tecnico_inactivo',
            'password' => 'password123',
            'estado'   => 0,
        ]);

        $response = $this->post(route('login.post'), [
            'login'    => 'tecnico_inactivo',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString(
            'desactivada',
            session('error')
        );
    }

    /** @test */
    public function login_con_usuario_inexistente_devuelve_error(): void
    {
        $response = $this->post(route('login.post'), [
            'login'    => 'usuario_que_no_existe',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function logout_invalida_la_sesion_y_redirige_al_login(): void
    {
        $user = $this->loginComo('administrador');

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /** @test */
    public function acceso_al_dashboard_sin_sesion_redirige_al_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function login_valida_campo_login_requerido(): void
    {
        $response = $this->post(route('login.post'), [
            'login'    => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['login']);
    }

    /** @test */
    public function login_valida_campo_password_requerido(): void
    {
        $response = $this->post(route('login.post'), [
            'login'    => 'usuario',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\User;
use Tests\TestCase;

/**
 * Tests de feature para el sistema de Auditoría.
 * Verifica que las acciones críticas quedan registradas en la tabla auditorias.
 */
class AuditoriaTest extends TestCase
{
    /** @test */
    public function login_exitoso_registra_entrada_en_auditorias(): void
    {
        User::factory()->create([
            'login'    => 'auditado',
            'password' => 'password123',
            'estado'   => 1,
        ]);

        $this->post(route('login.post'), [
            'login'    => 'auditado',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('auditorias', [
            'accion' => 'login',
            'modulo' => 'Autenticacion',
        ]);
    }

    /** @test */
    public function logout_registra_entrada_en_auditorias(): void
    {
        $user = $this->loginComo('tecnico');

        $this->post(route('logout'));

        $this->assertDatabaseHas('auditorias', [
            'accion'     => 'logout',
            'modulo'     => 'Autenticacion',
            'usuario_id' => $user->id,
        ]);
    }

    /** @test */
    public function auditoria_no_guarda_passwords_en_texto_plano(): void
    {
        User::factory()->create([
            'login'    => 'auditar_pass',
            'password' => 'mi_contrasena_secreta',
            'estado'   => 1,
        ]);

        $this->post(route('login.post'), [
            'login'    => 'auditar_pass',
            'password' => 'mi_contrasena_secreta',
        ]);

        // Verificar que en ninguna fila de auditoría aparece el password en claro
        $auditorias = Auditoria::all();
        $this->assertNotEmpty($auditorias);

        $contenidoAuditoria = $auditorias
            ->map(fn (Auditoria $auditoria) => json_encode($auditoria->toArray()))
            ->implode("\n");

        $this->assertStringNotContainsString('mi_contrasena_secreta', $contenidoAuditoria);
    }

    /** @test */
    public function auditoria_index_accesible_solo_para_administrador(): void
    {
        $this->loginComo('administrador');
        $response = $this->get(route('auditoria.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function auditoria_index_no_accesible_para_tecnico(): void
    {
        $this->loginComo('tecnico');
        $response = $this->get(route('auditoria.index'));
        // Redirige al dashboard por falta de permisos
        $response->assertRedirect(route('dashboard'));
    }
}

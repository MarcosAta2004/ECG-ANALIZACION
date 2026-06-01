<?php

namespace Tests\Unit;

use App\Models\Auditoria;
use App\Models\User;
use App\Services\ServicioAuditoria;
use Tests\TestCase;

/**
 * Tests unitarios del ServicioAuditoria.
 * Verifica que el servicio registra correctamente y protege datos sensibles.
 */
class ServicioAuditoriaTest extends TestCase
{
    /** @test */
    public function registra_un_evento_en_la_tabla_auditorias(): void
    {
        $usuario = User::factory()->create();

        ServicioAuditoria::registrar(
            accion: 'test_accion',
            modulo: 'TestModulo',
            entidad: 'usuarios',
            entidadId: 42,
            descripcion: 'Prueba de registro',
            usuarioId: $usuario->id,
        );

        $this->assertDatabaseHas('auditorias', [
            'accion'      => 'test_accion',
            'modulo'      => 'TestModulo',
            'entidad'     => 'usuarios',
            'entidad_id'  => '42',
            'descripcion' => 'Prueba de registro',
        ]);
    }

    /** @test */
    public function no_lanza_excepcion_si_falla_el_registro(): void
    {
        // El servicio no debe bloquear la operación principal si falla
        $this->expectNotToPerformAssertions();

        // usuario_id nulo — el servicio captura la excepción internamente
        ServicioAuditoria::registrar(
            accion: 'accion_test',
            modulo: 'Modulo',
            usuarioId: null,
        );
    }

    /** @test */
    public function protege_el_campo_password_en_valores_nuevos(): void
    {
        $usuario = User::factory()->create();

        ServicioAuditoria::registrar(
            accion: 'crear',
            modulo: 'Usuarios',
            entidad: 'users',
            entidadId: $usuario->id,
            valoresNuevos: [
                'login'    => 'usuario_test',
                'password' => 'secreto123',
            ],
            usuarioId: $usuario->id,
        );

        $auditoria = Auditoria::where('accion', 'crear')
            ->where('modulo', 'Usuarios')
            ->first();

        $this->assertNotNull($auditoria);
        $this->assertEquals('[PROTEGIDO]', $auditoria->valores_nuevos['password']);
        $this->assertEquals('usuario_test', $auditoria->valores_nuevos['login']);
    }

    /** @test */
    public function protege_el_campo_password_en_valores_anteriores(): void
    {
        $usuario = User::factory()->create();

        ServicioAuditoria::registrar(
            accion: 'actualizar',
            modulo: 'Usuarios',
            entidad: 'users',
            entidadId: $usuario->id,
            valoresAnteriores: [
                'password'              => 'viejo_secreto',
                'password_confirmation' => 'viejo_secreto',
                'email'                 => 'test@ecg.com',
            ],
            usuarioId: $usuario->id,
        );

        $auditoria = Auditoria::where('accion', 'actualizar')
            ->where('modulo', 'Usuarios')
            ->first();

        $this->assertNotNull($auditoria);
        $this->assertEquals('[PROTEGIDO]', $auditoria->valores_anteriores['password']);
        $this->assertEquals('[PROTEGIDO]', $auditoria->valores_anteriores['password_confirmation']);
        $this->assertEquals('test@ecg.com', $auditoria->valores_anteriores['email']);
    }

    /** @test */
    public function permite_valores_nulos_sin_error(): void
    {
        $usuario = User::factory()->create();

        ServicioAuditoria::registrar(
            accion: 'logout',
            modulo: 'Autenticacion',
            usuarioId: $usuario->id,
        );

        $this->assertDatabaseHas('auditorias', [
            'accion' => 'logout',
            'modulo' => 'Autenticacion',
        ]);
    }
}

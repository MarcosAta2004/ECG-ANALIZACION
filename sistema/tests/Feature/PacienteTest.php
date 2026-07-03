<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\PrefijoPaciente;
use Tests\TestCase;

/**
 * Tests de feature para el módulo de Pacientes.
 */
class PacienteTest extends TestCase
{
    /** @test */
    public function index_requiere_autenticacion(): void
    {
        $response = $this->get(route('pacientes.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function index_accesible_para_usuario_autenticado(): void
    {
        $this->loginComo('tecnico');
        $response = $this->get(route('pacientes.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function store_crea_paciente_con_codigo_generado(): void
    {
        $user   = $this->loginComo('tecnico');
        $prefijo = PrefijoPaciente::find(1); // sembrado en TestDatabaseSeeder

        $response = $this->from(route('pacientes.index'))->post(route('pacientes.store'), [
            'prefijo_id'       => $prefijo->prefijo_id,
            'fecha_nacimiento' => '1990-05-15',
            'sexo'             => 'M',
            'peso'             => 75.5,
        ]);

        $response->assertRedirect(route('pacientes.index'));
        $response->assertSessionHas('ok', 'enabled');

        // El paciente debe existir en BD con el prefijo correcto
        $this->assertDatabaseHas('pacientes', [
            'prefijo_id' => $prefijo->prefijo_id,
            'sexo'       => 'M',
        ]);

        // El código generado debe tener el formato correcto
        $paciente = Paciente::latest('paciente_id')->first();
        $this->assertMatchesRegularExpression(
            '/^TEST_\d{8}_\d{3}$/',
            $paciente->codigo_generado
        );
    }

    /** @test */
    public function store_con_prefijo_invalido_devuelve_error_de_validacion(): void
    {
        $this->loginComo('tecnico');

        $response = $this->post(route('pacientes.store'), [
            'prefijo_id'       => 99999,
            'fecha_nacimiento' => '1990-05-15',
        ]);

        $response->assertSessionHasErrors(['prefijo_id']);
    }

    /** @test */
    public function store_con_peso_negativo_devuelve_error_de_validacion(): void
    {
        $this->loginComo('tecnico');
        $prefijo = PrefijoPaciente::find(1);

        $response = $this->post(route('pacientes.store'), [
            'prefijo_id' => $prefijo->prefijo_id,
            'peso'       => -5,
        ]);

        $response->assertSessionHasErrors(['peso']);
    }

    /** @test */
    public function store_con_sexo_invalido_devuelve_error_de_validacion(): void
    {
        $this->loginComo('tecnico');
        $prefijo = PrefijoPaciente::find(1);

        $response = $this->post(route('pacientes.store'), [
            'prefijo_id' => $prefijo->prefijo_id,
            'sexo'       => 'X', // Solo M o F son válidos
        ]);

        $response->assertSessionHasErrors(['sexo']);
    }

    /** @test */
    public function update_actualiza_datos_del_paciente(): void
    {
        $this->loginComo('tecnico');
        $paciente = Paciente::factory()->create(['estado' => 1]);

        $response = $this->put(route('pacientes.update', $paciente), [
            'fecha_nacimiento' => '1985-03-20',
            'sexo'             => 'F',
            'peso'             => 60.0,
        ]);

        $response->assertRedirect(route('pacientes.index'));
        $paciente->refresh();
        $this->assertEquals('F', $paciente->sexo);
        $this->assertEquals(60.0, $paciente->peso);
    }

    /** @test */
    public function destroy_desactiva_paciente_sin_borrarlo(): void
    {
        $this->loginComo('tecnico');
        $paciente = Paciente::factory()->create(['estado' => 1]);

        $response = $this->delete(route('pacientes.destroy', $paciente));

        $response->assertRedirect(route('pacientes.index'));
        $paciente->refresh();
        $this->assertEquals(0, $paciente->estado);
        $this->assertDatabaseHas('pacientes', ['paciente_id' => $paciente->paciente_id]);
    }

    /** @test */
    public function activar_reactiva_un_paciente_desactivado(): void
    {
        $this->loginComo('tecnico');
        $paciente = Paciente::factory()->inactivo()->create();

        $response = $this->put(route('pacientes.activar', $paciente));

        $response->assertRedirect(route('pacientes.index'));
        $paciente->refresh();
        $this->assertEquals(1, $paciente->estado);
    }
}

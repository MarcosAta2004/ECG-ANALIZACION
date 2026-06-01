<?php

namespace Tests\Feature;

use App\Models\Estudio;
use App\Models\Paciente;
use Tests\TestCase;

/**
 * Tests de feature para el módulo de Estudios.
 */
class EstudioTest extends TestCase
{
    /** @test */
    public function store_crea_estudio_para_paciente_existente(): void
    {
        $user    = $this->loginComo('tecnico');
        $paciente = Paciente::factory()->create(['estado' => 1]);

        $response = $this->post(route('estudios.store'), [
            'paciente_id'   => $paciente->paciente_id,
            'edad'          => 45,
            'observaciones' => 'Paciente con historial de hipertensión.',
        ]);

        $response->assertRedirect(route('estudios.index'));
        $response->assertSessionHas('ok', 'enabled');

        $this->assertDatabaseHas('estudios', [
            'paciente_id'   => $paciente->paciente_id,
            'edad'          => 45,
        ]);
    }

    /** @test */
    public function store_con_paciente_invalido_devuelve_error_de_validacion(): void
    {
        $this->loginComo('tecnico');

        $response = $this->post(route('estudios.store'), [
            'paciente_id' => 99999,
            'edad'        => 30,
        ]);

        $response->assertSessionHasErrors(['paciente_id']);
    }

    /** @test */
    public function store_con_edad_mayor_a_120_devuelve_error_de_validacion(): void
    {
        $this->loginComo('tecnico');
        $paciente = Paciente::factory()->create();

        $response = $this->post(route('estudios.store'), [
            'paciente_id' => $paciente->paciente_id,
            'edad'        => 150,
        ]);

        $response->assertSessionHasErrors(['edad']);
    }

    /** @test */
    public function store_sin_paciente_id_devuelve_error_requerido(): void
    {
        $this->loginComo('tecnico');

        $response = $this->post(route('estudios.store'), [
            'edad' => 30,
        ]);

        $response->assertSessionHasErrors(['paciente_id']);
    }

    /** @test */
    public function update_actualiza_edad_y_observaciones(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['edad' => 30, 'estado' => 1]);

        $response = $this->put(route('estudios.update', $estudio), [
            'edad'          => 35,
            'observaciones' => 'Observación actualizada.',
        ]);

        $response->assertRedirect(route('estudios.index'));
        $estudio->refresh();
        $this->assertEquals(35, $estudio->edad);
        $this->assertEquals('Observación actualizada.', $estudio->observaciones);
    }

    /** @test */
    public function destroy_desactiva_estudio_sin_borrarlo(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['estado' => 1]);

        $response = $this->delete(route('estudios.destroy', $estudio));

        $response->assertRedirect(route('estudios.index'));
        $estudio->refresh();
        $this->assertEquals(0, $estudio->estado);
        $this->assertDatabaseHas('estudios', ['estudio_id' => $estudio->estudio_id]);
    }

    /** @test */
    public function activar_reactiva_un_estudio_desactivado(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->inactivo()->create();

        $response = $this->put(route('estudios.activar', $estudio));

        $response->assertRedirect(route('estudios.index'));
        $estudio->refresh();
        $this->assertEquals(1, $estudio->estado);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Diagnostico;
use App\Models\Estudio;
use App\Models\Imagen;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use Tests\TestCase;

/**
 * Tests de feature para el módulo de Diagnósticos.
 */
class DiagnosticoTest extends TestCase
{
    /** @test */
    public function store_registra_diagnostico_medico_correctamente(): void
    {
        $medico  = $this->loginComo('cardiologo');
        $estudio = Estudio::factory()->create(['estado' => 1]);
        $ritmo   = RitmoCardiaco::find(1); // NORM

        $response = $this->post(route('diagnosticos.store'), [
            'estudio_id'   => $estudio->estudio_id,
            'ritmo_id'     => $ritmo->ritmo_id,
            'concordancia' => 1,
            'descripcion'  => 'Diagnóstico de prueba.',
            'observacion'  => 'Sin observaciones adicionales.',
        ]);

        $response->assertRedirect(route('diagnosticos.index'));
        $response->assertSessionHas('ok', 'enabled');

        $this->assertDatabaseHas('diagnosticos', [
            'estudio_id' => $estudio->estudio_id,
            'ritmo_id'   => $ritmo->ritmo_id,
            'medico_id'  => $medico->id,
        ]);
    }

    /** @test */
    public function store_no_permite_diagnostico_duplicado_en_mismo_estudio(): void
    {
        $this->loginComo('cardiologo');
        $estudio = Estudio::factory()->create(['estado' => 1]);
        $ritmo   = RitmoCardiaco::find(1);

        // Crear diagnóstico inicial
        Diagnostico::factory()->create([
            'estudio_id' => $estudio->estudio_id,
            'ritmo_id'   => $ritmo->ritmo_id,
        ]);

        // Intentar crear uno segundo para el mismo estudio
        $response = $this->post(route('diagnosticos.store'), [
            'estudio_id' => $estudio->estudio_id,
            'ritmo_id'   => $ritmo->ritmo_id,
        ]);

        $response->assertSessionHasErrors(['estudio_id']);
    }

    /** @test */
    public function store_con_ritmo_invalido_devuelve_error_de_validacion(): void
    {
        $this->loginComo('cardiologo');
        $estudio = Estudio::factory()->create();

        $response = $this->post(route('diagnosticos.store'), [
            'estudio_id' => $estudio->estudio_id,
            'ritmo_id'   => 99999,
        ]);

        $response->assertSessionHasErrors(['ritmo_id']);
    }

    /** @test */
    public function update_actualiza_diagnostico_existente(): void
    {
        $this->loginComo('cardiologo');
        $diagnostico = Diagnostico::factory()->create([
            'concordancia' => false,
            'observacion'  => 'Observación original.',
        ]);
        $nuevoRitmo = RitmoCardiaco::find(2); // 1AVB

        $response = $this->put(route('diagnosticos.update', $diagnostico), [
            'ritmo_id'     => $nuevoRitmo->ritmo_id,
            'concordancia' => 1,
            'descripcion'  => 'Descripción actualizada.',
            'observacion'  => 'Observación actualizada.',
        ]);

        $response->assertRedirect(route('diagnosticos.index'));
        $diagnostico->refresh();
        $this->assertEquals($nuevoRitmo->ritmo_id, $diagnostico->ritmo_id);
        $this->assertEquals('Observación actualizada.', $diagnostico->observacion);
    }

    /** @test */
    public function destroy_desactiva_diagnostico_sin_borrarlo(): void
    {
        $this->loginComo('cardiologo');
        $diagnostico = Diagnostico::factory()->create(['estado' => 1]);

        $response = $this->delete(route('diagnosticos.destroy', $diagnostico));

        $response->assertRedirect(route('diagnosticos.index'));
        $diagnostico->refresh();
        $this->assertEquals(0, $diagnostico->estado);
    }

    /** @test */
    public function review_guarda_valoracion_medica_via_ajax(): void
    {
        $this->loginComo('cardiologo');

        $imagen     = Imagen::factory()->create();
        $ritmoNorm  = RitmoCardiaco::where('label', 'NORM')->first();
        Prediccion::factory()->create([
            'imagen_id' => $imagen->imagen_id,
            'ritmo_id'  => $ritmoNorm->ritmo_id,
        ]);

        $response = $this->postJson(
            route('diagnosticos.review', $imagen),
            [
                'doctor_result'   => 'normal',
                'doctor_ritmo_id' => $ritmoNorm->ritmo_id,
                'doctor_notes'    => 'Ritmo normal confirmado.',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('diagnosticos', [
            'estudio_id' => $imagen->estudio_id,
            'ritmo_id'   => $ritmoNorm->ritmo_id,
        ]);
    }

    /** @test */
    public function delete_review_elimina_valoracion_medica(): void
    {
        $this->loginComo('cardiologo');

        $imagen = Imagen::factory()->create();
        $ritmo  = RitmoCardiaco::find(1);

        Prediccion::factory()->create([
            'imagen_id' => $imagen->imagen_id,
            'ritmo_id'  => $ritmo->ritmo_id,
        ]);

        Diagnostico::factory()->create([
            'estudio_id' => $imagen->estudio_id,
            'ritmo_id'   => $ritmo->ritmo_id,
        ]);

        $response = $this->deleteJson(route('diagnosticos.deleteReview', $imagen));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('diagnosticos', [
            'estudio_id' => $imagen->estudio_id,
        ]);
    }
}

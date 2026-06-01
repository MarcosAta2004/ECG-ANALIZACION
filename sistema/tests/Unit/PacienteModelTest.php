<?php

namespace Tests\Unit;

use App\Models\Estudio;
use App\Models\Paciente;
use App\Models\PrefijoPaciente;
use App\Models\User;
use Tests\TestCase;

/**
 * Tests unitarios del modelo Paciente.
 * Verifica relaciones Eloquent, casts y configuración del modelo.
 */
class PacienteModelTest extends TestCase
{
    /** @test */
    public function tiene_la_tabla_correcta(): void
    {
        $paciente = new Paciente();
        $this->assertEquals('pacientes', $paciente->getTable());
    }

    /** @test */
    public function tiene_primary_key_correcta(): void
    {
        $paciente = new Paciente();
        $this->assertEquals('paciente_id', $paciente->getKeyName());
    }

    /** @test */
    public function cast_de_fecha_nacimiento_es_date(): void
    {
        $casts = (new Paciente())->getCasts();
        $this->assertArrayHasKey('fecha_nacimiento', $casts);
        $this->assertStringContainsString('date', $casts['fecha_nacimiento']);
    }

    /** @test */
    public function cast_de_peso_es_float(): void
    {
        $casts = (new Paciente())->getCasts();
        $this->assertArrayHasKey('peso', $casts);
        $this->assertEquals('float', $casts['peso']);
    }

    /** @test */
    public function tiene_relacion_prefijo_paciente(): void
    {
        $paciente = new Paciente();
        $relation = $paciente->prefijoPaciente();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(PrefijoPaciente::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_registrado_por(): void
    {
        $paciente = new Paciente();
        $relation = $paciente->registradoPor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_estudios(): void
    {
        $paciente = new Paciente();
        $relation = $paciente->estudios();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertInstanceOf(Estudio::class, $relation->getRelated());
    }

    /** @test */
    public function puede_crear_paciente_con_factory(): void
    {
        $paciente = Paciente::factory()->create();
        $this->assertDatabaseHas('pacientes', ['paciente_id' => $paciente->paciente_id]);
    }

    /** @test */
    public function factory_inactivo_crea_paciente_con_estado_cero(): void
    {
        $paciente = Paciente::factory()->inactivo()->create();
        $this->assertEquals(0, $paciente->estado);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Diagnostico;
use App\Models\Estudio;
use App\Models\Imagen;
use App\Models\Paciente;
use App\Models\Reporte;
use App\Models\User;
use Tests\TestCase;

/**
 * Tests unitarios del modelo Estudio.
 */
class EstudioModelTest extends TestCase
{
    /** @test */
    public function tiene_la_tabla_correcta(): void
    {
        $this->assertEquals('estudios', (new Estudio())->getTable());
    }

    /** @test */
    public function tiene_primary_key_correcta(): void
    {
        $this->assertEquals('estudio_id', (new Estudio())->getKeyName());
    }

    /** @test */
    public function tiene_relacion_paciente(): void
    {
        $relation = (new Estudio())->paciente();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(Paciente::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_registrado_por(): void
    {
        $relation = (new Estudio())->registradoPor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_imagen(): void
    {
        $relation = (new Estudio())->imagen();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertInstanceOf(Imagen::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_diagnostico(): void
    {
        $relation = (new Estudio())->diagnostico();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertInstanceOf(Diagnostico::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_reporte(): void
    {
        $relation = (new Estudio())->reporte();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertInstanceOf(Reporte::class, $relation->getRelated());
    }

    /** @test */
    public function puede_crear_estudio_con_factory(): void
    {
        $estudio = Estudio::factory()->create();
        $this->assertDatabaseHas('estudios', ['estudio_id' => $estudio->estudio_id]);
    }
}

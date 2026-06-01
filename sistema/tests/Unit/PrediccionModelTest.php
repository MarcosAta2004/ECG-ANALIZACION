<?php

namespace Tests\Unit;

use App\Models\Imagen;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use Tests\TestCase;

/**
 * Tests unitarios del modelo Prediccion.
 */
class PrediccionModelTest extends TestCase
{
    /** @test */
    public function tiene_la_tabla_correcta(): void
    {
        $this->assertEquals('predicciones', (new Prediccion())->getTable());
    }

    /** @test */
    public function tiene_primary_key_correcta(): void
    {
        $this->assertEquals('prediccion_id', (new Prediccion())->getKeyName());
    }

    /** @test */
    public function cast_de_top_predicciones_es_array(): void
    {
        $casts = (new Prediccion())->getCasts();
        $this->assertArrayHasKey('top_predicciones', $casts);
        $this->assertEquals('array', $casts['top_predicciones']);
    }

    /** @test */
    public function tiene_relacion_imagen(): void
    {
        $relation = (new Prediccion())->imagen();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(Imagen::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_ritmo(): void
    {
        $relation = (new Prediccion())->ritmo();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(RitmoCardiaco::class, $relation->getRelated());
    }

    /** @test */
    public function tiene_relacion_ritmo_cardiaco(): void
    {
        $relation = (new Prediccion())->ritmoCardiaco();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(RitmoCardiaco::class, $relation->getRelated());
    }

    /** @test */
    public function top_predicciones_se_guarda_y_recupera_como_array(): void
    {
        $topData = [
            ['label' => 'NORM', 'code' => 'NORM', 'probability' => 95.0],
            ['label' => 'AFIB', 'code' => 'AFIB', 'probability' => 3.0],
        ];

        $prediccion = Prediccion::factory()->create([
            'top_predicciones' => $topData,
        ]);

        $prediccion->refresh();

        $this->assertIsArray($prediccion->top_predicciones);
        $this->assertCount(2, $prediccion->top_predicciones);
        $this->assertEquals('NORM', $prediccion->top_predicciones[0]['code']);
    }

    /** @test */
    public function probabilidad_esta_entre_cero_y_uno(): void
    {
        $prediccion = Prediccion::factory()->create(['probabilidad' => 0.87]);
        $this->assertGreaterThanOrEqual(0.0, $prediccion->probabilidad);
        $this->assertLessThanOrEqual(1.0, $prediccion->probabilidad);
    }
}

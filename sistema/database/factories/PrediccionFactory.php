<?php

namespace Database\Factories;

use App\Models\Imagen;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prediccion>
 */
class PrediccionFactory extends Factory
{
    protected $model = Prediccion::class;

    public function definition(): array
    {
        return [
            'imagen_id'       => Imagen::factory(),
            'ritmo_id'        => RitmoCardiaco::factory(),
            'probabilidad'    => $this->faker->randomFloat(4, 0.5, 0.99),
            'tiempo_ms'       => $this->faker->numberBetween(500, 8000),
            'top_predicciones' => [
                ['label' => 'NORM', 'code' => 'NORM', 'probability' => 85.0],
                ['label' => 'AFIB', 'code' => 'AFIB', 'probability' => 5.0],
                ['label' => 'STACH', 'code' => 'STACH', 'probability' => 4.0],
                ['label' => 'PVC', 'code' => 'PVC', 'probability' => 3.5],
                ['label' => 'PAC', 'code' => 'PAC', 'probability' => 2.5],
            ],
            'estado' => 1,
        ];
    }

    /** Predicción de ritmo normal */
    public function normal(): static
    {
        return $this->state(fn () => [
            'probabilidad' => 0.95,
        ]);
    }

    /** Estado desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}

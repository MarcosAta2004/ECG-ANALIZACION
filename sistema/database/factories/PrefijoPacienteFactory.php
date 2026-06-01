<?php

namespace Database\Factories;

use App\Models\PrefijoPaciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrefijoPaciente>
 */
class PrefijoPacienteFactory extends Factory
{
    protected $model = PrefijoPaciente::class;

    public function definition(): array
    {
        return [
            'nombre'      => strtoupper($this->faker->unique()->lexify('???')),
            'descripcion' => $this->faker->sentence(),
            'estado'      => 1,
        ];
    }

    /** Estado desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}

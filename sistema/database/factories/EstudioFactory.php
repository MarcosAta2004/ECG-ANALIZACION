<?php

namespace Database\Factories;

use App\Models\Estudio;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estudio>
 */
class EstudioFactory extends Factory
{
    protected $model = Estudio::class;

    public function definition(): array
    {
        return [
            'paciente_id'    => Paciente::factory(),
            'registrado_por' => null,
            'edad'           => $this->faker->numberBetween(1, 100),
            'observaciones'  => $this->faker->optional()->sentence(),
            'estado'         => 1,
        ];
    }

    /** Asignar usuario registrador */
    public function registradoPor(User $user): static
    {
        return $this->state(['registrado_por' => $user->id]);
    }

    /** Estado desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}

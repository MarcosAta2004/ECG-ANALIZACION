<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\PrefijoPaciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        static $contador = 1;
        $anio = now()->year;

        return [
            'prefijo_id'       => PrefijoPaciente::factory(),
            'codigo_generado'  => 'TEST-' . $anio . '-' . str_pad($contador++, 3, '0', STR_PAD_LEFT),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'sexo'             => $this->faker->randomElement(['M', 'F']),
            'peso'             => $this->faker->randomFloat(1, 40, 150),
            'registrado_por'   => null,
            'estado'           => 1,
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

<?php

namespace Database\Factories;

use App\Models\Diagnostico;
use App\Models\Estudio;
use App\Models\RitmoCardiaco;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diagnostico>
 */
class DiagnosticoFactory extends Factory
{
    protected $model = Diagnostico::class;

    public function definition(): array
    {
        return [
            'estudio_id'     => Estudio::factory(),
            'ritmo_id'       => RitmoCardiaco::factory(),
            'medico_id'      => User::factory()->create([
                'login'  => 'medico_' . uniqid(),
                'email'  => 'medico_' . uniqid() . '@ecg.test',
                'estado' => 1,
            ])->id,
            'concordancia'   => $this->faker->boolean(),
            'descripcion'    => $this->faker->sentence(5),
            'observacion'    => $this->faker->optional()->paragraph(),
            'fecha_revision' => now(),
            'estado'         => 1,
        ];
    }

    /** Diagnóstico con concordancia confirmada */
    public function concordante(): static
    {
        return $this->state(['concordancia' => true]);
    }

    /** Diagnóstico sin concordancia (discrepa con IA) */
    public function discordante(): static
    {
        return $this->state(['concordancia' => false]);
    }

    /** Estado desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}

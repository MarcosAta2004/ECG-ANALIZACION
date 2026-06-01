<?php

namespace Database\Factories;

use App\Models\Estudio;
use App\Models\Imagen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Imagen>
 */
class ImagenFactory extends Factory
{
    protected $model = Imagen::class;

    public function definition(): array
    {
        $fecha = now()->format('Ymd-His');

        return [
            'estudio_id'   => Estudio::factory(),
            'ruta'         => 'ecg/' . $fecha . '.png',
            'formato'      => 'png',
            'resolucion'   => '1200x800',
            'tamano_kb'    => $this->faker->numberBetween(50, 5000),
            'estado'       => 1,
        ];
    }

    /** Imagen en formato PDF */
    public function pdf(): static
    {
        return $this->state(fn () => [
            'ruta'       => 'ecg/' . now()->format('Ymd-His') . '.pdf',
            'formato'    => 'pdf',
            'resolucion' => null,
        ]);
    }

    /** Estado desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}

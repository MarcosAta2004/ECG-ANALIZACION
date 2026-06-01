<?php

namespace Database\Factories;

use App\Models\RitmoCardiaco;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<RitmoCardiaco>
 */
class RitmoCardiacoFactory extends Factory
{
    protected $model = RitmoCardiaco::class;

    public function definition(): array
    {
        // Obtener IDs válidos de las tablas de catálogo que ya sembró el TestDatabaseSeeder
        $grupoId          = DB::table('grupos_cardiacos')->value('grupo_id') ?? 1;
        $nivelId          = DB::table('niveles_gravedad')->value('nivel_id') ?? 1;
        $clasificacionId  = DB::table('clasificaciones_arritmia')->value('clasificacion_id') ?? 1;

        return [
            'grupo_id'          => $grupoId,
            'nivel_id'          => $nivelId,
            'clasificacion_id'  => $clasificacionId,
            'label'             => strtoupper($this->faker->unique()->lexify('???')),
            'nombre'            => $this->faker->words(3, true),
            'descripcion'       => $this->faker->sentence(),
            'estado'            => 1,
        ];
    }

    /** Crear ritmo con label NORM (ritmo normal) */
    public function normal(): static
    {
        return $this->state(['label' => 'NORM', 'nombre' => 'Ritmo Sinusal Normal']);
    }

    /** Estado desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}

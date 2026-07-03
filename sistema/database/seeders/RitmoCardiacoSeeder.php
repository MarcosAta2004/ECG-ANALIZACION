<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RitmoCardiacoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'ritmo_id'         => 1,
                'grupo_id'         => 1,
                'nivel_id'         => 1,
                'clasificacion_id' => 1,
                'label'            => 'NORM',
                'nombre'           => 'Ritmo Sinusal Normal',
                'descripcion'      => 'ECG dentro de limites normales.',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
            [
                'ritmo_id'         => 2,
                'grupo_id'         => 2,
                'nivel_id'         => 2,
                'clasificacion_id' => 2,
                'label'            => '1AVB',
                'nombre'           => 'Bloqueo AV de primer grado',
                'descripcion'      => 'Retraso de conduccion auriculoventricular.',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
            [
                'ritmo_id'         => 4,
                'grupo_id'         => 3,
                'nivel_id'         => 2,
                'clasificacion_id' => 2,
                'label'            => 'PVC',
                'nombre'           => 'Complejo ventricular prematuro',
                'descripcion'      => 'Latido ventricular ectopico prematuro.',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
            [
                'ritmo_id'         => 6,
                'grupo_id'         => 4,
                'nivel_id'         => 3,
                'clasificacion_id' => 2,
                'label'            => 'AFIB',
                'nombre'           => 'Fibrilacion Auricular',
                'descripcion'      => 'Ritmo auricular irregular compatible con fibrilacion.',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
            [
                'ritmo_id'         => 7,
                'grupo_id'         => 1,
                'nivel_id'         => 2,
                'clasificacion_id' => 2,
                'label'            => 'STACH',
                'nombre'           => 'Taquicardia Sinusal',
                'descripcion'      => 'Frecuencia sinusal elevada.',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
            [
                'ritmo_id'         => 9,
                'grupo_id'         => 1,
                'nivel_id'         => 1,
                'clasificacion_id' => 2,
                'label'            => 'SBRAD',
                'nombre'           => 'Bradicardia Sinusal',
                'descripcion'      => 'Frecuencia sinusal disminuida.',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
        ];

        if (DB::table('ritmos_cardiacos')->count() == 0) {
            DB::table('ritmos_cardiacos')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('ritmos_cardiacos', 'ritmo_id'), COALESCE(MAX(ritmo_id), 1)) FROM ritmos_cardiacos");
        }
    }
}

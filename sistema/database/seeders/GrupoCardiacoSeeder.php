<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoCardiacoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'grupo_id'    => 1,
                'nombre'      => 'Sinusal',
                'descripcion' => 'Ritmos de origen sinusal',
                'estado'      => 1,
                'created_at'  => '2026-05-04 15:46:27',
                'updated_at'  => '2026-05-04 15:46:27',
            ],
            [
                'grupo_id'    => 2,
                'nombre'      => 'Conduccion',
                'descripcion' => 'Alteraciones de conduccion cardiaca',
                'estado'      => 1,
                'created_at'  => '2026-05-04 15:46:27',
                'updated_at'  => '2026-05-04 15:46:27',
            ],
            [
                'grupo_id'    => 3,
                'nombre'      => 'Ectopias',
                'descripcion' => 'Complejos prematuros o patrones ectopicos',
                'estado'      => 1,
                'created_at'  => '2026-05-04 15:46:27',
                'updated_at'  => '2026-05-04 15:46:27',
            ],
            [
                'grupo_id'    => 4,
                'nombre'      => 'Supraventricular',
                'descripcion' => 'Arritmias de origen supraventricular',
                'estado'      => 1,
                'created_at'  => '2026-05-04 15:46:27',
                'updated_at'  => '2026-05-04 15:46:27',
            ],
        ];

        if (DB::table('grupos_cardiacos')->count() == 0) {
            DB::table('grupos_cardiacos')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('grupos_cardiacos', 'grupo_id'), COALESCE(MAX(grupo_id), 1)) FROM grupos_cardiacos");
        }
    }
}

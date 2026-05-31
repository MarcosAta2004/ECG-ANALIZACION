<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClasificacionArritmiaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'clasificacion_id' => 1,
                'nombre'           => 'Normal',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
            [
                'clasificacion_id' => 2,
                'nombre'           => 'Arritmia',
                'estado'           => 1,
                'created_at'       => '2026-05-04 15:46:27',
                'updated_at'       => '2026-05-04 15:46:27',
            ],
        ];

        if (DB::table('clasificaciones_arritmia')->count() == 0) {
            DB::table('clasificaciones_arritmia')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('clasificaciones_arritmia', 'clasificacion_id'), COALESCE(MAX(clasificacion_id), 1)) FROM clasificaciones_arritmia");
        }
    }
}

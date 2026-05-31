<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelGravedadSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nivel_id'   => 1,
                'nombre'     => 'Baja',
                'estado'     => 1,
                'created_at' => '2026-05-04 15:46:27',
                'updated_at' => '2026-05-04 15:46:27',
            ],
            [
                'nivel_id'   => 2,
                'nombre'     => 'Moderada',
                'estado'     => 1,
                'created_at' => '2026-05-04 15:46:27',
                'updated_at' => '2026-05-04 15:46:27',
            ],
            [
                'nivel_id'   => 3,
                'nombre'     => 'Alta',
                'estado'     => 1,
                'created_at' => '2026-05-04 15:46:27',
                'updated_at' => '2026-05-04 15:46:27',
            ],
        ];

        if (DB::table('niveles_gravedad')->count() == 0) {
            DB::table('niveles_gravedad')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('niveles_gravedad', 'nivel_id'), COALESCE(MAX(nivel_id), 1)) FROM niveles_gravedad");
        }
    }
}

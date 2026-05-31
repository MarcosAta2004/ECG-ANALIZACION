<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrefijoPacienteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'prefijo_id'  => 1,
                'nombre'      => 'PACIENTE',
                'descripcion' => 'Codigo generado por el sistema',
                'estado'      => 1,
                'created_at'  => '2026-05-04 15:46:27',
                'updated_at'  => '2026-05-04 15:46:27',
            ],
        ];

        if (DB::table('prefijos_paciente')->count() == 0) {
            DB::table('prefijos_paciente')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('prefijos_paciente', 'prefijo_id'), COALESCE(MAX(prefijo_id), 1)) FROM prefijos_paciente");
        }
    }
}

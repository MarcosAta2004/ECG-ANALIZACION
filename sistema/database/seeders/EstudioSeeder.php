<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstudioSeeder extends Seeder
{
    public function run(): void
    {
        $estudios = [
            ['estudio_id' => 1,  'paciente_id' => 1,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260406-834219.pdf', 'estado' => 1, 'created_at' => '2026-04-06 08:10:00', 'updated_at' => '2026-04-06 08:10:00'],
            ['estudio_id' => 2,  'paciente_id' => 2,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260406-157604.pdf', 'estado' => 1, 'created_at' => '2026-04-06 09:25:00', 'updated_at' => '2026-04-06 09:25:00'],
            ['estudio_id' => 3,  'paciente_id' => 3,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260406-692381.pdf', 'estado' => 1, 'created_at' => '2026-04-06 10:40:00', 'updated_at' => '2026-04-06 10:40:00'],
            ['estudio_id' => 4,  'paciente_id' => 4,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260407-420918.pdf', 'estado' => 1, 'created_at' => '2026-04-07 08:05:00', 'updated_at' => '2026-04-07 08:05:00'],
            ['estudio_id' => 5,  'paciente_id' => 5,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260407-783052.pdf', 'estado' => 1, 'created_at' => '2026-04-07 09:20:00', 'updated_at' => '2026-04-07 09:20:00'],
            ['estudio_id' => 6,  'paciente_id' => 6,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260407-269447.pdf', 'estado' => 1, 'created_at' => '2026-04-07 10:35:00', 'updated_at' => '2026-04-07 10:35:00'],
            ['estudio_id' => 7,  'paciente_id' => 7,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260407-915630.pdf', 'estado' => 1, 'created_at' => '2026-04-07 11:50:00', 'updated_at' => '2026-04-07 11:50:00'],
            ['estudio_id' => 8,  'paciente_id' => 8,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260408-508274.pdf', 'estado' => 1, 'created_at' => '2026-04-08 08:15:00', 'updated_at' => '2026-04-08 08:15:00'],
            ['estudio_id' => 9,  'paciente_id' => 9,  'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260408-731965.pdf', 'estado' => 1, 'created_at' => '2026-04-08 09:30:00', 'updated_at' => '2026-04-08 09:30:00'],
            ['estudio_id' => 10, 'paciente_id' => 10, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260409-184603.pdf', 'estado' => 1, 'created_at' => '2026-04-09 08:00:00', 'updated_at' => '2026-04-09 08:00:00'],
            ['estudio_id' => 11, 'paciente_id' => 11, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260409-647290.pdf', 'estado' => 1, 'created_at' => '2026-04-09 09:15:00', 'updated_at' => '2026-04-09 09:15:00'],
            ['estudio_id' => 12, 'paciente_id' => 12, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260409-392815.pdf', 'estado' => 1, 'created_at' => '2026-04-09 10:30:00', 'updated_at' => '2026-04-09 10:30:00'],
            ['estudio_id' => 13, 'paciente_id' => 13, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260410-859104.pdf', 'estado' => 1, 'created_at' => '2026-04-10 08:20:00', 'updated_at' => '2026-04-10 08:20:00'],
            ['estudio_id' => 14, 'paciente_id' => 14, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260410-206738.pdf', 'estado' => 1, 'created_at' => '2026-04-10 09:35:00', 'updated_at' => '2026-04-10 09:35:00'],
            ['estudio_id' => 15, 'paciente_id' => 15, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260410-574921.pdf', 'estado' => 1, 'created_at' => '2026-04-10 10:50:00', 'updated_at' => '2026-04-10 10:50:00'],
            ['estudio_id' => 16, 'paciente_id' => 16, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260413-720381.pdf', 'estado' => 1, 'created_at' => '2026-04-13 08:10:00', 'updated_at' => '2026-04-13 08:10:00'],
            ['estudio_id' => 17, 'paciente_id' => 17, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260413-262026.pdf', 'estado' => 1, 'created_at' => '2026-04-13 09:25:00', 'updated_at' => '2026-04-13 09:25:00'],
            ['estudio_id' => 18, 'paciente_id' => 18, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260414-296552.pdf', 'estado' => 1, 'created_at' => '2026-04-14 08:05:00', 'updated_at' => '2026-04-14 08:05:00'],
            ['estudio_id' => 19, 'paciente_id' => 19, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260414-528797.pdf', 'estado' => 1, 'created_at' => '2026-04-14 09:20:00', 'updated_at' => '2026-04-14 09:20:00'],
            ['estudio_id' => 20, 'paciente_id' => 20, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260414-841306.pdf', 'estado' => 1, 'created_at' => '2026-04-14 10:35:00', 'updated_at' => '2026-04-14 10:35:00'],
            ['estudio_id' => 21, 'paciente_id' => 21, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260414-973510.pdf', 'estado' => 1, 'created_at' => '2026-04-14 11:50:00', 'updated_at' => '2026-04-14 11:50:00'],
            ['estudio_id' => 22, 'paciente_id' => 22, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260415-239487.pdf', 'estado' => 1, 'created_at' => '2026-04-15 08:15:00', 'updated_at' => '2026-04-15 08:15:00'],
            ['estudio_id' => 23, 'paciente_id' => 23, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260415-973710.pdf', 'estado' => 1, 'created_at' => '2026-04-15 09:30:00', 'updated_at' => '2026-04-15 09:30:00'],
            ['estudio_id' => 24, 'paciente_id' => 24, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260415-675813.pdf', 'estado' => 1, 'created_at' => '2026-04-15 10:45:00', 'updated_at' => '2026-04-15 10:45:00'],
            ['estudio_id' => 25, 'paciente_id' => 25, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260416-498501.pdf', 'estado' => 1, 'created_at' => '2026-04-16 08:00:00', 'updated_at' => '2026-04-16 08:00:00'],
            ['estudio_id' => 26, 'paciente_id' => 26, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260416-149807.pdf', 'estado' => 1, 'created_at' => '2026-04-16 09:15:00', 'updated_at' => '2026-04-16 09:15:00'],
            ['estudio_id' => 27, 'paciente_id' => 27, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260416-595321.pdf', 'estado' => 1, 'created_at' => '2026-04-16 10:30:00', 'updated_at' => '2026-04-16 10:30:00'],
            ['estudio_id' => 28, 'paciente_id' => 28, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260417-288592.pdf', 'estado' => 1, 'created_at' => '2026-04-17 08:20:00', 'updated_at' => '2026-04-17 08:20:00'],
            ['estudio_id' => 29, 'paciente_id' => 29, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260417-957298.pdf', 'estado' => 1, 'created_at' => '2026-04-17 09:35:00', 'updated_at' => '2026-04-17 09:35:00'],
            ['estudio_id' => 30, 'paciente_id' => 30, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260417-180834.pdf', 'estado' => 1, 'created_at' => '2026-04-17 10:50:00', 'updated_at' => '2026-04-17 10:50:00'],
            ['estudio_id' => 31, 'paciente_id' => 31, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260420-620001.pdf', 'estado' => 1, 'created_at' => '2026-04-20 08:05:00', 'updated_at' => '2026-04-20 08:05:00'],
            ['estudio_id' => 32, 'paciente_id' => 32, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260420-620002.pdf', 'estado' => 1, 'created_at' => '2026-04-20 09:20:00', 'updated_at' => '2026-04-20 09:20:00'],
            ['estudio_id' => 33, 'paciente_id' => 33, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260420-620003.pdf', 'estado' => 1, 'created_at' => '2026-04-20 10:35:00', 'updated_at' => '2026-04-20 10:35:00'],
            ['estudio_id' => 34, 'paciente_id' => 34, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260420-620004.pdf', 'estado' => 1, 'created_at' => '2026-04-20 11:50:00', 'updated_at' => '2026-04-20 11:50:00'],
            ['estudio_id' => 35, 'paciente_id' => 35, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260421-621381.pdf', 'estado' => 1, 'created_at' => '2026-04-21 08:15:00', 'updated_at' => '2026-04-21 08:15:00'],
            ['estudio_id' => 36, 'paciente_id' => 36, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260421-846027.pdf', 'estado' => 1, 'created_at' => '2026-04-21 09:30:00', 'updated_at' => '2026-04-21 09:30:00'],
            ['estudio_id' => 37, 'paciente_id' => 37, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260422-622731.pdf', 'estado' => 1, 'created_at' => '2026-04-22 08:00:00', 'updated_at' => '2026-04-22 08:00:00'],
            ['estudio_id' => 38, 'paciente_id' => 38, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260422-194608.pdf', 'estado' => 1, 'created_at' => '2026-04-22 09:15:00', 'updated_at' => '2026-04-22 09:15:00'],
            ['estudio_id' => 39, 'paciente_id' => 39, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260422-753042.pdf', 'estado' => 1, 'created_at' => '2026-04-22 10:30:00', 'updated_at' => '2026-04-22 10:30:00'],
            ['estudio_id' => 40, 'paciente_id' => 40, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260423-623915.pdf', 'estado' => 1, 'created_at' => '2026-04-23 08:20:00', 'updated_at' => '2026-04-23 08:20:00'],
            ['estudio_id' => 41, 'paciente_id' => 41, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260423-407286.pdf', 'estado' => 1, 'created_at' => '2026-04-23 09:35:00', 'updated_at' => '2026-04-23 09:35:00'],
            ['estudio_id' => 42, 'paciente_id' => 42, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260423-865134.pdf', 'estado' => 1, 'created_at' => '2026-04-23 10:50:00', 'updated_at' => '2026-04-23 10:50:00'],
            ['estudio_id' => 43, 'paciente_id' => 43, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260424-624508.pdf', 'estado' => 1, 'created_at' => '2026-04-24 08:05:00', 'updated_at' => '2026-04-24 08:05:00'],
            ['estudio_id' => 44, 'paciente_id' => 44, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260424-218763.pdf', 'estado' => 1, 'created_at' => '2026-04-24 09:20:00', 'updated_at' => '2026-04-24 09:20:00'],
            ['estudio_id' => 45, 'paciente_id' => 45, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260424-790341.pdf', 'estado' => 1, 'created_at' => '2026-04-24 10:35:00', 'updated_at' => '2026-04-24 10:35:00'],
            ['estudio_id' => 46, 'paciente_id' => 46, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260427-627194.pdf', 'estado' => 1, 'created_at' => '2026-04-27 08:15:00', 'updated_at' => '2026-04-27 08:15:00'],
            ['estudio_id' => 47, 'paciente_id' => 47, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260427-482650.pdf', 'estado' => 1, 'created_at' => '2026-04-27 09:30:00', 'updated_at' => '2026-04-27 09:30:00'],
            ['estudio_id' => 48, 'paciente_id' => 48, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260427-936815.pdf', 'estado' => 1, 'created_at' => '2026-04-27 10:45:00', 'updated_at' => '2026-04-27 10:45:00'],
            ['estudio_id' => 49, 'paciente_id' => 49, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260427-305742.pdf', 'estado' => 1, 'created_at' => '2026-04-27 12:00:00', 'updated_at' => '2026-04-27 12:00:00'],
            ['estudio_id' => 50, 'paciente_id' => 50, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260428-628409.pdf', 'estado' => 1, 'created_at' => '2026-04-28 08:00:00', 'updated_at' => '2026-04-28 08:00:00'],
            ['estudio_id' => 51, 'paciente_id' => 51, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260428-174936.pdf', 'estado' => 1, 'created_at' => '2026-04-28 09:15:00', 'updated_at' => '2026-04-28 09:15:00'],
            ['estudio_id' => 52, 'paciente_id' => 52, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260428-852617.pdf', 'estado' => 1, 'created_at' => '2026-04-28 10:30:00', 'updated_at' => '2026-04-28 10:30:00'],
            ['estudio_id' => 53, 'paciente_id' => 53, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260429-629284.pdf', 'estado' => 1, 'created_at' => '2026-04-29 08:20:00', 'updated_at' => '2026-04-29 08:20:00'],
            ['estudio_id' => 54, 'paciente_id' => 54, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260429-416950.pdf', 'estado' => 1, 'created_at' => '2026-04-29 09:35:00', 'updated_at' => '2026-04-29 09:35:00'],
            ['estudio_id' => 55, 'paciente_id' => 55, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260429-783621.pdf', 'estado' => 1, 'created_at' => '2026-04-29 10:50:00', 'updated_at' => '2026-04-29 10:50:00'],
            ['estudio_id' => 56, 'paciente_id' => 56, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260430-630472.pdf', 'estado' => 1, 'created_at' => '2026-04-30 08:05:00', 'updated_at' => '2026-04-30 08:05:00'],
            ['estudio_id' => 57, 'paciente_id' => 57, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260430-205819.pdf', 'estado' => 1, 'created_at' => '2026-04-30 09:20:00', 'updated_at' => '2026-04-30 09:20:00'],
            ['estudio_id' => 58, 'paciente_id' => 58, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260504-471638.pdf', 'estado' => 1, 'created_at' => '2026-05-04 08:15:00', 'updated_at' => '2026-05-04 08:15:00'],
            ['estudio_id' => 59, 'paciente_id' => 59, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260504-839205.pdf', 'estado' => 1, 'created_at' => '2026-05-04 09:30:00', 'updated_at' => '2026-05-04 09:30:00'],
            ['estudio_id' => 60, 'paciente_id' => 60, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260504-126794.pdf', 'estado' => 1, 'created_at' => '2026-05-04 10:45:00', 'updated_at' => '2026-05-04 10:45:00'],
            ['estudio_id' => 61, 'paciente_id' => 61, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260504-592481.pdf', 'estado' => 1, 'created_at' => '2026-05-04 12:00:00', 'updated_at' => '2026-05-04 12:00:00'],
            ['estudio_id' => 62, 'paciente_id' => 62, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260505-384729.pdf', 'estado' => 1, 'created_at' => '2026-05-05 08:00:00', 'updated_at' => '2026-05-05 08:00:00'],
            ['estudio_id' => 63, 'paciente_id' => 63, 'registrado_por' => 1, 'observaciones' => 'Migrado desde ecg_analyses. Archivo: 20260505-917506.pdf', 'estado' => 1, 'created_at' => '2026-05-05 09:15:00', 'updated_at' => '2026-05-05 09:15:00'],
        ];

        if (DB::table('estudios')->count() == 0) {
            // Cargar pacientes para calcular edad automáticamente
            $pacientes = DB::table('pacientes')
                ->whereNotNull('fecha_nacimiento')
                ->pluck('fecha_nacimiento', 'paciente_id');

            $data = array_map(function ($estudio) use ($pacientes) {
                $fechaEstudio = Carbon::parse($estudio['created_at']);
                $fechaNacimiento = isset($pacientes[$estudio['paciente_id']])
                    ? Carbon::parse($pacientes[$estudio['paciente_id']])
                    : null;

                $estudio['edad'] = $fechaNacimiento
                    ? $fechaNacimiento->diffInYears($fechaEstudio)
                    : null;

                return $estudio;
            }, $estudios);

            DB::table('estudios')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('estudios', 'estudio_id'), COALESCE(MAX(estudio_id), 1)) FROM estudios");
        }
    }
}

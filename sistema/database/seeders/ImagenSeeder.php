<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImagenSeeder extends Seeder
{
    public function run(): void
    {
        $rutas = [
            'ecg/20260406-073305.pdf',
            'ecg/20260406-003414.pdf',
            'ecg/20260406-165440.pdf',
            'ecg/20260407-005528.pdf',
            'ecg/20260407-025820.pdf',
            'ecg/20260407-045724.pdf',
            'ecg/20260407-224600.pdf',
            'ecg/20260408-051320.pdf',
            'ecg/20260408-020912.pdf',
            'ecg/20260409-102042.pdf',
            'ecg/20260409-004426.pdf',
            'ecg/20260409-135548.pdf',
            'ecg/20260410-105919.pdf',
            'ecg/20260410-235327.pdf',
            'ecg/20260410-151022.pdf',
            'ecg/20260413-014647.pdf',
            'ecg/20260413-084942.pdf',
            'ecg/20260414-093303.pdf',
            'ecg/20260414-100821.pdf',
            'ecg/20260414-034148.pdf',
            'ecg/20260414-163753.pdf',
            'ecg/20260415-193248.pdf',
            'ecg/20260415-085019.pdf',
            'ecg/20260415-195052.pdf',
            'ecg/20260416-231046.pdf',
            'ecg/20260416-041558.pdf',
            'ecg/20260416-001757.pdf',
            'ecg/20260417-104352.pdf',
            'ecg/20260417-154751.pdf',
            'ecg/20260417-131149.pdf',
            'ecg/20260420-032337.pdf',
            'ecg/20260420-163706.pdf',
            'ecg/20260420-195711.pdf',
            'ecg/20260420-231738.pdf',
            'ecg/20260421-104755.pdf',
            'ecg/20260421-044805.pdf',
            'ecg/20260422-051111.pdf',
            'ecg/20260422-220140.pdf',
            'ecg/20260422-034849.pdf',
            'ecg/20260423-144826.pdf',
            'ecg/20260423-180506.pdf',
            'ecg/20260423-160127.pdf',
            'ecg/20260424-085620.pdf',
            'ecg/20260424-123132.pdf',
            'ecg/20260424-165524.pdf',
            'ecg/20260427-095501.pdf',
            'ecg/20260427-062007.pdf',
            'ecg/20260427-024651.pdf',
            'ecg/20260427-131115.pdf',
            'ecg/20260428-091222.pdf',
            'ecg/20260428-171350.pdf',
            'ecg/20260428-005621.pdf',
            'ecg/20260429-015100.pdf',
            'ecg/20260429-030821.pdf',
            'ecg/20260429-031136.pdf',
            'ecg/20260430-004128.pdf',
            'ecg/20260430-154500.pdf',
            'ecg/20260504-060218.pdf',
            'ecg/20260504-044305.pdf',
            'ecg/20260504-034349.pdf',
            'ecg/20260504-193145.pdf',
            'ecg/20260505-220753.pdf',
            'ecg/20260505-225202.pdf',
        ];

        $data = collect($rutas)->map(function (string $ruta, int $index) {
            $fecha = Carbon::createFromFormat('Ymd-His', pathinfo($ruta, PATHINFO_FILENAME));

            return [
                'imagen_id' => $index + 1,
                'estudio_id' => $index + 1,
                'ruta' => $ruta,
                'formato' => pathinfo($ruta, PATHINFO_EXTENSION),
                'resolucion' => 'Documento PDF',
                'tamano_kb' => rand(200, 1200),
                'estado' => 1,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ];
        })->all();

        if (DB::table('imagenes')->count() == 0) {
            DB::table('imagenes')->insert($data);
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('imagenes', 'imagen_id'), COALESCE(MAX(imagen_id), 1)) FROM imagenes");
        }
    }
}

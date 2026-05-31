<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumentoIdentidad;

class TipoIdentidadDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'siglas' => 'DNI',
                'descripcion' => 'DOCUMENTO NACIONAL DE IDENTIDAD',
                'estado' => 1,
                'minimo' => 8,
                'maximo' => 8
            ],
            [
                'siglas' => 'CE',
                'descripcion' => 'CARNÉ DE EXTRANJERÍA',
                'estado' => 1,
                'minimo' => 9,
                'maximo' => 12
            ],
            [
                'siglas' => 'PAS',
                'descripcion' => 'PASAPORTE',
                'estado' => 1,
                'minimo' => 6,
                'maximo' => 12
            ],
            [
                'siglas' => 'RUC',
                'descripcion' => 'REGISTRO ÚNICO DE CONTRIBUYENTES',
                'estado' => 1,
                'minimo' => 11,
                'maximo' => 11
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoDocumentoIdentidad::updateOrCreate(
                ['descripcion' => $tipo['descripcion']],
                $tipo
            );
        }
    }
}

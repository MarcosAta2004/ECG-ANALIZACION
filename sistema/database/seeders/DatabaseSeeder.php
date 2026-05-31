<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TipoIdentidadDocumentoSeeder::class,
            UserSeeder::class,
            GrupoCardiacoSeeder::class,
            NivelGravedadSeeder::class,
            ClasificacionArritmiaSeeder::class,
            RitmoCardiacoSeeder::class,
            PrefijoPacienteSeeder::class,
            PacienteSeeder::class,
            EstudioSeeder::class,
            ImagenSeeder::class,
            PrediccionSeeder::class,
            DiagnosticoSeeder::class,
            AuditoriaSeeder::class,
        ]);
    }
}

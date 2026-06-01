<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Seeder exclusivo para el entorno de testing.
 * Siembra solo los datos de catálogo mínimos que los tests necesitan.
 * NO toca la BD de producción (solo se invoca desde TestCase::setUp).
 */
class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar cache de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Tipo de documento identidad (columnas reales: siglas, descripcion, estado)
        DB::table('tipo_documento_identidades')->insertOrIgnore([
            ['id' => 1, 'siglas' => 'DNI',  'descripcion' => 'Documento Nacional de Identidad', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'siglas' => 'PAS',  'descripcion' => 'Pasaporte',                        'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Roles (Spatie)
        $roles = ['administrador', 'cardiologo', 'tecnico'];
        foreach ($roles as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }

        // 3. Grupos cardíacos
        DB::table('grupos_cardiacos')->insertOrIgnore([
            ['grupo_id' => 1, 'nombre' => 'Sinusal', 'descripcion' => 'Grupo sinusal', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['grupo_id' => 2, 'nombre' => 'Conduccion', 'descripcion' => 'Grupo conducción', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['grupo_id' => 3, 'nombre' => 'Ectopico', 'descripcion' => 'Grupo ectópico', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['grupo_id' => 4, 'nombre' => 'Supraventricular', 'descripcion' => 'Grupo supraventricular', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Niveles de gravedad
        DB::table('niveles_gravedad')->insertOrIgnore([
            ['nivel_id' => 1, 'nombre' => 'Bajo', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nivel_id' => 2, 'nombre' => 'Moderado', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nivel_id' => 3, 'nombre' => 'Alto', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Clasificaciones de arritmia
        DB::table('clasificaciones_arritmia')->insertOrIgnore([
            ['clasificacion_id' => 1, 'nombre' => 'Normal', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['clasificacion_id' => 2, 'nombre' => 'Arritmia', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. Ritmos cardíacos esenciales (los que usa el modelo IA)
        DB::table('ritmos_cardiacos')->insertOrIgnore([
            ['ritmo_id' => 1, 'grupo_id' => 1, 'nivel_id' => 1, 'clasificacion_id' => 1, 'label' => 'NORM',  'nombre' => 'Ritmo Sinusal Normal',           'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 2, 'grupo_id' => 2, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => '1AVB',  'nombre' => 'Bloqueo AV de primer grado',      'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 3, 'grupo_id' => 2, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'WPW',   'nombre' => 'Sindrome de Wolff-Parkinson-White','estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 4, 'grupo_id' => 3, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'PVC',   'nombre' => 'Complejo ventricular prematuro',   'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 5, 'grupo_id' => 3, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'PAC',   'nombre' => 'Complejo auricular prematuro',    'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 6, 'grupo_id' => 4, 'nivel_id' => 3, 'clasificacion_id' => 2, 'label' => 'AFIB',  'nombre' => 'Fibrilacion Auricular',           'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 7, 'grupo_id' => 1, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'STACH', 'nombre' => 'Taquicardia Sinusal',             'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 8, 'grupo_id' => 1, 'nivel_id' => 1, 'clasificacion_id' => 1, 'label' => 'SARRH', 'nombre' => 'Arritmia Sinusal',               'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' => 9, 'grupo_id' => 1, 'nivel_id' => 1, 'clasificacion_id' => 1, 'label' => 'SBRAD', 'nombre' => 'Bradicardia Sinusal',            'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' =>10, 'grupo_id' => 4, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'SVARR', 'nombre' => 'Arritmia Supraventricular',      'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' =>11, 'grupo_id' => 3, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'BIGU',  'nombre' => 'Bigeminismo',                    'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' =>12, 'grupo_id' => 4, 'nivel_id' => 3, 'clasificacion_id' => 2, 'label' => 'AFLT',  'nombre' => 'Flutter Auricular',              'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['ritmo_id' =>13, 'grupo_id' => 4, 'nivel_id' => 2, 'clasificacion_id' => 2, 'label' => 'PSVT',  'nombre' => 'Taquicardia supraventricular paroxistica', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 7. Prefijo base para tests
        DB::table('prefijos_paciente')->insertOrIgnore([
            ['prefijo_id' => 1, 'nombre' => 'TEST', 'descripcion' => 'Prefijo para tests', 'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['prefijo_id' => 2, 'nombre' => 'PAC',  'descripcion' => 'Prefijo paciente',   'estado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

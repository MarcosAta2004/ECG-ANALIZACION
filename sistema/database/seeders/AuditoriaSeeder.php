<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $auditorias = [
            [
                'auditoria_id' => 1,
                'usuario_id' => 1,
                'accion' => 'logout',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '1',
                'descripcion' => 'Cierre de sesion.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"email": "admin@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:06:23',
                'updated_at' => '2026-05-21 14:06:23',
            ],
            [
                'auditoria_id' => 2,
                'usuario_id' => 2,
                'accion' => 'login',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '2',
                'descripcion' => 'Inicio de sesion exitoso.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"rol": "Medico", "email": "jose@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:06:31',
                'updated_at' => '2026-05-21 14:06:31',
            ],
            [
                'auditoria_id' => 3,
                'usuario_id' => 2,
                'accion' => 'logout',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '2',
                'descripcion' => 'Cierre de sesion.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"email": "jose@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:20:55',
                'updated_at' => '2026-05-21 14:20:55',
            ],
            [
                'auditoria_id' => 4,
                'usuario_id' => 1,
                'accion' => 'login',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '1',
                'descripcion' => 'Inicio de sesion exitoso.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"rol": "Administrador", "email": "admin@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:21:04',
                'updated_at' => '2026-05-21 14:21:04',
            ],
            [
                'auditoria_id' => 5,
                'usuario_id' => 1,
                'accion' => 'crear',
                'modulo' => 'Usuarios',
                'entidad' => 'users',
                'entidad_id' => '3',
                'descripcion' => 'Creacion de usuario.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"auditoria_id": 3, "name": "Fermin", "email": "fermin@ecg.com", "estado": true, "role_id": "3"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:21:50',
                'updated_at' => '2026-05-21 14:21:50',
            ],
            [
                'auditoria_id' => 6,
                'usuario_id' => 1,
                'accion' => 'logout',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '1',
                'descripcion' => 'Cierre de sesion.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"email": "admin@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:21:55',
                'updated_at' => '2026-05-21 14:21:55',
            ],
            [
                'auditoria_id' => 7,
                'usuario_id' => 3,
                'accion' => 'login',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '3',
                'descripcion' => 'Inicio de sesion exitoso.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"rol": "Operador", "email": "fermin@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:22:03',
                'updated_at' => '2026-05-21 14:22:03',
            ],
            [
                'auditoria_id' => 8,
                'usuario_id' => 3,
                'accion' => 'logout',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '3',
                'descripcion' => 'Cierre de sesion.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"email": "fermin@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:53:21',
                'updated_at' => '2026-05-21 14:53:21',
            ],
            [
                'auditoria_id' => 9,
                'usuario_id' => 1,
                'accion' => 'login',
                'modulo' => 'Autenticacion',
                'entidad' => 'users',
                'entidad_id' => '1',
                'descripcion' => 'Inicio de sesion exitoso.',
                'valores_anteriores' => null,
                'valores_nuevos' => '{"rol": "Administrador", "email": "admin@ecg.com"}',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
                'created_at' => '2026-05-21 14:53:34',
                'updated_at' => '2026-05-21 14:53:34',
            ],
        ];

        foreach ($auditorias as $a) {
            Auditoria::updateOrCreate(
                ['auditoria_id' => $a['auditoria_id']],
                $a
            );
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('auditorias', 'auditoria_id'), COALESCE(MAX(auditoria_id), 1)) FROM auditorias");
        }
    }
}

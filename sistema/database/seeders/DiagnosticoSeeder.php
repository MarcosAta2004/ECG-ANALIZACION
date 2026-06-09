<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DiagnosticoSeeder extends Seeder
{
    /**
     * Seeder normalizado para diagnósticos clínicos
     *
     * CAMBIOS DE NORMALIZACIÓN:
     * ❌ Eliminado: descripcion  → se obtiene via ritmoCardiaco->nombre (Eloquent)
     * ❌ Eliminado: medico_id    → reemplazado por registrado_por (FK a users.id)
     * ✅ concordancia            → booleano estricto (true=1 / false=0) TINYINT
     * ✅ registrado_por          → FK a users (rol validado en backend vía Spatie)
     */
    public function run(): void
    {
        // Buscar dinámicamente el usuario cardiólogo
        $registradorId = User::where('login', 'cardiologo')->first()?->id ?? 2;

        $data = [
            ['diagnostico_id' =>  1, 'estudio_id' =>  1, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-06 08:10:00', 'estado' => 1, 'created_at' => '2026-04-06 08:10:00', 'updated_at' => '2026-04-06 08:10:00'],
            ['diagnostico_id' =>  2, 'estudio_id' =>  2, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-06 09:25:00', 'estado' => 1, 'created_at' => '2026-04-06 09:25:00', 'updated_at' => '2026-04-06 09:25:00'],
            ['diagnostico_id' =>  3, 'estudio_id' =>  3, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-06 10:40:00', 'estado' => 1, 'created_at' => '2026-04-06 10:40:00', 'updated_at' => '2026-04-06 10:40:00'],
            ['diagnostico_id' =>  4, 'estudio_id' =>  4, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-07 08:05:00', 'estado' => 1, 'created_at' => '2026-04-07 08:05:00', 'updated_at' => '2026-04-07 08:05:00'],
            ['diagnostico_id' =>  5, 'estudio_id' =>  5, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-07 09:20:00', 'estado' => 1, 'created_at' => '2026-04-07 09:20:00', 'updated_at' => '2026-04-07 09:20:00'],
            ['diagnostico_id' =>  6, 'estudio_id' =>  6, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-07 10:35:00', 'estado' => 1, 'created_at' => '2026-04-07 10:35:00', 'updated_at' => '2026-04-07 10:35:00'],
            ['diagnostico_id' =>  7, 'estudio_id' =>  7, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 0, 'observacion' => null, 'fecha_revision' => '2026-04-07 11:50:00', 'estado' => 1, 'created_at' => '2026-04-07 11:50:00', 'updated_at' => '2026-04-07 11:50:00'],
            ['diagnostico_id' =>  8, 'estudio_id' =>  8, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-08 08:15:00', 'estado' => 1, 'created_at' => '2026-04-08 08:15:00', 'updated_at' => '2026-04-08 08:15:00'],
            ['diagnostico_id' =>  9, 'estudio_id' =>  9, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-08 09:30:00', 'estado' => 1, 'created_at' => '2026-04-08 09:30:00', 'updated_at' => '2026-04-08 09:30:00'],
            ['diagnostico_id' => 10, 'estudio_id' => 10, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-09 08:00:00', 'estado' => 1, 'created_at' => '2026-04-09 08:00:00', 'updated_at' => '2026-04-09 08:00:00'],
            ['diagnostico_id' => 11, 'estudio_id' => 11, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-09 09:15:00', 'estado' => 1, 'created_at' => '2026-04-09 09:15:00', 'updated_at' => '2026-04-09 09:15:00'],
            ['diagnostico_id' => 12, 'estudio_id' => 12, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-09 10:30:00', 'estado' => 1, 'created_at' => '2026-04-09 10:30:00', 'updated_at' => '2026-04-09 10:30:00'],
            ['diagnostico_id' => 13, 'estudio_id' => 13, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-10 08:20:00', 'estado' => 1, 'created_at' => '2026-04-10 08:20:00', 'updated_at' => '2026-04-10 08:20:00'],
            ['diagnostico_id' => 14, 'estudio_id' => 14, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-10 09:35:00', 'estado' => 1, 'created_at' => '2026-04-10 09:35:00', 'updated_at' => '2026-04-10 09:35:00'],
            ['diagnostico_id' => 15, 'estudio_id' => 15, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-10 10:50:00', 'estado' => 1, 'created_at' => '2026-04-10 10:50:00', 'updated_at' => '2026-04-10 10:50:00'],
            ['diagnostico_id' => 16, 'estudio_id' => 16, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-13 08:10:00', 'estado' => 1, 'created_at' => '2026-04-13 08:10:00', 'updated_at' => '2026-04-13 08:10:00'],
            ['diagnostico_id' => 17, 'estudio_id' => 17, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-13 09:25:00', 'estado' => 1, 'created_at' => '2026-04-13 09:25:00', 'updated_at' => '2026-04-13 09:25:00'],
            ['diagnostico_id' => 18, 'estudio_id' => 18, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-14 08:05:00', 'estado' => 1, 'created_at' => '2026-04-14 08:05:00', 'updated_at' => '2026-04-14 08:05:00'],
            ['diagnostico_id' => 19, 'estudio_id' => 19, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-14 09:20:00', 'estado' => 1, 'created_at' => '2026-04-14 09:20:00', 'updated_at' => '2026-04-14 09:20:00'],
            ['diagnostico_id' => 20, 'estudio_id' => 20, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-14 10:35:00', 'estado' => 1, 'created_at' => '2026-04-14 10:35:00', 'updated_at' => '2026-04-14 10:35:00'],
            ['diagnostico_id' => 21, 'estudio_id' => 21, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 0, 'observacion' => null, 'fecha_revision' => '2026-04-14 11:50:00', 'estado' => 1, 'created_at' => '2026-04-14 11:50:00', 'updated_at' => '2026-04-14 11:50:00'],
            ['diagnostico_id' => 22, 'estudio_id' => 22, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-15 08:15:00', 'estado' => 1, 'created_at' => '2026-04-15 08:15:00', 'updated_at' => '2026-04-15 08:15:00'],
            ['diagnostico_id' => 23, 'estudio_id' => 23, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-15 09:30:00', 'estado' => 1, 'created_at' => '2026-04-15 09:30:00', 'updated_at' => '2026-04-15 09:30:00'],
            ['diagnostico_id' => 24, 'estudio_id' => 24, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-15 10:45:00', 'estado' => 1, 'created_at' => '2026-04-15 10:45:00', 'updated_at' => '2026-04-15 10:45:00'],
            ['diagnostico_id' => 25, 'estudio_id' => 25, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-16 08:00:00', 'estado' => 1, 'created_at' => '2026-04-16 08:00:00', 'updated_at' => '2026-04-16 08:00:00'],
            ['diagnostico_id' => 26, 'estudio_id' => 26, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-16 09:15:00', 'estado' => 1, 'created_at' => '2026-04-16 09:15:00', 'updated_at' => '2026-04-16 09:15:00'],
            ['diagnostico_id' => 27, 'estudio_id' => 27, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-16 10:30:00', 'estado' => 1, 'created_at' => '2026-04-16 10:30:00', 'updated_at' => '2026-04-16 10:30:00'],
            ['diagnostico_id' => 28, 'estudio_id' => 28, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-17 08:20:00', 'estado' => 1, 'created_at' => '2026-04-17 08:20:00', 'updated_at' => '2026-04-17 08:20:00'],
            ['diagnostico_id' => 29, 'estudio_id' => 29, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-17 09:35:00', 'estado' => 1, 'created_at' => '2026-04-17 09:35:00', 'updated_at' => '2026-04-17 09:35:00'],
            ['diagnostico_id' => 30, 'estudio_id' => 30, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-17 10:50:00', 'estado' => 1, 'created_at' => '2026-04-17 10:50:00', 'updated_at' => '2026-04-17 10:50:00'],
            ['diagnostico_id' => 31, 'estudio_id' => 31, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-20 08:05:00', 'estado' => 1, 'created_at' => '2026-04-20 08:05:00', 'updated_at' => '2026-04-20 08:05:00'],
            ['diagnostico_id' => 32, 'estudio_id' => 32, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-20 09:20:00', 'estado' => 1, 'created_at' => '2026-04-20 09:20:00', 'updated_at' => '2026-04-20 09:20:00'],
            ['diagnostico_id' => 33, 'estudio_id' => 33, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-20 10:35:00', 'estado' => 1, 'created_at' => '2026-04-20 10:35:00', 'updated_at' => '2026-04-20 10:35:00'],
            ['diagnostico_id' => 34, 'estudio_id' => 34, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 0, 'observacion' => null, 'fecha_revision' => '2026-04-20 11:50:00', 'estado' => 1, 'created_at' => '2026-04-20 11:50:00', 'updated_at' => '2026-04-20 11:50:00'],
            ['diagnostico_id' => 35, 'estudio_id' => 35, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-21 08:15:00', 'estado' => 1, 'created_at' => '2026-04-21 08:15:00', 'updated_at' => '2026-04-21 08:15:00'],
            ['diagnostico_id' => 36, 'estudio_id' => 36, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-21 09:30:00', 'estado' => 1, 'created_at' => '2026-04-21 09:30:00', 'updated_at' => '2026-04-21 09:30:00'],
            ['diagnostico_id' => 37, 'estudio_id' => 37, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-22 08:00:00', 'estado' => 1, 'created_at' => '2026-04-22 08:00:00', 'updated_at' => '2026-04-22 08:00:00'],
            ['diagnostico_id' => 38, 'estudio_id' => 38, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-22 09:15:00', 'estado' => 1, 'created_at' => '2026-04-22 09:15:00', 'updated_at' => '2026-04-22 09:15:00'],
            ['diagnostico_id' => 39, 'estudio_id' => 39, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-22 10:30:00', 'estado' => 1, 'created_at' => '2026-04-22 10:30:00', 'updated_at' => '2026-04-22 10:30:00'],
            ['diagnostico_id' => 40, 'estudio_id' => 40, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-23 08:20:00', 'estado' => 1, 'created_at' => '2026-04-23 08:20:00', 'updated_at' => '2026-04-23 08:20:00'],
            ['diagnostico_id' => 41, 'estudio_id' => 41, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-23 09:35:00', 'estado' => 1, 'created_at' => '2026-04-23 09:35:00', 'updated_at' => '2026-04-23 09:35:00'],
            ['diagnostico_id' => 42, 'estudio_id' => 42, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-23 10:50:00', 'estado' => 1, 'created_at' => '2026-04-23 10:50:00', 'updated_at' => '2026-04-23 10:50:00'],
            ['diagnostico_id' => 43, 'estudio_id' => 43, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-24 08:05:00', 'estado' => 1, 'created_at' => '2026-04-24 08:05:00', 'updated_at' => '2026-04-24 08:05:00'],
            ['diagnostico_id' => 44, 'estudio_id' => 44, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-24 09:20:00', 'estado' => 1, 'created_at' => '2026-04-24 09:20:00', 'updated_at' => '2026-04-24 09:20:00'],
            ['diagnostico_id' => 45, 'estudio_id' => 45, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-24 10:35:00', 'estado' => 1, 'created_at' => '2026-04-24 10:35:00', 'updated_at' => '2026-04-24 10:35:00'],
            ['diagnostico_id' => 46, 'estudio_id' => 46, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-27 08:15:00', 'estado' => 1, 'created_at' => '2026-04-27 08:15:00', 'updated_at' => '2026-04-27 08:15:00'],
            ['diagnostico_id' => 47, 'estudio_id' => 47, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-27 09:30:00', 'estado' => 1, 'created_at' => '2026-04-27 09:30:00', 'updated_at' => '2026-04-27 09:30:00'],
            ['diagnostico_id' => 48, 'estudio_id' => 48, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-27 10:45:00', 'estado' => 1, 'created_at' => '2026-04-27 10:45:00', 'updated_at' => '2026-04-27 10:45:00'],
            ['diagnostico_id' => 49, 'estudio_id' => 49, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-27 12:00:00', 'estado' => 1, 'created_at' => '2026-04-27 12:00:00', 'updated_at' => '2026-04-27 12:00:00'],
            ['diagnostico_id' => 50, 'estudio_id' => 50, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-28 08:00:00', 'estado' => 1, 'created_at' => '2026-04-28 08:00:00', 'updated_at' => '2026-04-28 08:00:00'],
            ['diagnostico_id' => 51, 'estudio_id' => 51, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-28 09:15:00', 'estado' => 1, 'created_at' => '2026-04-28 09:15:00', 'updated_at' => '2026-04-28 09:15:00'],
            ['diagnostico_id' => 52, 'estudio_id' => 52, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-28 10:30:00', 'estado' => 1, 'created_at' => '2026-04-28 10:30:00', 'updated_at' => '2026-04-28 10:30:00'],
            ['diagnostico_id' => 53, 'estudio_id' => 53, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-29 08:20:00', 'estado' => 1, 'created_at' => '2026-04-29 08:20:00', 'updated_at' => '2026-04-29 08:20:00'],
            ['diagnostico_id' => 54, 'estudio_id' => 54, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-29 09:35:00', 'estado' => 1, 'created_at' => '2026-04-29 09:35:00', 'updated_at' => '2026-04-29 09:35:00'],
            ['diagnostico_id' => 55, 'estudio_id' => 55, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-29 10:50:00', 'estado' => 1, 'created_at' => '2026-04-29 10:50:00', 'updated_at' => '2026-04-29 10:50:00'],
            ['diagnostico_id' => 56, 'estudio_id' => 56, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-30 08:05:00', 'estado' => 1, 'created_at' => '2026-04-30 08:05:00', 'updated_at' => '2026-04-30 08:05:00'],
            ['diagnostico_id' => 57, 'estudio_id' => 57, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-04-30 09:20:00', 'estado' => 1, 'created_at' => '2026-04-30 09:20:00', 'updated_at' => '2026-04-30 09:20:00'],
            ['diagnostico_id' => 58, 'estudio_id' => 58, 'ritmo_id' =>  6, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-05-04 08:15:00', 'estado' => 1, 'created_at' => '2026-05-04 08:15:00', 'updated_at' => '2026-05-04 08:15:00'],
            ['diagnostico_id' => 59, 'estudio_id' => 59, 'ritmo_id' =>  7, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-05-04 09:30:00', 'estado' => 1, 'created_at' => '2026-05-04 09:30:00', 'updated_at' => '2026-05-04 09:30:00'],
            ['diagnostico_id' => 60, 'estudio_id' => 60, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-05-04 10:45:00', 'estado' => 1, 'created_at' => '2026-05-04 10:45:00', 'updated_at' => '2026-05-04 10:45:00'],
            ['diagnostico_id' => 61, 'estudio_id' => 61, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-05-04 12:00:00', 'estado' => 1, 'created_at' => '2026-05-04 12:00:00', 'updated_at' => '2026-05-04 12:00:00'],
            ['diagnostico_id' => 62, 'estudio_id' => 62, 'ritmo_id' => 12, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-05-05 08:00:00', 'estado' => 1, 'created_at' => '2026-05-05 08:00:00', 'updated_at' => '2026-05-05 08:00:00'],
            ['diagnostico_id' => 63, 'estudio_id' => 63, 'ritmo_id' =>  1, 'registrado_por' => $registradorId, 'concordancia' => 1, 'observacion' => null, 'fecha_revision' => '2026-05-05 09:15:00', 'estado' => 1, 'created_at' => '2026-05-05 09:15:00', 'updated_at' => '2026-05-05 09:15:00'],
        ];

        if (DB::table('diagnosticos')->count() == 0) {
            DB::table('diagnosticos')->insert($data);
        }

        // Sincronizar secuencia solo en Postgres
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('diagnosticos', 'diagnostico_id'), COALESCE(MAX(diagnostico_id), 1)) FROM diagnosticos");
        }
    }
}

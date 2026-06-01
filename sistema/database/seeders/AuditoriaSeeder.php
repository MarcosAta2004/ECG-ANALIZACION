<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditoriaSeeder extends Seeder
{
    /**
     * Seeder para auditoría de validaciones médicas
     * 
     * DESCRIPCIÓN:
     * - Carga 63 registros de auditoría simulando valoraciones del cardiólogo
     * - Representa las validaciones y diagnósticos en el historial médico
     * - Es como "logs" de lo que el especialista validó en el sistema
     * 
     * CAMPOS PRINCIPALES (Lo esencial):
     * ✅ usuario_id        → ID del cardiólogo (usuario_id = 2)
     * ✅ accion            → VALIDACION_DIAGNOSTICO
     * ✅ modulo            → Clinico
     * ✅ created_at        → Fecha de la valoración
     */
    public function run(): void
    {
        // Obtener el cardiólogo del seeder de usuarios
        $cardiologo = User::where('login', 'cardiologo')->first();
        $cardiologoId = $cardiologo?->id ?? 2;

        $auditorias = [
            ['diagnostico_id' => 1, 'ritmo' => 'Taquicardia Sinusal', 'confianza' => 'ALTO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-06 08:15:00'],
            ['diagnostico_id' => 2, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-06 09:30:00'],
            ['diagnostico_id' => 3, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-06 10:45:00'],
            ['diagnostico_id' => 4, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-07 08:10:00'],
            ['diagnostico_id' => 5, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-07 09:25:00'],
            ['diagnostico_id' => 6, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-07 10:40:00'],
            ['diagnostico_id' => 7, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-07 11:55:00'],
            ['diagnostico_id' => 8, 'ritmo' => 'Complejo ventricular prematuro', 'confianza' => 'ALTO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-08 08:20:00'],
            ['diagnostico_id' => 9, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-08 09:35:00'],
            ['diagnostico_id' => 10, 'ritmo' => 'Bradicardia Sinusal', 'confianza' => 'MEDIO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-09 08:05:00'],
            ['diagnostico_id' => 11, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-09 09:20:00'],
            ['diagnostico_id' => 12, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-09 10:35:00'],
            ['diagnostico_id' => 13, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-10 08:25:00'],
            ['diagnostico_id' => 14, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-10 09:40:00'],
            ['diagnostico_id' => 15, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-10 10:55:00'],
            ['diagnostico_id' => 16, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-13 08:15:00'],
            ['diagnostico_id' => 17, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-13 09:30:00'],
            ['diagnostico_id' => 18, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-14 08:10:00'],
            ['diagnostico_id' => 19, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-14 09:25:00'],
            ['diagnostico_id' => 20, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-14 10:40:00'],
            ['diagnostico_id' => 21, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-14 11:55:00'],
            ['diagnostico_id' => 22, 'ritmo' => 'Bradicardia Sinusal', 'confianza' => 'MEDIO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-15 08:20:00'],
            ['diagnostico_id' => 23, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-15 09:35:00'],
            ['diagnostico_id' => 24, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-15 10:50:00'],
            ['diagnostico_id' => 25, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-16 08:05:00'],
            ['diagnostico_id' => 26, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-16 09:20:00'],
            ['diagnostico_id' => 27, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-16 10:35:00'],
            ['diagnostico_id' => 28, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-17 08:25:00'],
            ['diagnostico_id' => 29, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-17 09:40:00'],
            ['diagnostico_id' => 30, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-17 10:55:00'],
            ['diagnostico_id' => 31, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-20 08:10:00'],
            ['diagnostico_id' => 32, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-20 09:25:00'],
            ['diagnostico_id' => 33, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-20 10:40:00'],
            ['diagnostico_id' => 34, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-20 11:55:00'],
            ['diagnostico_id' => 35, 'ritmo' => 'Complejo ventricular prematuro', 'confianza' => 'ALTO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-21 08:20:00'],
            ['diagnostico_id' => 36, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-21 09:35:00'],
            ['diagnostico_id' => 37, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-22 08:05:00'],
            ['diagnostico_id' => 38, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-22 09:20:00'],
            ['diagnostico_id' => 39, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-22 10:35:00'],
            ['diagnostico_id' => 40, 'ritmo' => 'Complejo ventricular prematuro', 'confianza' => 'ALTO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-23 08:25:00'],
            ['diagnostico_id' => 41, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-23 09:40:00'],
            ['diagnostico_id' => 42, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-23 10:55:00'],
            ['diagnostico_id' => 43, 'ritmo' => 'Bradicardia Sinusal', 'confianza' => 'MEDIO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-24 08:10:00'],
            ['diagnostico_id' => 44, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-24 09:25:00'],
            ['diagnostico_id' => 45, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-24 10:40:00'],
            ['diagnostico_id' => 46, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-27 08:20:00'],
            ['diagnostico_id' => 47, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-27 09:35:00'],
            ['diagnostico_id' => 48, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-27 10:50:00'],
            ['diagnostico_id' => 49, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-27 12:05:00'],
            ['diagnostico_id' => 50, 'ritmo' => 'Bradicardia Sinusal', 'confianza' => 'MEDIO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-04-28 08:05:00'],
            ['diagnostico_id' => 51, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-28 09:20:00'],
            ['diagnostico_id' => 52, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-28 10:35:00'],
            ['diagnostico_id' => 53, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-29 08:25:00'],
            ['diagnostico_id' => 54, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-29 09:40:00'],
            ['diagnostico_id' => 55, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-29 10:55:00'],
            ['diagnostico_id' => 56, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-04-30 08:10:00'],
            ['diagnostico_id' => 57, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-04-30 09:25:00'],
            ['diagnostico_id' => 58, 'ritmo' => 'Bradicardia Sinusal', 'confianza' => 'MEDIO', 'severidad' => 'IMPORTANTE', 'created_at' => '2026-05-04 08:20:00'],
            ['diagnostico_id' => 59, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-05-04 09:35:00'],
            ['diagnostico_id' => 60, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-05-04 10:50:00'],
            ['diagnostico_id' => 61, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-05-04 12:05:00'],
            ['diagnostico_id' => 62, 'ritmo' => 'Fibrilacion Auricular', 'confianza' => 'ALTO', 'severidad' => 'CRITICA', 'created_at' => '2026-05-05 08:05:00'],
            ['diagnostico_id' => 63, 'ritmo' => 'Ritmo Sinusal Normal', 'confianza' => 'ALTO', 'severidad' => 'MENOR', 'created_at' => '2026-05-05 09:20:00'],
        ];

        // ====== PROCESAMIENTO DE DATOS ======
        // Registra las 63 valoraciones médicas en la auditoría
        foreach ($auditorias as $a) {
            Auditoria::create([
                // CAMPOS PRIMORDIALES:
                'usuario_id' => $cardiologoId,              // ✅ El cardiólogo
                'accion' => 'VALIDACION_DIAGNOSTICO',      // ✅ Qué acción
                'modulo' => 'Clinico',                      // ✅ Módulo
                'entidad' => 'diagnosticos',
                'entidad_id' => (string) $a['diagnostico_id'],
                'created_at' => $a['created_at'],           // ✅ Cuándo
                
                // DATOS MÉDICOS (La valoración):
                'cardiologo_id' => $cardiologoId,          // ✅ Quién valoró
                'valoracion_medico' => "Validación confirmada. Ritmo detectado: {$a['ritmo']}. Concordancia entre IA y médico verificada.",
                'nivel_confianza' => $a['confianza'],
                'razon_cambio' => 'VALIDACION_MEDICA',
                'severidad' => $a['severidad'],
                
                // CAMPOS OPCIONALES:
                'descripcion' => "Validación de diagnóstico #{$a['diagnostico_id']}: {$a['ritmo']}",
                'valores_anteriores' => null,
                'valores_nuevos' => null,
                'ip_address' => null,
                'user_agent' => null,
            ]);
        }
    }
}

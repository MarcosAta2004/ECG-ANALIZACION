<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\Diagnostico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AuditoriaSeeder extends Seeder
{
    public function run(): void
    {
        $administradorId = User::where('login', 'admin')->first()?->id ?? 1;
        $cardiologoId = User::where('login', 'cardiologo')->first()?->id ?? 2;
        $licenciadoId = User::where('login', 'licenciado')->first()?->id ?? 3;

        $auditorias = [
            [1, 'Taquicardia Sinusal', 'ALTO', 'IMPORTANTE', '2026-04-06 08:15:00'],
            [2, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-06 09:30:00'],
            [3, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-06 10:45:00'],
            [4, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-07 08:10:00'],
            [5, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-07 09:25:00'],
            [6, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-07 10:40:00'],
            [7, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-07 11:55:00'],
            [8, 'Complejo ventricular prematuro', 'ALTO', 'IMPORTANTE', '2026-04-08 08:20:00'],
            [9, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-08 09:35:00'],
            [10, 'Bradicardia Sinusal', 'MEDIO', 'IMPORTANTE', '2026-04-09 08:05:00'],
            [11, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-09 09:20:00'],
            [12, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-09 10:35:00'],
            [13, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-10 08:25:00'],
            [14, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-10 09:40:00'],
            [15, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-10 10:55:00'],
            [16, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-13 08:15:00'],
            [17, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-13 09:30:00'],
            [18, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-14 08:10:00'],
            [19, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-14 09:25:00'],
            [20, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-14 10:40:00'],
            [21, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-14 11:55:00'],
            [22, 'Bradicardia Sinusal', 'MEDIO', 'IMPORTANTE', '2026-04-15 08:20:00'],
            [23, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-15 09:35:00'],
            [24, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-15 10:50:00'],
            [25, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-16 08:05:00'],
            [26, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-16 09:20:00'],
            [27, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-16 10:35:00'],
            [28, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-17 08:25:00'],
            [29, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-17 09:40:00'],
            [30, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-17 10:55:00'],
            [31, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-20 08:10:00'],
            [32, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-20 09:25:00'],
            [33, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-20 10:40:00'],
            [34, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-20 11:55:00'],
            [35, 'Complejo ventricular prematuro', 'ALTO', 'IMPORTANTE', '2026-04-21 08:20:00'],
            [36, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-21 09:35:00'],
            [37, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-22 08:05:00'],
            [38, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-22 09:20:00'],
            [39, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-22 10:35:00'],
            [40, 'Complejo ventricular prematuro', 'ALTO', 'IMPORTANTE', '2026-04-23 08:25:00'],
            [41, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-23 09:40:00'],
            [42, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-23 10:55:00'],
            [43, 'Bradicardia Sinusal', 'MEDIO', 'IMPORTANTE', '2026-04-24 08:10:00'],
            [44, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-24 09:25:00'],
            [45, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-24 10:40:00'],
            [46, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-27 08:20:00'],
            [47, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-27 09:35:00'],
            [48, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-27 10:50:00'],
            [49, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-27 12:05:00'],
            [50, 'Bradicardia Sinusal', 'MEDIO', 'IMPORTANTE', '2026-04-28 08:05:00'],
            [51, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-28 09:20:00'],
            [52, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-28 10:35:00'],
            [53, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-29 08:25:00'],
            [54, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-29 09:40:00'],
            [55, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-29 10:55:00'],
            [56, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-04-30 08:10:00'],
            [57, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-04-30 09:25:00'],
            [58, 'Bradicardia Sinusal', 'MEDIO', 'IMPORTANTE', '2026-05-04 08:20:00'],
            [59, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-05-04 09:35:00'],
            [60, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-05-04 10:50:00'],
            [61, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-05-04 12:05:00'],
            [62, 'Fibrilacion Auricular', 'ALTO', 'CRITICA', '2026-05-05 08:05:00'],
            [63, 'Ritmo Sinusal Normal', 'ALTO', 'MENOR', '2026-05-05 09:20:00'],
        ];

        foreach ($auditorias as [$diagnosticoId, $ritmo, $confianza, $severidad, $createdAt]) {
            $diagnostico = Diagnostico::with('estudio')->find($diagnosticoId);
            $pacienteId = $diagnostico?->estudio?->paciente_id;
            $estudioId = $diagnostico?->estudio?->estudio_id;
            $fechaBase = Carbon::parse($createdAt);

            $this->crearRegistroLicenciado($licenciadoId, $pacienteId, $estudioId, $diagnosticoId, $ritmo, $severidad, $fechaBase->copy()->subMinutes(25));
            $this->crearValidacionCardiologo($cardiologoId, $pacienteId, $diagnosticoId, $ritmo, $confianza, $severidad, $fechaBase);
            $this->crearRevisionAdministrador($administradorId, $pacienteId, $diagnosticoId, $severidad, $fechaBase->copy()->addMinutes(35));
        }
    }

    private function crearRegistroLicenciado(int $usuarioId, ?int $pacienteId, ?int $estudioId, int $diagnosticoId, string $ritmo, string $severidad, Carbon $fecha): void
    {
        Auditoria::updateOrCreate(
            [
                'usuario_id' => $usuarioId,
                'accion' => 'REGISTRO_ESTUDIO',
                'entidad' => 'estudios',
                'entidad_id' => (string) ($estudioId ?? $diagnosticoId),
                'created_at' => $fecha->toDateTimeString(),
            ],
            $this->datosBase([
                'paciente_id' => $pacienteId,
                'modulo' => 'Clinico',
                'descripcion' => "Fermin registro y preparo el estudio ECG asociado al diagnostico #{$diagnosticoId}.",
                'nivel_confianza' => 'MEDIO',
                'razon_cambio' => 'REQUIERE_REVISION',
                'severidad' => $severidad === 'CRITICA' ? 'IMPORTANTE' : 'MENOR',
                'valores_nuevos' => [
                    'estudio_id' => $estudioId,
                    'diagnostico_pendiente' => $diagnosticoId,
                    'ritmo_referencial' => $ritmo,
                ],
                'updated_at' => $fecha->toDateTimeString(),
            ])
        );
    }

    private function crearValidacionCardiologo(int $usuarioId, ?int $pacienteId, int $diagnosticoId, string $ritmo, string $confianza, string $severidad, Carbon $fecha): void
    {
        Auditoria::updateOrCreate(
            [
                'usuario_id' => $usuarioId,
                'accion' => 'VALIDACION_DIAGNOSTICO',
                'entidad' => 'diagnosticos',
                'entidad_id' => (string) $diagnosticoId,
                'created_at' => $fecha->toDateTimeString(),
            ],
            $this->datosBase([
                'paciente_id' => $pacienteId,
                'modulo' => 'Clinico',
                'descripcion' => "Validacion de diagnostico #{$diagnosticoId}: {$ritmo}",
                'valoracion_medico' => "Validacion confirmada. Ritmo detectado: {$ritmo}. Concordancia entre IA y medico verificada.",
                'cardiologo_id' => $usuarioId,
                'nivel_confianza' => $confianza,
                'razon_cambio' => 'VALIDACION_MEDICA',
                'severidad' => $severidad,
                'updated_at' => $fecha->toDateTimeString(),
            ])
        );
    }

    private function crearRevisionAdministrador(int $usuarioId, ?int $pacienteId, int $diagnosticoId, string $severidad, Carbon $fecha): void
    {
        Auditoria::updateOrCreate(
            [
                'usuario_id' => $usuarioId,
                'accion' => 'REVISION_AUDITORIA',
                'entidad' => 'auditorias',
                'entidad_id' => (string) $diagnosticoId,
                'created_at' => $fecha->toDateTimeString(),
            ],
            $this->datosBase([
                'paciente_id' => $pacienteId,
                'modulo' => 'Seguridad',
                'descripcion' => "Administrador reviso la trazabilidad del diagnostico #{$diagnosticoId} y su validacion clinica.",
                'nivel_confianza' => 'ALTO',
                'razon_cambio' => 'CORRECCION_MANUAL',
                'severidad' => $severidad === 'CRITICA' ? 'IMPORTANTE' : 'MENOR',
                'valores_nuevos' => [
                    'diagnostico_id' => $diagnosticoId,
                    'revision' => 'trazabilidad verificada',
                ],
                'updated_at' => $fecha->toDateTimeString(),
            ])
        );
    }

    private function datosBase(array $datos): array
    {
        return array_merge([
            'valoracion_medico' => null,
            'cardiologo_id' => null,
            'valores_anteriores' => null,
            'valores_nuevos' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Seeder',
            'session_id' => null,
        ], $datos);
    }
}

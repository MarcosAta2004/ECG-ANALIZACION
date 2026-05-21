<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;

class ServicioAuditoria
{
    public static function registrar(
        string $accion,
        string $modulo,
        ?string $entidad = null,
        mixed $entidadId = null,
        ?string $descripcion = null,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?Request $request = null,
        ?int $usuarioId = null
    ): void {
        try {
            Auditoria::create([
                'usuario_id' => $usuarioId ?? Session::get('user.id'),
                'accion' => $accion,
                'modulo' => $modulo,
                'entidad' => $entidad,
                'entidad_id' => $entidadId === null ? null : (string) $entidadId,
                'descripcion' => $descripcion,
                'valores_anteriores' => self::limpiar($valoresAnteriores),
                'valores_nuevos' => self::limpiar($valoresNuevos),
                'user_agent' => $request?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // La auditoria no debe bloquear la operacion principal.
        }
    }

    private static function limpiar(?array $valores): ?array
    {
        if ($valores === null) {
            return null;
        }

        foreach (['password', 'remember_token', 'password_confirmation'] as $campo) {
            if (array_key_exists($campo, $valores)) {
                $valores[$campo] = '[PROTEGIDO]';
            }
        }

        return $valores;
    }
}

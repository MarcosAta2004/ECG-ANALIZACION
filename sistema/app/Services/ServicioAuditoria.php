<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ?int $usuarioId = null,
        // ⭐ Parámetros nuevos para auditoría mejorada:
        ?string $nivelConfianza = null,      // BAJO, MEDIO, ALTO
        ?string $razonCambio = null,         // CORRECCION_MANUAL, VALIDACION_MEDICA, etc
        ?string $severidad = null,           // CRITICA, IMPORTANTE, MENOR
        ?string $valoracionMedico = null,    // Solo si auth()->user()->hasRole('CARDIOLOGO')
        ?int $cardiologo_id = null
    ): void {
        try {
            $usuarioActual = Auth::user() ?? \App\Models\User::find(Session::get('user.id'));
            
            // ✅ Solo cardiólogo puede llenar valoración médica
            $arrayAuditoria = [
                'usuario_id' => $usuarioId ?? Auth::id() ?? Session::get('user.id'),
                'accion' => $accion,
                'modulo' => $modulo,
                'entidad' => $entidad,
                'entidad_id' => $entidadId === null ? null : (string) $entidadId,
                'descripcion' => $descripcion,
                'valores_anteriores' => self::limpiar($valoresAnteriores),
                'valores_nuevos' => self::limpiar($valoresNuevos),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'created_at' => now(),
                
                // ⭐ Campos nuevos:
                'nivel_confianza' => $nivelConfianza,
                'razon_cambio' => $razonCambio,
                'severidad' => $severidad,
            ];
            
            // Solo cardiólogo puede agregar valoración médica
            if ($usuarioActual && $usuarioActual->hasRole('CARDIOLOGO')) {
                $arrayAuditoria['valoracion_medico'] = $valoracionMedico;
                $arrayAuditoria['cardiologo_id'] = $cardiologo_id ?? Auth::id();
            }
            
            Auditoria::create($arrayAuditoria);
        } catch (Throwable $e) {
            // La auditoria no debe bloquear la operacion principal.
            // Puedes loguear el error si lo deseas: \Log::error($e->getMessage());
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

    /**
     * Valida si el usuario actual puede agregar valoración médica
     * 
     * @return bool true si es cardiólogo, false en caso contrario
     */
    public static function puedeValorar(): bool
    {
        $usuario = Auth::user() ?? \App\Models\User::find(Session::get('user.id'));
        return $usuario?->hasRole('CARDIOLOGO') ?? false;
    }

    /**
     * Obtiene el rol del usuario actual
     * 
     * @return string|null Nombre del rol o null
     */
    public static function obtenerRolActual(): ?string
    {
        $usuario = Auth::user() ?? \App\Models\User::find(Session::get('user.id'));
        return $usuario?->roles()->first()?->name;
    }
}

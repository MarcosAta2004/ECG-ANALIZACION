<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareRol
{
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        if (! Session::has('user')) {
            return redirect()->route('login');
        }

        $rolActual = Session::get('user.role');

        if (! $rolActual && Session::get('user.id')) {
            $usuario = Usuario::with('rol')->find(Session::get('user.id'));
            $rolActual = $usuario?->rol?->nombre;

            if ($usuario) {
                Session::put('user', [
                    'id' => $usuario->id,
                    'email' => $usuario->email,
                    'name' => $usuario->name,
                    'role_id' => $usuario->role_id,
                    'role' => $rolActual,
                ]);
            }
        }

        $rolesNormalizados = array_map(fn ($rol) => mb_strtolower($rol, 'UTF-8'), $rolesPermitidos);
        $rolNormalizado = mb_strtolower((string) $rolActual, 'UTF-8');

        if (! in_array($rolNormalizado, $rolesNormalizados, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para realizar esta accion.',
                ], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a este modulo.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareAutenticacion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('user')) {
            return redirect()->route('login');
        }

        $usuario = Usuario::with('rol')->find(Session::get('user.id'));
        if (! $usuario || $usuario->getAttribute('estado') === false || $usuario->getAttribute('estado') === 0) {
            Session::forget('user');
            return redirect()->route('login');
        }

        Session::put('user', [
            'id' => $usuario->id,
            'email' => $usuario->email,
            'name' => $usuario->name,
            'role_id' => $usuario->role_id,
            'role' => $usuario->rol?->nombre,
        ]);

        return $next($request);
    }
}

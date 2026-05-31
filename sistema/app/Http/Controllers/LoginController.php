<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ServicioAuditoria;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function iniciarSesion(Request $request)
    {
        // Soporte flexible para 'usuario' o 'name' para pruebas rápidas
        $loginField = $request->has('usuario') ? 'usuario' : ($request->has('name') ? 'name' : 'usuario');

        $request->validate([
            $loginField => 'required',
            'password' => 'required'
        ]);

        $credentials = [
            'usuario' => $request->input($loginField),
            'password' => $request->input('password')
        ];

        $user = User::where('usuario', $credentials['usuario'])
                    ->orWhere('name', $credentials['usuario'])
                    ->first();

        if ($user && $user->estado != 1) {
            return response()->json([
                'error' => 'Cuenta desactivada',
                'message' => 'Su cuenta está desactivada. Contacte al administrador.'
            ], 403);
        }

        if (Auth::attempt($credentials)) {
            $authenticatedUser = Auth::user();
            $request->session()->put('user', [
                'id' => $authenticatedUser?->id,
                'email' => $authenticatedUser?->email,
                'name' => $authenticatedUser?->name,
                'role_id' => $authenticatedUser?->rol_id,
                'role' => $authenticatedUser?->rolesa?->name ?? '',
            ]);
            ServicioAuditoria::registrar('login', 'Autenticacion', 'users', Auth::id(), 'Inicio de sesion exitoso.', null, ['email' => $user->email ?? $user->usuario], $request);
            $request->session()->regenerate();
            
            return response()->json([
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user->id,
                    'usuario' => $user->usuario,
                    'name' => $user->name,
                    'role' => $authenticatedUser?->rolesa?->name ?? '',
                ],
                'redirect_hint' => '/dashboard'
            ]);
        }

        return response()->json([
            'error' => 'Credenciales incorrectas',
            'message' => 'El usuario o contraseña no coinciden.'
        ], 401);
    }

    public function logout(Request $request)
    {
        ServicioAuditoria::registrar('logout', 'Autenticacion', 'users', Auth::id(), 'Cierre de sesion.', null, null, $request);
        Auth::logout();

        // Invalida la sesión actual
        $request->session()->invalidate();

        // Regenera el token CSRF
        $request->session()->regenerateToken();

        // Redirige al inicio o login
        return redirect()->route('login')->with([
            'ok' => 'enabled',
            'message' => 'Sesión cerrada correctamente',
            'alert' => 'success'
        ]);
    }
}

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
        $request->validate([
            'usuario' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('usuario', 'password');

        $user = User::where('usuario', $credentials['usuario'])->first();

        if ($user && $user->estado != 1) {
            return back()->withErrors([
                'usuario' => 'Su cuenta está desactivada. Contacte al administrador.'
            ]);
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
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Credenciales incorrectas.'
        ]);
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

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
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginValue = $request->input('login');

        // Buscar usuario por campo 'login' o por 'email'
        $user = User::where('login', $loginValue)
                    ->orWhere('email', $loginValue)
                    ->first();

        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('login'))
                ->with('error', 'El usuario o contraseña no coinciden.');
        }

        if ($user->estado != 1) {
            return redirect()->back()
                ->withInput($request->only('login'))
                ->with('error', 'Su cuenta está desactivada. Contacte al administrador.');
        }

        $credentials = [
            'login'    => $loginValue,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            $authenticatedUser = Auth::user();
            $request->session()->put('user', [
                'id'      => $authenticatedUser?->id,
                'login'   => $authenticatedUser?->login,
                'role_id' => $authenticatedUser?->rol_id,
                'role'    => $authenticatedUser?->rolesa?->name ?? '',
            ]);
            ServicioAuditoria::registrar('login', 'Autenticacion', 'users', Auth::id(), 'Inicio de sesion exitoso.', null, ['login' => $user->login], $request);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return redirect()->back()
            ->withInput($request->only('login'))
            ->with('error', 'El usuario o contraseña no coinciden.');
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

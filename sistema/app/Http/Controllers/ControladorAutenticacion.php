<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\ServicioAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ControladorAutenticacion extends Controlador
{
    public function showLogin()
    {
        if (Session::has('user')) {
            return redirect()->route('dashboard');
        }
        return view('autenticacion.inicio-sesion');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:4'],
        ], [
            'email.required'    => 'Por favor, ingresa tu correo electrónico.',
            'email.email'       => 'Por favor, ingresa un correo electrónico válido.',
            'password.required' => 'Por favor, ingresa tu contraseña.',
            'password.min'      => 'La contraseña debe tener al menos 4 caracteres.',
        ]);

        $user = Usuario::with('rol')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            ServicioAuditoria::registrar(
                'login_fallido',
                'Autenticacion',
                'users',
                null,
                'Intento de inicio de sesion fallido para ' . $request->email,
                null,
                ['email' => $request->email],
                $request
            );

            return back()->withErrors([
                'email' => 'Credenciales incorrectas.',
            ])->withInput($request->only('email'));
        }

        if ($user->getAttribute('estado') === false || $user->getAttribute('estado') === 0) {
            ServicioAuditoria::registrar(
                'login_bloqueado',
                'Autenticacion',
                'users',
                $user->id,
                'Intento de inicio de sesion con usuario inactivo.',
                null,
                ['email' => $user->email],
                $request,
                $user->id
            );

            return back()->withErrors([
                'email' => 'El usuario se encuentra inactivo.',
            ])->withInput($request->only('email'));
        }

        Session::put('user', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role_id' => $user->role_id,
            'role' => $user->rol?->nombre,
        ]);

        ServicioAuditoria::registrar(
            'login',
            'Autenticacion',
            'users',
            $user->id,
            'Inicio de sesion exitoso.',
            null,
            ['email' => $user->email, 'rol' => $user->rol?->nombre],
            $request,
            $user->id
        );

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        ServicioAuditoria::registrar(
            'logout',
            'Autenticacion',
            'users',
            Session::get('user.id'),
            'Cierre de sesion.',
            null,
            ['email' => Session::get('user.email')],
            $request
        );

        Session::forget('user');
        return redirect()->route('login');
    }
}

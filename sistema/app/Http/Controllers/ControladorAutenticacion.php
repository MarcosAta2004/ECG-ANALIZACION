<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
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

        $user = Usuario::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas.',
            ])->withInput($request->only('email'));
        }

        Session::put('user', [
            'id'    => $user->id,
            'email' => $user->email,
            'name'  => $user->name,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login');
    }
}

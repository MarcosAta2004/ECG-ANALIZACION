<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role'   => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:all,active,inactive'],
            'tab'    => ['nullable', 'string', 'in:usuarios,roles'],
        ]);

        $filters = [
            'search' => $validated['search'] ?? '',
            'role'   => $validated['role']   ?? '',
            'status' => $validated['status'] ?? 'all',
            'tab'    => $validated['tab']    ?? 'usuarios',
        ];

        // Consulta de Usuarios con filtros
        $query = User::query()->with('rolesa')->withCount('roles'); // Ajustado según tu esquema

        if ($filters['search']) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['role']) {
            $query->where('rol_id', $filters['role']);
        }

        if ($filters['status'] !== 'all') {
            $query->where('estado', $filters['status'] === 'active' ? 1 : 0);
        }

        $usuarios = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Datos para Roles
        $roles = Role::withCount('users')->get();
        $rolesActivos = Role::all(); // O filtrar por estado si tienes esa columna

        // Estadísticas para el dashboard superior de la vista
        $stats = [
            'usuarios' => User::count(),
            'activos'  => User::where('estado', 1)->count(),
            'inactivos'=> User::where('estado', 0)->count(),
            'roles'    => Role::count(),
        ];

        return view('usuarios', compact('usuarios', 'roles', 'rolesActivos', 'filters', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:100',
            'email'    => 'required|email|unique:users,email',
            'role_id'  => 'required|exists:roles,id',
            'password' => 'required|min:6|confirmed',
        ]);

        $usuario = User::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'password'=> $request->password,
            'rol_id'  => $request->role_id,
            'estado'  => $request->estado ?? 1,
        ]);

        return redirect()->route('usuario.index')->with([
            'status'  => 'success',
            'message' => 'Usuario creado correctamente.',
            'data'    => $usuario->name
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'    => 'required|max:100',
            'email'   => 'required|email|unique:users,email,' . $usuario->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = $request->only(['name', 'email', 'role_id', 'estado']);
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        return redirect()->route('usuario.index')->with([
            'status'  => 'success',
            'message' => 'Usuario actualizado correctamente.',
            'data'    => $usuario->name
        ]);
    }

    public function destroy(User $usuario)
    {
        $usuario->update(['estado' => 0]);
        return redirect()->route('usuario.index')->with([
            'status'  => 'warning',
            'message' => 'Usuario desactivado.',
            'data'    => $usuario->name
        ]);
    }

    public function activar(User $usuario)
    {
        $usuario->update(['estado' => 1]);
        return redirect()->route('usuario.index')->with([
            'status'  => 'success',
            'message' => 'Usuario activado.',
            'data'    => $usuario->name
        ]);
    }
}

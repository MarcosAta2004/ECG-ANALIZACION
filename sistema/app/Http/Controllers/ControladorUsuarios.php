<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ServicioAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ControladorUsuarios extends Controlador
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'integer', 'exists:roles,role_id'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'tab' => ['nullable', 'in:usuarios,roles'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $role = $validated['role'] ?? null;
        $status = $validated['status'] ?? 'all';

        $query = Usuario::query()
            ->with('rol')
            ->withCount('analisisEcg');

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($role) {
            $query->where('role_id', $role);
        }

        match ($status) {
            'active' => $query->where('estado', true),
            'inactive' => $query->where('estado', false),
            default => null,
        };

        $usuarios = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $roles = Rol::query()
            ->withCount('usuarios')
            ->orderBy('nombre')
            ->get();

        $rolesActivos = $roles->where('estado', true)->values();

        $stats = [
            'usuarios' => Usuario::count(),
            'activos' => Usuario::where('estado', true)->count(),
            'inactivos' => Usuario::where('estado', false)->count(),
            'roles' => Rol::count(),
        ];

        $filters = [
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'tab' => $validated['tab'] ?? 'usuarios',
        ];

        return view('usuarios', compact('usuarios', 'roles', 'rolesActivos', 'stats', 'filters'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'estado' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $usuario = Usuario::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => $validated['password'],
            'estado' => $request->boolean('estado', true),
        ]);

        ServicioAuditoria::registrar(
            'crear',
            'Usuarios',
            'users',
            $usuario->id,
            'Creacion de usuario.',
            null,
            $usuario->only(['id', 'name', 'email', 'role_id', 'estado']),
            $request
        );

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function updateUser(Request $request, Usuario $usuario)
    {
        $anteriores = $usuario->only(['id', 'name', 'email', 'role_id', 'estado']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'estado' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $usuario->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'estado' => $request->boolean('estado'),
        ]);

        if (! empty($validated['password'])) {
            $usuario->password = $validated['password'];
        }

        $usuario->save();
        $nuevos = $usuario->only(['id', 'name', 'email', 'role_id', 'estado']);

        if ((int) Session::get('user.id') === (int) $usuario->id) {
            $usuario->load('rol');

            Session::put('user', [
                'id' => $usuario->id,
                'email' => $usuario->email,
                'name' => $usuario->name,
                'role_id' => $usuario->role_id,
                'role' => $usuario->rol?->nombre,
            ]);
        }

        ServicioAuditoria::registrar(
            'actualizar',
            'Usuarios',
            'users',
            $usuario->id,
            'Actualizacion de usuario.',
            $anteriores,
            $nuevos + ['password_actualizada' => ! empty($validated['password'])],
            $request
        );

        return redirect()
            ->route('usuarios.index', $request->only('search', 'role', 'status', 'tab'))
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $rol = Rol::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => $request->boolean('estado', true),
        ]);

        ServicioAuditoria::registrar(
            'crear',
            'Usuarios',
            'roles',
            $rol->role_id,
            'Creacion de rol.',
            null,
            $rol->only(['role_id', 'nombre', 'descripcion', 'estado']),
            $request
        );

        return redirect()
            ->route('usuarios.index', ['tab' => 'roles'])
            ->with('status', 'Rol creado correctamente.');
    }

    public function updateRole(Request $request, Rol $rol)
    {
        $anteriores = $rol->only(['role_id', 'nombre', 'descripcion', 'estado']);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('roles', 'nombre')->ignore($rol->role_id, 'role_id')],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $rol->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => $request->boolean('estado'),
        ]);

        ServicioAuditoria::registrar(
            'actualizar',
            'Usuarios',
            'roles',
            $rol->role_id,
            'Actualizacion de rol.',
            $anteriores,
            $rol->only(['role_id', 'nombre', 'descripcion', 'estado']),
            $request
        );

        return redirect()
            ->route('usuarios.index', ['tab' => 'roles'])
            ->with('status', 'Rol actualizado correctamente.');
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'Ingresa un correo electronico valido.',
            'max.string' => 'El campo :attribute no debe superar :max caracteres.',
            'min.string' => 'El campo :attribute debe tener al menos :min caracteres.',
            'unique' => 'Ya existe un registro con este :attribute.',
            'exists' => 'El :attribute seleccionado no existe.',
            'confirmed' => 'La confirmacion de :attribute no coincide.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo',
            'role_id' => 'rol',
            'password' => 'contrasena',
            'nombre' => 'nombre',
            'descripcion' => 'descripcion',
            'estado' => 'estado',
        ];
    }
}

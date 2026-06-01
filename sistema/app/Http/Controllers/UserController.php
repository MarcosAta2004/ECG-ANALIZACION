<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TipoDocumentoIdentidad;
use Illuminate\Http\Request;
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

        $query = User::query()->with('rolesa')->withCount('roles');

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('login', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nombres', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('numero_documento', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['role']) {
            $query->where('rol_id', $filters['role']);
        }

        if ($filters['status'] !== 'all') {
            $query->where('estado', $filters['status'] === 'active' ? 1 : 0);
        }

        $usuarios = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $roles       = Role::withCount('users')->get();
        $rolesActivos = Role::all();
        $tiposDoc    = TipoDocumentoIdentidad::all();

        $stats = [
            'usuarios' => User::count(),
            'activos'  => User::where('estado', 1)->count(),
            'inactivos'=> User::where('estado', 0)->count(),
            'roles'    => Role::count(),
        ];

        return view('usuarios.index', compact('usuarios', 'roles', 'rolesActivos', 'tiposDoc', 'filters', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'login'                       => 'required|string|max:100|unique:users,login',
            'nombres'                     => 'required|string|max:100',
            'apellido_paterno'            => 'nullable|string|max:100',
            'apellido_materno'            => 'nullable|string|max:100',
            'tipo_documento_identidad_id' => 'nullable|exists:tipo_documento_identidades,id',
            'numero_documento'            => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->tipo_documento_identidad_id) {
                        $tipo = TipoDocumentoIdentidad::find($request->tipo_documento_identidad_id);
                        if ($tipo) {
                            $len = strlen($value);
                            if ($tipo->minimo && $len < $tipo->minimo) {
                                $fail("El número de documento debe tener al menos {$tipo->minimo} caracteres.");
                            }
                            if ($tipo->maximo && $len > $tipo->maximo) {
                                $fail("El número de documento no debe exceder los {$tipo->maximo} caracteres.");
                            }
                        }
                    }
                },
            ],
            'rol_id'                      => 'required|exists:roles,id',
            'password'                    => 'required|min:6',
        ]);

        $usuario = User::create([
            'login'                       => $request->login,
            'nombres'                     => $request->nombres,
            'apellido_paterno'            => $request->apellido_paterno,
            'apellido_materno'            => $request->apellido_materno,
            'tipo_documento_identidad_id' => $request->tipo_documento_identidad_id,
            'numero_documento'            => $request->numero_documento,
            'rol_id'                      => $request->rol_id,
            'password'                    => $request->password,
            'estado'                      => $request->has('estado') ? 1 : 0,
        ]);

        $rol = Role::find($request->rol_id);
        if ($rol) {
            $usuario->assignRole($rol->name);
        }

        return redirect()->route('usuarios.index')->with([
            'status'  => 'success',
            'message' => 'Usuario creado correctamente.',
            'data'    => $usuario->login,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'login'                       => 'required|string|max:100|unique:users,login,' . $usuario->id,
            'nombres'                     => 'required|string|max:100',
            'apellido_paterno'            => 'nullable|string|max:100',
            'apellido_materno'            => 'nullable|string|max:100',
            'tipo_documento_identidad_id' => 'nullable|exists:tipo_documento_identidades,id',
            'numero_documento'            => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->tipo_documento_identidad_id) {
                        $tipo = TipoDocumentoIdentidad::find($request->tipo_documento_identidad_id);
                        if ($tipo) {
                            $len = strlen($value);
                            if ($tipo->minimo && $len < $tipo->minimo) {
                                $fail("El número de documento debe tener al menos {$tipo->minimo} caracteres.");
                            }
                            if ($tipo->maximo && $len > $tipo->maximo) {
                                $fail("El número de documento no debe exceder los {$tipo->maximo} caracteres.");
                            }
                        }
                    }
                },
            ],
            'rol_id'                      => 'required|exists:roles,id',
        ]);

        $data = [
            'login'                       => $request->login,
            'nombres'                     => $request->nombres,
            'apellido_paterno'            => $request->apellido_paterno,
            'apellido_materno'            => $request->apellido_materno,
            'tipo_documento_identidad_id' => $request->tipo_documento_identidad_id,
            'numero_documento'            => $request->numero_documento,
            'rol_id'                      => $request->rol_id,
            'estado'                      => $request->has('estado') ? 1 : 0,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        // Sincronizar rol Spatie
        $rol = Role::find($request->rol_id);
        if ($rol) {
            $usuario->syncRoles([$rol->name]);
        }

        return redirect()->route('usuarios.index')->with([
            'status'  => 'success',
            'message' => 'Usuario actualizado correctamente.',
            'data'    => $usuario->login,
        ]);
    }

    public function destroy(User $usuario)
    {
        $usuario->update(['estado' => 0]);
        return redirect()->route('usuarios.index')->with([
            'status'  => 'warning',
            'message' => 'Usuario desactivado.',
            'data'    => $usuario->login,
        ]);
    }

    public function activar(User $usuario)
    {
        $usuario->update(['estado' => 1]);
        return redirect()->route('usuarios.index')->with([
            'status'  => 'success',
            'message' => 'Usuario activado.',
            'data'    => $usuario->login,
        ]);
    }
}

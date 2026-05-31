<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CentroCosto;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {



        // $areas = Area::get();

        $roles = Role::get();

        $query = User::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->whereRaw(
                "CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) ILIKE ?",
                ['%' . $searchTerm . '%']
            );
        }

        $usuarios = $query->orderBy('id', 'desc')->paginate(5);
        $usuarios->appends(['search' => $request->input('search')]);
        /*$users = User::with('rolesa')
            ->select(
                'id',
                DB::raw("CONCAT(users.nombres,' ' ,
                users.apellido_paterno, ' ', users.apellido_materno) 
        AS nombre_completo"),
                'users.rol_id',
                'users.estado',
                'users.numero_documento',

                'users.created_at'
            );*/

        // return ($users);
        /*if ($request->ajax()) {
            return datatables()->of($users)
                ->toJson();
        }*/



        return view('usuarios.index', compact('roles', 'usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $roles = Role::get();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|max:100',
            'apellido_paterno' => 'required|max:100',
            'apellido_materno' => 'required|max:100',
            'rol_id' => 'required|exists:roles,id',
            'login' => 'required',
            'password' => 'required|max:50|min:4',

        ]);

        $usuario = new User();
        $usuario->nombres = strtoupper($request->nombres);
        $usuario->apellido_paterno = strtoupper($request->apellido_paterno);
        $usuario->apellido_materno = strtoupper($request->apellido_materno);
        $usuario->password = $request->password;
        $usuario->numero_documento = $request->numero_documento ?? NULL;


        $usuario->rol_id = $request->rol_id;
        $usuario->usuario = $request->login ?? NULL;
        $usuario->save(); // No es necesario poner Bycrit ya que en el Modelo hay un metodo
        // que encripta todo los datos enviados en un Input con name password.


        $usuario->roles()->sync([$request->rol_id]);


        return redirect()->route('usuario.index')->with([
            'ok' => 'enabled',
            'message' => 'Se acaba de guardar correctamente el registro de',
            'alert' => 'success',
            'data' => $usuario->nombres . ' ' . $usuario->apellido_paterno
        ]);
    }


    public function show(User $usuario)
    {
        return view('usuarios.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User $usuario
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        $user_roles = $usuario->roles->pluck('id')->toArray(); // Obtiene los IDs de los roles asignados al usuario
        $roles = Role::get();
        return view('usuarios.edit', compact('user_roles', 'usuario', 'roles'));
    }
    public function editrol(User $usuario)
    {

        $roles = Role::all();

        return view('usuarios.role', compact('usuario', 'roles'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User $usuario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'nombres' => 'required|max:100',
            'apellido_paterno' => 'required|max:100',
            'apellido_materno' => 'required|max:100',
            'rol_id' => 'required',
            'login' => 'required'
        ]);

        $rol_id = $request->rol_id;

        $usuario->update($request->except(['password']));

        $usuario->update([
            $usuario->nombres = strtoupper($request->nombres),
            $usuario->apellido_paterno = strtoupper($request->apellido_paterno),
            $usuario->apellido_materno = strtoupper($request->apellido_materno),
            $usuario->rol_id = $request->rol_id,
            $usuario->usuario = $request->login ?? NULL,
            $usuario->numero_documento = $request->numero_documento ?? NULL,
            // que encripta todo los datos enviados en un Input con name password.
        ]);
        if ($request->password != '') {
            $usuario->update([
                $usuario->password = $request->password,
            ]);
        }
       
        $usuario->roles()->sync([$rol_id]);

        return redirect()->route('usuario.index')->with([
            'ok' => 'enabled',
            'message' => 'Se acaba de actualizar correctamente el registro de',
            'alert' => 'success',
            'data' => $usuario->nombres . ' ' . $usuario->apellido_paterno
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $usuario
     * @return \Illuminate\Http\RedirectResponse
     */

    public function updaterol(Request $request, User $usuario)
    {
        $request->validate([
            'roles' => 'required'
        ]);

        $usuario->roles()->sync($request->roles);

        return redirect()->route('usuario.index');
    }
    public function destroy(User $usuario)
    {
        $usuario->estado = 2;

        $usuario->save();
        return redirect()->route('usuario.index')->with([
            'ok' => 'enabled',
            'message' => 'Se acaba de deshabilitar el usuario',
            'alert' => 'danger',
            'data' => $usuario->nombres . ' ' . $usuario->apellido_paterno
        ]);
    }

    public function activar(User $usuario)
    {
        $usuario->estado = 1;
        $usuario->save();
        return redirect()->route('usuario.index')->with([
            'ok' => 'enabled',
            'message' => 'Se acaba de habilitar el usuario',
            'alert' => 'primary',
            'data' => $usuario->nombres . ' ' . $usuario->apellido_paterno
        ]);
    }
}

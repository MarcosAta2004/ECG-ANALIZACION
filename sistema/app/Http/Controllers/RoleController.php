<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{

    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permisos = Permission::orderBy('name')->get();
        return view('seguridad.roles.index', compact('roles', 'permisos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'permissions' => 'required'
        ], [
            'name.required' => 'el nombre es requerido',
            'permissions.required' => 'Debe elegir al menos un permiso'
        ]);

        $role = Role::create([
            'name' => strtoupper($request->name)
        ]);

        $role->permissions()->attach($request->permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with([
            'ok' => 'enabled',
            'alert' => 'success',
            'message' => "Rol {$role->name} registrado correctamente",
            'data' => $role->name,
            'form' => 'create'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $permissions = Permission::all();


        return view('roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required',
            'permissions' => 'required'
        ], [
            'name.required' => 'el nombre es requerido',
            'permissions.required' => 'Debe elegir al menos un permiso'
        ]);

        $role->update([
            'name' => strtoupper($request->name)
        ]);

        $role->permissions()->sync($request->permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with([
            'ok' => 'enabled',
            'alert' => 'success',
            'message' => "Rol {$role->name} actualizado correctamente",
            'data' => $role->name,
            'form' => 'update'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with([
            'ok' => 'enabled',
            'alert' => 'success',
            'message' => "Rol {$role->name} eliminado correctamente",
            'data' => $role->name,
            'form' => 'delete'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Muestra el listado de roles con el conteo de usuarios asignados.
     */
    public function index()
    {
        $roles = Role::withCount('usuarios')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Almacena un nuevo rol en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,nombre'],
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique'   => 'Este rol ya se encuentra registrado.',
        ]);

        Role::create([
            'nombre' => trim($request->nombre),
        ]);

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Actualiza el nombre de un rol existente.
     */
    public function update(Request $request, Role $role)
    {
        // Proteger el rol de Administrador General para evitar modificaciones que rompan el sistema
        if ($role->id === 1 || $role->nombre === 'Administrador General') {
            return redirect()->back()->with('error', 'El rol Administrador General no se puede modificar.');
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,nombre,' . $role->id],
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique'   => 'Ya existe otro rol con este nombre.',
        ]);

        $role->update([
            'nombre' => trim($request->nombre),
        ]);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Elimina un rol si no tiene usuarios asignados.
     */
    public function destroy(Role $role)
    {
        // Impide eliminar el Administrador General
        if ($role->id === 1 || $role->nombre === 'Administrador General') {
            return redirect()->back()->with('error', 'El rol Administrador General no se puede eliminar.');
        }

        // Validar si el rol tiene usuarios vinculados antes de borrar
        if ($role->usuarios()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado con éxito.');
    }

    public function create()
    {
        return view('roles.create');
    }

    public function edit(Role $role)
    {
        // Proteger el rol de Administrador General
        if ($role->id === 1 || $role->nombre === 'Administrador General') {
            return redirect()->route('roles.index')->with('error', 'El rol Administrador General no se puede modificar.');
        }

        return view('roles.edit', compact('role'));
    }
}
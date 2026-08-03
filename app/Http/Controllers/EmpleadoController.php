<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    /**
     * Lista todos los empleados del sistema.
     */
    public function index()
    {
        $empleados = User::with(['sucursal', 'role'])->latest()->get();
        return view('empleados.index', compact('empleados'));
    }

    /**
     * Muestra el formulario para registrar un nuevo empleado.
     */
    public function create()
    {
        $roles = Role::all();
        $sucursales = Sucursal::where('activa', true)->get();
        
        // Agrupamos los permisos por módulo para organizar la UI en checkboxes
        $permisosGrouped = Permiso::all()->groupBy('modulo');

        return view('empleados.create', compact('roles', 'sucursales', 'permisosGrouped'));
    }

    /**
     * Guarda un nuevo empleado en la base de datos y le asigna sus permisos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'required|string|min:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'rol_id'      => 'required|exists:roles,id',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permisos,id',
        ]);

        // Crear Empleado
        $empleado = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'sucursal_id' => $request->sucursal_id,
            'rol_id'      => $request->rol_id,
            'activo'      => $request->has('activo'),
        ]);

        // Sincronizar Permisos seleccionados en la tabla pivote 'permiso_user'
        if ($request->has('permisos')) {
            $empleado->permisos()->sync($request->permisos);
        }

        return redirect()->route('empleados.index')->with('success', 'Empleado creado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un empleado.
     */
    public function edit(User $empleado)
    {
        $roles = Role::all();
        $sucursales = Sucursal::where('activa', true)->get();
        $permisosGrouped = Permiso::all()->groupBy('modulo');

        // Obtener array con los IDs de permisos que el empleado ya tiene asignados
        $userPermisos = $empleado->permisos()->pluck('permisos.id')->toArray();

        return view('empleados.edit', compact('empleado', 'roles', 'sucursales', 'permisosGrouped', 'userPermisos'));
    }

    /**
     * Actualiza los datos y permisos de un empleado.
     */
    public function update(Request $request, User $empleado)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($empleado->id)],
            'password'    => 'nullable|string|min:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'rol_id'      => 'required|exists:roles,id',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permisos,id',
        ]);

        $empleado->name        = $request->name;
        $empleado->email       = $request->email;
        $empleado->sucursal_id = $request->sucursal_id;
        $empleado->rol_id      = $request->rol_id;
        $empleado->activo      = $request->has('activo');

        // Actualizar contraseña solo si se ingresó una nueva
        if ($request->filled('password')) {
            $empleado->password = Hash::make($request->password);
        }

        $empleado->save();

        // Actualizar Permisos
        $empleado->permisos()->sync($request->permisos ?? []);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente.');
    }

    /**
     * Elimina un empleado.
     */
    public function destroy(User $empleado)
    {
        // Evitar eliminar al Administrador principal por seguridad
        if ($empleado->email === 'admin@llantas.com') {
            return redirect()->route('empleados.index')->with('error', 'No se puede eliminar al Administrador Principal.');
        }

        $empleado->delete();

        return redirect()->route('empleados.index')->with('success', 'Empleado eliminado del sistema.');
    }
}
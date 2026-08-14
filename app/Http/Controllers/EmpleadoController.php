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
     * Lista solo los empleados ACTIVOS del sistema.
     * El Administrador Principal siempre aparece primero.
     */
    public function index()
    {
        $empleados = User::with(['sucursal', 'role', 'permisos'])
            ->where('activo', true)
            ->orderByRaw("CASE WHEN email = 'admin@llantas.com' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('empleados.index', compact('empleados'));
    }

    /**
     * Lista los empleados deshabilitados (activo = false).
     */
    public function inactivos()
    {
        $empleados = User::with(['sucursal', 'role'])
            ->where('activo', false)
            ->orderByRaw("CASE WHEN email = 'admin@llantas.com' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('empleados.inactivos', compact('empleados'));
    }

    /**
     * Muestra el formulario para registrar un nuevo empleado.
     */
    public function create()
    {
        $roles = Role::all();
        $sucursales = Sucursal::where('activa', true)->get();
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

        $empleado = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'sucursal_id' => $request->sucursal_id,
            'rol_id'      => $request->rol_id,
            'activo'      => $request->has('activo'),
        ]);

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
        
        // Cargar los permisos agrupados para mostrarlos en la vista de edición
        $permisosGrouped = Permiso::all()->groupBy('modulo');

        return view('empleados.edit', compact('empleado', 'roles', 'sucursales', 'permisosGrouped'));
    }

    /**
     * Actualiza los datos de un empleado y sus permisos.
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

        if ($request->filled('password')) {
            $empleado->password = Hash::make($request->password);
        }

        $empleado->save();

        // Guardar/Actualizar los permisos seleccionados
        $empleado->permisos()->sync($request->permisos ?? []);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente.');
    }

    /**
     * Actualiza ÚNICAMENTE los permisos del empleado (usado por el modal "Configurar").
     */
    public function updatePermisos(Request $request, User $empleado)
    {
        $request->validate([
            'permisos'   => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $empleado->permisos()->sync($request->permisos ?? []);

        return redirect()->route('empleados.index')->with('success', "Permisos de {$empleado->name} actualizados correctamente.");
    }

    /**
     * Alterna el estado activo/inactivo de un empleado.
     */
    public function toggleStatus(User $empleado)
    {
        if ($empleado->email === 'admin@llantas.com') {
            return redirect()->back()->with('error', 'No se puede deshabilitar al Administrador Principal.');
        }

        $empleado->activo = ! $empleado->activo;
        $empleado->save();

        $estado = $empleado->activo ? 'habilitado' : 'deshabilitado';

        return redirect()->back()->with('success', "Empleado {$estado} correctamente.");
    }

    /**
     * Elimina un empleado.
     */
    public function destroy(User $empleado)
    {
        if ($empleado->email === 'admin@llantas.com') {
            return redirect()->route('empleados.index')->with('error', 'No se puede eliminar al Administrador Principal.');
        }

        $empleado->delete();

        return redirect()->route('empleados.index')->with('success', 'Empleado eliminado del sistema.');
    }
}
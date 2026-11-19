<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /** El rol con acceso total solo se protege por su nombre (no por su número de id). */
    public const ROL_ADMIN = EmpleadoController::ROL_ADMIN;

    private function esProtegido(Role $role): bool
    {
        return $role->nombre === self::ROL_ADMIN;
    }

    private function mensajes(string $unico): array
    {
        return [
            'nombre.required' => 'Escribe el nombre del rol.',
            'nombre.max'      => 'El nombre del rol no puede pasar de 50 letras.',
            'nombre.unique'   => $unico,
        ];
    }

    /**
     * Lista de roles con cuántos empleados tiene cada uno.
     */
    public function index()
    {
        $roles = Role::withCount('usuarios')->orderBy('id')->get();

        return view('roles.index', [
            'roles'         => $roles,
            'totalRoles'    => $roles->count(),
            'totalUsuarios' => (int) $roles->sum('usuarios_count'),
            'sinUsuarios'   => $roles->where('usuarios_count', 0)->count(),
            'rolAdmin'      => self::ROL_ADMIN,
        ]);
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        // Se quitan los espacios sobrantes ANTES de revisar si el nombre ya existe
        $request->merge(['nombre' => trim((string) $request->input('nombre'))]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,nombre'],
        ], $this->mensajes('Ya existe un rol con ese nombre.'));

        Role::create(['nombre' => $datos['nombre']]);

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        if ($this->esProtegido($role)) {
            return redirect()->route('roles.index')->with('error', 'El rol Administrador General no se puede modificar.');
        }

        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        if ($this->esProtegido($role)) {
            return redirect()->route('roles.index')->with('error', 'El rol Administrador General no se puede modificar.');
        }

        $request->merge(['nombre' => trim((string) $request->input('nombre'))]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('roles', 'nombre')->ignore($role->id)],
        ], $this->mensajes('Ya existe otro rol con ese nombre.'));

        $role->update(['nombre' => $datos['nombre']]);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Elimina un rol solo si ningún empleado (activo o no) lo tiene.
     */
    public function destroy(Role $role)
    {
        if ($this->esProtegido($role)) {
            return redirect()->route('roles.index')->with('error', 'El rol Administrador General no se puede eliminar.');
        }

        $usuarios = $role->usuarios()->count();

        if ($usuarios > 0) {
            return redirect()->route('roles.index')->with(
                'error',
                "No se puede eliminar el rol {$role->nombre} porque lo tienen {$usuarios} empleado(s). Cámbiales el rol primero."
            );
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    /** Correo del Administrador Principal: no se puede deshabilitar, eliminar ni cambiarle correo o rol. */
    public const ADMIN_PRINCIPAL = 'admin@llantas.com';

    /** Nombre del rol con acceso total al sistema. */
    public const ROL_ADMIN = 'Administrador General';

    /** Tablas donde un empleado deja historial (si aparece en alguna, no se elimina). */
    private const HISTORIAL = [
        'ventas'                => 'user_id',
        'cortes_caja'           => 'user_id',
        'cotizaciones'          => 'usuario_id',
        'movimientos_inventario' => 'usuario_id',
    ];

    /* =====================================================================
       AYUDAS
       ===================================================================== */

    private function esAdmin(?User $usuario): bool
    {
        return $usuario !== null && $usuario->tieneRol(self::ROL_ADMIN);
    }

    private function esPrincipal(User $empleado): bool
    {
        return $empleado->email === self::ADMIN_PRINCIPAL;
    }

    /** Roles que la persona que usa el sistema puede asignar (solo un administrador asigna "Administrador General"). */
    private function rolesAsignables()
    {
        $roles = Role::orderBy('nombre')->get();

        if (! $this->esAdmin(auth()->user())) {
            $roles = $roles->reject(fn (Role $rol) => $rol->nombre === self::ROL_ADMIN)->values();
        }

        return $roles;
    }

    /** De una lista de ids de empleados, devuelve los que ya tienen ventas, cortes, cotizaciones o movimientos. */
    private function idsConHistorial(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $conHistorial = [];

        foreach (self::HISTORIAL as $tabla => $columna) {
            $conHistorial = array_merge(
                $conHistorial,
                DB::table($tabla)->whereIn($columna, $ids)->distinct()->pluck($columna)->all()
            );
        }

        return array_values(array_unique(array_map('intval', $conHistorial)));
    }

    /** Respuesta cuando un usuario que no es administrador intenta tocar a un administrador. */
    private function soloAdministrador()
    {
        return redirect()->route('empleados.index')
            ->with('error', 'Solo un Administrador General puede hacer cambios a otro administrador.');
    }

    /** Consulta base con búsqueda por nombre o correo; el Administrador Principal siempre va primero. */
    private function consulta(bool $activo, string $busqueda)
    {
        $consulta = User::query()->where('activo', $activo);

        if ($busqueda !== '') {
            $like = '%' . addcslashes($busqueda, '%_\\') . '%';
            $consulta->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)->orWhere('email', 'like', $like);
            });
        }

        return $consulta;
    }

    /* =====================================================================
       LISTAS
       ===================================================================== */

    /**
     * Lista los empleados ACTIVOS (con búsqueda y tarjetas).
     */
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('q', ''));

        $base = $this->consulta(true, $busqueda);

        // Las tarjetas cuentan todo el filtro, no solo la página
        $totalActivos   = (clone $base)->count();
        $totalAdmins    = (clone $base)->whereHas('role', fn ($q) => $q->where('nombre', self::ROL_ADMIN))->count();
        $totalInactivos = User::where('activo', false)->count();
        $totalRoles     = Role::count();

        $empleados = (clone $base)
            ->with(['sucursal', 'role', 'permisos'])
            ->orderByRaw('CASE WHEN email = ? THEN 0 ELSE 1 END', [self::ADMIN_PRINCIPAL])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $permisosGrouped = Permiso::orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');

        return view('empleados.index', [
            'empleados'            => $empleados,
            'busqueda'             => $busqueda,
            'totalActivos'         => $totalActivos,
            'totalAdmins'          => $totalAdmins,
            'totalInactivos'       => $totalInactivos,
            'totalRoles'           => $totalRoles,
            'permisosGrouped'      => $permisosGrouped,
            'puedeGestionarAdmins' => $this->esAdmin(auth()->user()),
            'rolAdmin'             => self::ROL_ADMIN,
            'adminPrincipal'       => self::ADMIN_PRINCIPAL,
        ]);
    }

    /**
     * Lista los empleados deshabilitados (activo = false).
     */
    public function inactivos(Request $request)
    {
        $busqueda = trim((string) $request->query('q', ''));

        $base = $this->consulta(false, $busqueda);

        $totalInactivos = (clone $base)->count();
        $totalActivos   = User::where('activo', true)->count();

        $empleados = (clone $base)
            ->with(['sucursal', 'role'])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('empleados.inactivos', [
            'empleados'            => $empleados,
            'busqueda'             => $busqueda,
            'totalInactivos'       => $totalInactivos,
            'totalActivos'         => $totalActivos,
            'conHistorial'         => $this->idsConHistorial($empleados->pluck('id')->all()),
            'puedeGestionarAdmins' => $this->esAdmin(auth()->user()),
            'rolAdmin'             => self::ROL_ADMIN,
        ]);
    }

    /* =====================================================================
       ALTA Y EDICIÓN
       ===================================================================== */

    public function create()
    {
        $roles = $this->rolesAsignables();
        $sucursales = Sucursal::where('activa', true)->orderBy('nombre')->get();
        $permisosGrouped = Permiso::orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');

        return view('empleados.create', compact('roles', 'sucursales', 'permisosGrouped'));
    }

    /** Mensajes de validación en español. */
    private function mensajes(): array
    {
        return [
            'name.required'        => 'Escribe el nombre del empleado.',
            'name.max'             => 'El nombre es demasiado largo (máximo 255 letras).',
            'email.required'       => 'Escribe el correo del empleado.',
            'email.email'          => 'Escribe un correo válido.',
            'email.unique'         => 'Ya hay un empleado con ese correo.',
            'email.max'            => 'El correo es demasiado largo.',
            'password.required'    => 'Escribe una contraseña.',
            'password.min'         => 'La contraseña debe tener al menos 8 caracteres.',
            'sucursal_id.required' => 'Elige una sucursal.',
            'sucursal_id.exists'   => 'La sucursal elegida no existe.',
            'rol_id.required'      => 'Elige un rol.',
            'rol_id.exists'        => 'El rol elegido no existe.',
            'permisos.*.exists'    => 'Alguno de los permisos elegidos no existe.',
        ];
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users,email',
            'password'    => 'required|string|min:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'rol_id'      => 'required|exists:roles,id',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permisos,id',
        ], $this->mensajes());

        // Solo un administrador puede crear a otro administrador
        $rol = Role::find($datos['rol_id']);
        if ($rol->nombre === self::ROL_ADMIN && ! $this->esAdmin($request->user())) {
            return back()->withInput()->withErrors([
                'rol_id' => 'Solo un Administrador General puede asignar ese rol.',
            ]);
        }

        $empleado = User::create([
            'name'        => $datos['name'],
            'email'       => $datos['email'],
            'password'    => Hash::make($datos['password']),
            'sucursal_id' => $datos['sucursal_id'],
            'rol_id'      => $datos['rol_id'],
            'activo'      => $request->has('activo'),
        ]);

        $empleado->permisos()->sync($datos['permisos'] ?? []);

        return redirect()->route('empleados.index')->with('success', 'Empleado creado correctamente.');
    }

    public function edit(User $empleado)
    {
        if ($this->esAdmin($empleado) && ! $this->esAdmin(auth()->user())) {
            return $this->soloAdministrador();
        }

        $roles = $this->rolesAsignables();
        $sucursales = Sucursal::where('activa', true)->orderBy('nombre')->get();
        $permisosGrouped = Permiso::orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');

        return view('empleados.edit', compact('empleado', 'roles', 'sucursales', 'permisosGrouped'));
    }

    public function update(Request $request, User $empleado)
    {
        $actor = $request->user();

        if ($this->esAdmin($empleado) && ! $this->esAdmin($actor)) {
            return $this->soloAdministrador();
        }

        $esPrincipal = $this->esPrincipal($empleado);
        $esYo        = $actor && $actor->id === $empleado->id;

        // Al Administrador Principal no se le cambia el correo ni el rol
        if ($esPrincipal) {
            $request->merge([
                'email'  => $empleado->email,
                'rol_id' => $empleado->rol_id,
            ]);
        }

        $datos = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($empleado->id)],
            'password'    => 'nullable|string|min:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'rol_id'      => 'required|exists:roles,id',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permisos,id',
        ], $this->mensajes());

        // Solo un administrador puede dar el rol de administrador
        $rol = Role::find($datos['rol_id']);
        if ($rol->nombre === self::ROL_ADMIN && ! $this->esAdmin($actor)) {
            return back()->withInput()->withErrors([
                'rol_id' => 'Solo un Administrador General puede asignar ese rol.',
            ]);
        }

        $empleado->name        = $datos['name'];
        $empleado->email       = $datos['email'];
        $empleado->sucursal_id = $datos['sucursal_id'];
        $empleado->rol_id      = $datos['rol_id'];

        // El Administrador Principal y la propia persona no pueden quedar deshabilitados desde aquí
        $empleado->activo = ($esPrincipal || $esYo) ? true : $request->has('activo');

        if (! empty($datos['password'])) {
            $empleado->password = Hash::make($datos['password']);
        }

        $empleado->save();

        $empleado->permisos()->sync($datos['permisos'] ?? []);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente.');
    }

    /**
     * Actualiza ÚNICAMENTE los permisos del empleado (ventana "Configurar").
     */
    public function updatePermisos(Request $request, User $empleado)
    {
        if ($this->esAdmin($empleado) && ! $this->esAdmin($request->user())) {
            return $this->soloAdministrador();
        }

        $datos = $request->validate([
            'permisos'   => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ], $this->mensajes());

        $empleado->permisos()->sync($datos['permisos'] ?? []);

        return redirect()->route('empleados.index')->with('success', "Permisos de {$empleado->name} actualizados correctamente.");
    }

    /* =====================================================================
       HABILITAR / DESHABILITAR / ELIMINAR
       ===================================================================== */

    public function toggleStatus(Request $request, User $empleado)
    {
        if ($this->esPrincipal($empleado)) {
            return redirect()->back()->with('error', 'No se puede deshabilitar al Administrador Principal.');
        }

        if ($request->user() && $request->user()->id === $empleado->id) {
            return redirect()->back()->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }

        if ($this->esAdmin($empleado) && ! $this->esAdmin($request->user())) {
            return redirect()->back()->with('error', 'Solo un Administrador General puede hacer cambios a otro administrador.');
        }

        $empleado->activo = ! $empleado->activo;
        $empleado->save();

        $estado = $empleado->activo ? 'habilitado' : 'deshabilitado';

        return redirect()->back()->with('success', "Empleado {$estado} correctamente.");
    }

    public function destroy(Request $request, User $empleado)
    {
        if ($this->esPrincipal($empleado)) {
            return redirect()->route('empleados.inactivos')->with('error', 'No se puede eliminar al Administrador Principal.');
        }

        if ($request->user() && $request->user()->id === $empleado->id) {
            return redirect()->route('empleados.inactivos')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($this->esAdmin($empleado) && ! $this->esAdmin($request->user())) {
            return redirect()->route('empleados.inactivos')->with('error', 'Solo un Administrador General puede hacer cambios a otro administrador.');
        }

        // Si ya registró ventas, cortes, cotizaciones o movimientos, se conserva el historial
        if ($this->idsConHistorial([$empleado->id]) !== []) {
            return redirect()->route('empleados.inactivos')->with(
                'error',
                "{$empleado->name} tiene ventas o movimientos registrados y no se puede eliminar para conservar el historial. Déjalo deshabilitado."
            );
        }

        try {
            $empleado->delete();
        } catch (QueryException $e) {
            return redirect()->route('empleados.inactivos')->with(
                'error',
                "No se pudo eliminar a {$empleado->name} porque tiene información relacionada. Déjalo deshabilitado."
            );
        }

        return redirect()->route('empleados.inactivos')->with('success', 'Empleado eliminado del sistema.');
    }
}

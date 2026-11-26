<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SucursalController extends Controller
{
    private function mensajes(string $unico): array
    {
        return [
            'nombre.required' => 'Escribe el nombre de la sucursal.',
            'nombre.max'      => 'El nombre de la sucursal no puede pasar de 100 letras.',
            'nombre.unique'   => $unico,
        ];
    }

    /**
     * Cuentas que dicen si una sucursal ya se usó (empleados, ventas, cajas, cotizaciones,
     * movimientos de inventario o piezas en stock). Una sucursal usada no se puede eliminar.
     */
    private function conteosDeUso(): array
    {
        return [
            'usuarios as usuarios_total_count',
            'ventas',
            'cortesCaja',
            'cotizaciones',
            'movimientosInventario',
            'stocks as stock_con_existencia_count' => fn ($q) => $q->where('cantidad', '>', 0),
        ];
    }

    /** Suma de todo lo que usa la sucursal (necesita los conteos cargados). */
    private function usoTotal(Sucursal $sucursal): int
    {
        return (int) $sucursal->usuarios_total_count
            + (int) $sucursal->ventas_count
            + (int) $sucursal->cortes_caja_count
            + (int) $sucursal->cotizaciones_count
            + (int) $sucursal->movimientos_inventario_count
            + (int) $sucursal->stock_con_existencia_count;
    }

    /**
     * Lista de sucursales con cuántos empleados activos tiene cada una.
     */
    public function index()
    {
        $sucursales = Sucursal::withCount(array_merge([
            'usuarios as empleados_count' => fn ($q) => $q->where('activo', true),
        ], $this->conteosDeUso()))->orderBy('id')->get();

        foreach ($sucursales as $sucursal) {
            $sucursal->en_uso = $this->usoTotal($sucursal) > 0;
        }

        return view('sucursales.index', [
            'sucursales'      => $sucursales,
            'totalSucursales' => $sucursales->count(),
            'activas'         => $sucursales->where('activa', true)->count(),
            'inactivas'       => $sucursales->where('activa', false)->count(),
        ]);
    }

    public function create()
    {
        return view('sucursales.create');
    }

    public function store(Request $request)
    {
        // Se quitan los espacios sobrantes ANTES de revisar si el nombre ya existe
        $request->merge(['nombre' => trim((string) $request->input('nombre'))]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:sucursales,nombre'],
        ], $this->mensajes('Ya existe una sucursal con ese nombre.'));

        Sucursal::create(['nombre' => $datos['nombre'], 'activa' => true]);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        return view('sucursales.edit', compact('sucursal'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $request->merge(['nombre' => trim((string) $request->input('nombre'))]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('sucursales', 'nombre')->ignore($sucursal->id)],
        ], $this->mensajes('Ya existe otra sucursal con ese nombre.'));

        $sucursal->update(['nombre' => $datos['nombre']]);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal actualizada correctamente.');
    }

    /**
     * Elimina una sucursal solo si nunca se usó (por ejemplo, una creada por error).
     */
    public function destroy(Sucursal $sucursal)
    {
        $sucursal->loadCount($this->conteosDeUso());

        if ($this->usoTotal($sucursal) > 0) {
            return redirect()->route('sucursales.index')->with(
                'error',
                "No se puede eliminar {$sucursal->nombre} porque ya tiene empleados, ventas, caja o inventario. Si ya no se usa, desactívala."
            );
        }

        $sucursal->delete();

        return redirect()->route('sucursales.index')->with('success', 'Sucursal eliminada correctamente.');
    }

    /**
     * Activa o desactiva una sucursal. Es lo que se usa cuando una sucursal ya tiene
     * historial de ventas, caja o inventario.
     */
    public function toggle(Sucursal $sucursal)
    {
        if (! $sucursal->activa) {
            $sucursal->update(['activa' => true]);

            return redirect()->route('sucursales.index')->with('success', "La sucursal {$sucursal->nombre} se activó.");
        }

        if (Sucursal::where('activa', true)->count() <= 1) {
            return redirect()->route('sucursales.index')->with('error', 'Debe quedar al menos una sucursal activa.');
        }

        $empleados = $sucursal->usuarios()->where('activo', true)->count();

        if ($empleados > 0) {
            return redirect()->route('sucursales.index')->with(
                'error',
                "No se puede desactivar {$sucursal->nombre} porque tiene {$empleados} empleado(s) activo(s). Cámbiales la sucursal primero."
            );
        }

        $sucursal->update(['activa' => false]);

        return redirect()->route('sucursales.index')->with('success', "La sucursal {$sucursal->nombre} se desactivó.");
    }
}

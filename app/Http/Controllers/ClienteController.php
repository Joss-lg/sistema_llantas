<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('q', ''));

        // Consulta base con la búsqueda: sirve para la lista y para los números de arriba
        $query = Cliente::query();

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $like = '%' . $busqueda . '%';
                $q->where('nombre', 'like', $like)
                  ->orWhere('telefono', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('rfc', 'like', $like)
                  ->orWhere('razon_social', 'like', $like);
            });
        }

        // Números de TODO el filtro (no solo de la página que se ve en pantalla)
        $totalClientes = (clone $query)->count();
        $totalVip = (clone $query)->where('is_vip', true)->count();
        $clientesConCompras = (clone $query)->whereHas('ventas')->count();

        // Ticket promedio real: lo vendido a estos clientes entre el número de compras
        $ventasDelFiltro = Venta::whereIn('cliente_id', (clone $query)->select('clientes.id'));
        $numeroCompras = (clone $ventasDelFiltro)->count();
        $ticketPromedio = $numeroCompras > 0
            ? (float) (clone $ventasDelFiltro)->sum('total') / $numeroCompras
            : 0.0;

        $clientes = $query
            ->withSum('ventas as compras_sum', 'total')
            ->withMax('ventas as ultima_compra', 'fecha')
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact(
            'clientes', 'busqueda', 'totalClientes', 'totalVip', 'clientesConCompras', 'ticketPromedio'
        ));
    }

    public function create()
    {
        // La creación se maneja con un modal en clientes.index.
        // Si alguien entra directo a esta URL, lo mandamos al listado
        // con el modal de nuevo cliente ya abierto.
        return redirect()
            ->route('clientes.index')
            ->with('abrirModalNuevo', true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'         => 'required|string|max:150',
            'telefono'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:150',
            'rfc'            => 'nullable|string|max:20',
            'razon_social'   => 'nullable|string|max:150',
            'uso_cfdi'       => 'nullable|string|max:5',
            'cp'             => 'nullable|string|max:10',
            'regimen_fiscal' => 'nullable|string|max:10',
        ]);

        $validated['is_vip'] = $request->boolean('is_vip');

        Cliente::create($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado con éxito.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->loadSum('ventas as compras_sum', 'total');
        $cliente->loadMax('ventas as ultima_compra', 'fecha');
        $cliente->load(['ventas' => function ($query) {
            $query->latest('fecha')->orderByDesc('id');
        }]);

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        // La edición se maneja con un modal en clientes.index.
        // Si alguien entra directo a esta URL, lo mandamos al listado
        // con los datos del cliente listos para abrir el modal.
        return redirect()
            ->route('clientes.index')
            ->with('clienteEditar', $cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre'         => 'required|string|max:150',
            'telefono'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:150',
            'rfc'            => 'nullable|string|max:20',
            'razon_social'   => 'nullable|string|max:150',
            'uso_cfdi'       => 'nullable|string|max:5',
            'cp'             => 'nullable|string|max:10',
            'regimen_fiscal' => 'nullable|string|max:10',
        ]);

        $validated['is_vip'] = $request->boolean('is_vip');

        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy(Cliente $cliente)
    {
        // Un cliente con compras no se borra: su historial de ventas se conserva
        $compras = $cliente->ventas()->count();

        if ($compras > 0) {
            $texto = $compras === 1 ? '1 compra registrada' : "{$compras} compras registradas";

            return redirect()->route('clientes.index')
                ->with('error', "No se puede eliminar a {$cliente->nombre}: tiene {$texto}. Su historial de ventas se conserva.");
        }

        try {
            $cliente->delete();
        } catch (QueryException $e) {
            Log::warning('No se pudo eliminar el cliente ' . $cliente->id . ': ' . $e->getMessage());

            return redirect()->route('clientes.index')
                ->with('error', "No se puede eliminar a {$cliente->nombre}: tiene otros registros ligados (por ejemplo, cotizaciones).");
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado con éxito.');
    }
}

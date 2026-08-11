<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::withSum('ventas as compras_sum', 'total')
            ->withMax('ventas as ultima_compra', 'fecha')
            ->latest()
            ->paginate(10);

        return view('clientes.index', compact('clientes'));
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
            $query->latest('fecha');
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
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado con éxito.');
    }
}
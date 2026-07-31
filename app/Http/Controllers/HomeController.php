<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Variables requeridas por la vista dashboard (se inicializan vacías o en 0 mientras vinculas tu BD)
        $ventasHoy = 0.00;
        $llantasVendidas = 0;
        $bajoStock = 0;
        $clientesNuevos = 0;
        $ultimasVentas = [];
        $rendimientoSucursales = [];

        return view('dashboard', compact(
            'ventasHoy',
            'llantasVendidas',
            'bajoStock',
            'clientesNuevos',
            'ultimasVentas',
            'rendimientoSucursales'
        ));
    }
}
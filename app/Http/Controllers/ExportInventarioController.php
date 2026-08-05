<?php

namespace App\Http\Controllers;

use App\Exports\InventarioExport;
use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Services\InventarioQueryService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportInventarioController extends Controller
{
    use ResuelveContextoSucursal;

    public function __construct(private InventarioQueryService $inventarioQuery)
    {
    }

    private function productosParaExportar(Request $request)
    {
        $sucursalFiltro = $this->sucursalSeleccionada($request);

        return $this->inventarioQuery->query($request, $sucursalFiltro)
            ->orderBy('marca')->orderBy('medida')
            ->get();
    }

    public function excel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $productos = $this->productosParaExportar($request);
        $nombre = 'inventario_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new InventarioExport($productos), $nombre);
    }

    public function pdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $productos = $this->productosParaExportar($request);
        $fecha = now()->format('d/m/Y H:i');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventario.pdf', compact('productos', 'fecha'));
        $pdf->setPaper('a4', 'portrait');

        $nombre = 'inventario_' . now()->format('Y-m-d_His') . '.pdf';
        return $pdf->download($nombre);
    }
}
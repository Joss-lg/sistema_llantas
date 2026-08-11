<?php

namespace App\Http\Controllers;

use App\Exports\InventarioExport;
use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Sucursal;
use App\Services\InventarioQueryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportInventarioController extends Controller
{
    use ResuelveContextoSucursal;

    public function __construct(private InventarioQueryService $inventarioQuery)
    {
    }

    /**
     * Obtiene los productos filtrados según el contexto de la sucursal y la consulta.
     */
    private function productosParaExportar(Request $request)
    {
        $sucursalFiltro = $this->sucursalSeleccionada($request);

        return $this->inventarioQuery->query($request, $sucursalFiltro)
            ->groupBy('productos.id')
            ->orderBy('marca')
            ->orderBy('medida')
            ->get();
    }

    /**
     * Obtiene el nombre representativo de la sucursal para los encabezados del reporte.
     */
    private function obtenerNombreSucursal(Request $request): string
    {
        $sucursalId = $this->sucursalSeleccionada($request);

        if (!$sucursalId) {
            return 'Todas las Sucursales';
        }

        return Sucursal::find($sucursalId)?->nombre ?? 'Sucursal #' . $sucursalId;
    }

    public function excel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $productos = $this->productosParaExportar($request);
        $nombreSucursal = $this->obtenerNombreSucursal($request);
        $nombreArchivo = 'inventario_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new InventarioExport($productos, $nombreSucursal), $nombreArchivo);
    }

    public function pdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $productos = $this->productosParaExportar($request);
        $nombreSucursal = $this->obtenerNombreSucursal($request);
        $fecha = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('inventario.pdf', compact('productos', 'fecha', 'nombreSucursal'));
        $pdf->setPaper('a4', 'portrait');

        $nombreArchivo = 'inventario_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($nombreArchivo);
    }
}
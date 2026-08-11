<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Services\Importacion\ExcelInventarioImporter;
use Illuminate\Http\Request;

class ImportacionInventarioController extends Controller
{
    use ResuelveContextoSucursal;

    public function __construct(private ExcelInventarioImporter $importer)
    {
    }

    public function form()
    {
        // Enviamos las sucursales disponibles para que la vista renderice el desplegable (si aplica)
        $sucursales = $this->sucursalesDisponibles();

        return view('inventario.importar', compact('sucursales'));
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'sucursal_id'   => 'nullable|exists:sucursales,id',
        ]);

        try {
            // Resolvemos la sucursal respetando el rol del usuario
            $sucursalDestino = $this->sucursalSeleccionada($request);

            $path = $request->file('archivo_excel')->path();
            $productosProcesados = $this->importer->importarDesdeArchivo($path, $sucursalDestino);

            if ($productosProcesados === 0) {
                return back()->with('error', 'No se detectaron productos válidos o los encabezados del archivo no coinciden con las columnas esperadas.');
            }

            return redirect()->route('inventario.index')
                ->with('success', "¡Éxito! Se procesaron correctamente {$productosProcesados} productos en la sucursal seleccionada.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
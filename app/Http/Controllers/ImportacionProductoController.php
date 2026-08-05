<?php

namespace App\Http\Controllers;

use App\Services\Importacion\ExcelInventarioImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportacionInventarioController extends Controller
{
    public function __construct(private ExcelInventarioImporter $importer)
    {
    }

    public function form()
    {
        return view('inventario.importar');
    }

    public function procesar(Request $request)
    {
        $request->validate(['archivo_excel' => 'required|mimes:xlsx,xls,csv,txt|max:10240']);

        try {
            $usuario = Auth::user();
            $sucursalUsuario = $usuario->sucursal_id ?? 1;
            $sucursalDestino = $request->input('sucursal_id', $sucursalUsuario);

            $path = $request->file('archivo_excel')->path();
            $productosProcesados = $this->importer->importarDesdeArchivo($path, $sucursalDestino);

            if ($productosProcesados === 0) {
                return back()->with('error', 'No detectamos productos válidos.');
            }

            return redirect()->route('inventario.index')
                ->with('success', "¡Éxito! Se importaron $productosProcesados productos en la sucursal correspondiente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Sucursal;
use App\Services\Importacion\ExcelInventarioImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportacionInventarioController extends Controller
{
    use ResuelveContextoSucursal;

    private const CARPETA   = 'importaciones';
    private const SESION    = 'importacion_pendiente';
    private const EXTENSIONES = ['xlsx', 'xls', 'csv'];

    public function __construct(private ExcelInventarioImporter $importer)
    {
    }

    public function form()
    {
        // Enviamos las sucursales disponibles para que la vista renderice el desplegable (si aplica)
        $sucursales = $this->sucursalesDisponibles();

        return view('inventario.importar', compact('sucursales'));
    }

    /**
     * Una sola ruta para los tres pasos (así no hace falta un permiso nuevo):
     * previsualizar (por defecto), confirmar o cancelar.
     */
    public function procesar(Request $request)
    {
        $accion = $request->input('accion', 'previsualizar');

        if ($accion === 'confirmar') {
            return $this->confirmar();
        }

        if ($accion === 'cancelar') {
            $this->descartarPendiente();

            return redirect()->route('inventario.importar');
        }

        return $this->previsualizar($request);
    }

    private function previsualizar(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'sucursal_id'   => 'nullable|exists:sucursales,id',
            'modo'          => 'nullable|in:sumar,reemplazar',
        ]);

        // Resolvemos la sucursal respetando el rol del usuario
        $sucursalDestino = $this->sucursalSeleccionada($request);

        if (!$sucursalDestino || !Sucursal::where('id', $sucursalDestino)->where('activa', true)->exists()) {
            return back()->with('error', 'Selecciona una sucursal activa a la que se cargará el inventario.');
        }

        $archivo   = $request->file('archivo_excel');
        $extension = strtolower($archivo->getClientOriginalExtension());

        if (!in_array($extension, self::EXTENSIONES, true)) {
            return back()->with('error', 'El archivo debe ser .xlsx, .xls o .csv.');
        }

        $modo = $request->input('modo') === ExcelInventarioImporter::MODO_REEMPLAZAR
            ? ExcelInventarioImporter::MODO_REEMPLAZAR
            : ExcelInventarioImporter::MODO_SUMAR;

        // Si había una vista previa anterior sin confirmar, se descarta
        $this->descartarPendiente();

        $nombreTemporal = Str::random(40) . '.' . $extension;
        $relativa       = self::CARPETA . '/' . $nombreTemporal;
        Storage::disk('local')->putFileAs(self::CARPETA, $archivo, $nombreTemporal);

        try {
            $analisis = $this->importer->analizar(Storage::disk('local')->path($relativa));
        } catch (\Throwable $e) {
            Log::error('Error al leer el archivo de importación de inventario: ' . $e->getMessage(), ['usuario' => Auth::id()]);
            Storage::disk('local')->delete($relativa);

            return back()->with('error', 'No se pudo leer el archivo. Revisa que sea un Excel o CSV válido.');
        }

        if (!$analisis['encabezado']) {
            Storage::disk('local')->delete($relativa);

            return back()->with('error', 'No se encontraron los encabezados esperados. El archivo necesita MARCA y MEDIDA (o DESCRIPCIÓN) y al menos una columna de PRECIO PÚBLICO, MAYOREO, COSTO o STOCK.');
        }

        if (count($analisis['filas']) === 0) {
            Storage::disk('local')->delete($relativa);

            return back()->with('error', 'El archivo no tiene productos debajo de los encabezados.');
        }

        session([self::SESION => [
            'archivo'  => $nombreTemporal,
            'sucursal' => (int) $sucursalDestino,
            'modo'     => $modo,
            'usuario'  => (int) Auth::id(),
        ]]);

        $vista = $this->armarVistaPrevia($analisis, $archivo->getClientOriginalName(), (int) $sucursalDestino, $modo);
        $sucursales = $this->sucursalesDisponibles();

        return view('inventario.importar', compact('sucursales', 'vista'));
    }

    private function confirmar()
    {
        $pendiente = session(self::SESION);

        if (!$pendiente || (int) ($pendiente['usuario'] ?? 0) !== (int) Auth::id()) {
            return redirect()->route('inventario.importar')
                ->with('error', 'La vista previa ya no está disponible. Sube el archivo otra vez.');
        }

        $relativa = self::CARPETA . '/' . basename((string) $pendiente['archivo']);

        if (!Storage::disk('local')->exists($relativa)) {
            session()->forget(self::SESION);

            return redirect()->route('inventario.importar')
                ->with('error', 'El archivo temporal ya no existe. Sube el archivo otra vez.');
        }

        try {
            $resumen = $this->importer->aplicar(
                Storage::disk('local')->path($relativa),
                (int) $pendiente['sucursal'],
                (string) $pendiente['modo'],
                Auth::id()
            );
        } catch (\Throwable $e) {
            Log::error('Error al importar inventario: ' . $e->getMessage(), ['usuario' => Auth::id()]);
            $this->descartarPendiente();

            return redirect()->route('inventario.importar')
                ->with('error', 'No se pudo importar el archivo. No se guardó ningún cambio. Intenta de nuevo o avisa al administrador del sistema.');
        }

        $this->descartarPendiente();

        $mensaje = "Importación terminada: {$resumen['nuevos']} productos nuevos, {$resumen['actualizados']} actualizados, "
            . "{$resumen['movimientos']} movimientos de stock registrados";

        if ($resumen['omitidas'] > 0) {
            $mensaje .= " y {$resumen['omitidas']} filas omitidas por tener problemas";
        }

        return redirect()->route('inventario.index')->with('success', $mensaje . '.');
    }

    private function descartarPendiente(): void
    {
        $pendiente = session(self::SESION);

        if ($pendiente && !empty($pendiente['archivo'])) {
            Storage::disk('local')->delete(self::CARPETA . '/' . basename((string) $pendiente['archivo']));
        }

        session()->forget(self::SESION);
    }

    private function armarVistaPrevia(array $analisis, string $nombreArchivo, int $sucursalId, string $modo): array
    {
        $filas   = collect($analisis['filas']);
        $buenas  = $filas->where('ok', true);
        $malas   = $filas->where('ok', false);
        $problemas = $filas->filter(fn ($f) => !$f['ok'] || count($f['avisos']) > 0);

        $etiquetas = [
            'marca'       => 'MARCA',
            'medida'      => 'MEDIDA',
            'descripcion' => 'DESCRIPCIÓN',
            'categoria'   => 'CATEGORÍA',
            'publico'     => 'PRECIO PÚBLICO',
            'mayoreo'     => 'MAYOREO',
            'costo'       => 'COSTO',
            'stock'       => 'STOCK',
        ];

        $detectadas = [];
        foreach ($analisis['columnas'] as $col) {
            $detectadas[] = $etiquetas[$col] ?? strtoupper($col);
        }

        // Columnas de datos que el archivo NO trae: se conserva lo que ya tiene cada producto
        $faltantes = [];
        foreach (['publico', 'mayoreo', 'costo', 'stock'] as $col) {
            if (!in_array($col, $analisis['columnas'], true)) {
                $faltantes[] = $etiquetas[$col];
            }
        }

        return [
            'archivo'         => $nombreArchivo,
            'sucursal'        => Sucursal::find($sucursalId)?->nombre ?? 'Sucursal',
            'modo'            => $modo,
            'total'           => $filas->count(),
            'ok'              => $buenas->count(),
            'con_error'       => $malas->count(),
            'con_aviso'       => $buenas->filter(fn ($f) => count($f['avisos']) > 0)->count(),
            'nuevos'          => $buenas->where('existe', false)->count(),
            'actualizados'    => $buenas->where('existe', true)->count(),
            'con_stock'       => $buenas->filter(fn ($f) => $f['stock'] !== null)->count(),
            'sin_stock'       => $buenas->filter(fn ($f) => $f['stock'] === null)->count(),
            'columnas'        => $detectadas,
            'faltantes'       => $faltantes,
            'problemas_total' => $problemas->count(),
            'problemas'       => $problemas->take(200)->values()->all(),
            'muestra'         => $buenas->take(30)->values()->all(),
        ];
    }
}
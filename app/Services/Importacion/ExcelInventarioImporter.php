<?php

namespace App\Services\Importacion;

use App\Models\Producto;
use App\Models\StockSucursal;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;

/**
 * Lógica extraída tal cual de InventarioController::procesarImportacion().
 *
 * ADVERTENCIAS QUE SIGUEN VIGENTES (no las corregí, solo las muevo aquí):
 * - No verifica duplicados: si el mismo archivo se importa dos veces, crea productos repetidos.
 * - La detección de columnas es heurística (busca "descrip", "categ", "mayor", etc. en el texto
 *   de los encabezados). Si el archivo no trae encabezados reconocibles, no importa nada
 *   y no siempre queda claro por qué.
 * - Si no detecta una columna de "stock" explícita, toma el último valor numérico de la fila
 *   (de derecha a izquierda), lo cual puede confundir un precio con una cantidad.
 * - La limpieza de precios con regex no soporta formato "1.200,50" (punto como separador de miles).
 */
class ExcelInventarioImporter
{
    /**
     * @return int cantidad de productos procesados
     */
    public function importarDesdeArchivo(string $path, int $sucursalDestino): int
    {
        $coleccion = (new FastExcel)->withoutHeaders()->import($path);
        $productosProcesados = 0;
        $indices = null;

        DB::beginTransaction();
        try {
            foreach ($coleccion as $fila) {
                $valores = array_values($fila);

                if (!$indices) {
                    $indices = $this->detectarIndicesDeEncabezado($valores);
                    continue;
                }

                $productoCreado = $this->procesarFila($valores, $indices, $sucursalDestino);
                if ($productoCreado) {
                    $productosProcesados++;
                }
            }

            DB::commit();
            return $productosProcesados;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function detectarIndicesDeEncabezado(array $valores): ?array
    {
        $tempIndices = [];
        foreach ($valores as $k => $v) {
            if ($v === null || trim((string) $v) === '') {
                continue;
            }
            $valStr = strtolower(preg_replace(
                '/[\s\r\n]+/',
                '',
                str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], trim((string) $v))
            ));

            if (str_contains($valStr, 'descrip')) $tempIndices['descripcion'] = $k;
            elseif (str_contains($valStr, 'categ') || str_contains($valStr, 'tipo')) $tempIndices['categoria'] = $k;
            elseif (str_contains($valStr, 'mayor') || str_contains($valStr, 'costo')) $tempIndices['mayoreo'] = $k;
            elseif (str_contains($valStr, 'public') || str_contains($valStr, 'menude')) $tempIndices['publico'] = $k;
            elseif (str_contains($valStr, 'stock') || str_contains($valStr, 'actual') || str_contains($valStr, 'cantid')) $tempIndices['stock'] = $k;
        }

        $encabezadoValido = isset($tempIndices['descripcion'])
            && (isset($tempIndices['mayoreo']) || isset($tempIndices['publico']));

        return $encabezadoValido ? $tempIndices : null;
    }

    private function procesarFila(array $valores, array $indices, int $sucursalDestino): bool
    {
        $descIndex = $indices['descripcion'] ?? -1;
        $descripcion = ($descIndex !== -1 && isset($valores[$descIndex])) ? $valores[$descIndex] : '';
        if (empty(trim((string) $descripcion))) {
            return false;
        }

        $catIndex = $indices['categoria'] ?? -1;
        $mayIndex = $indices['mayoreo'] ?? -1;
        $pubIndex = $indices['publico'] ?? -1;

        $categoria = ($catIndex !== -1 && isset($valores[$catIndex])) ? $valores[$catIndex] : 'Llanta';
        $precioMay = ($mayIndex !== -1 && isset($valores[$mayIndex])) ? $valores[$mayIndex] : 0;
        $precioPub = ($pubIndex !== -1 && isset($valores[$pubIndex])) ? $valores[$pubIndex] : 0;

        $stockRaw = $this->extraerStock($valores, $indices);

        $precioMay = (float) preg_replace('/[^0-9\.]/', '', (string) $precioMay);
        $precioPub = (float) preg_replace('/[^0-9\.]/', '', (string) $precioPub);
        $stockLimpio = preg_replace('/[^0-9\.]/', '', (string) $stockRaw);
        $stock = (int) round((float) ($stockLimpio ?: 0));

        [$marca, $medida] = $this->separarMarcaYMedida((string) $descripcion);

        $producto = Producto::create([
            'tipo' => mb_substr((string) $categoria, 0, 50),
            'marca' => mb_strtoupper(mb_substr($marca, 0, 100)),
            'medida' => mb_strtoupper(mb_substr($medida, 0, 100)),
            'descripcion' => mb_substr((string) $descripcion, 0, 255),
            'costo' => $precioMay,
            'precio_mayoreo' => $precioMay,
            'precio_publico' => $precioPub,
            'estado' => true,
        ]);

        StockSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursalDestino,
            'cantidad' => $stock,
            'stock_minimo' => 5,
        ]);

        return true;
    }

    private function extraerStock(array $valores, array $indices): ?string
    {
        if (isset($indices['stock']) && trim((string) ($valores[$indices['stock']] ?? '')) !== '') {
            return $valores[$indices['stock']];
        }

        foreach (array_reverse($valores) as $val) {
            $limpio = trim((string) $val);
            if ($limpio !== '' && is_numeric(preg_replace('/[^0-9\.]/', '', $limpio))) {
                return $limpio;
            }
        }

        return null;
    }

    private function separarMarcaYMedida(string $descripcion): array
    {
        $medida = 'S/M';
        $marca = trim($descripcion);
        $partes = explode(' ', $marca, 2);

        if (count($partes) > 1 && preg_match('/[0-9]/', $partes[0])) {
            $medida = $partes[0];
            $marca = $partes[1];
        }

        return [$marca, $medida];
    }
}
<?php

namespace App\Services\Importacion;

use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;

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
        $sucursalesTotales = Sucursal::pluck('id')->toArray();

        DB::beginTransaction();
        try {
            foreach ($coleccion as $fila) {
                $valores = array_values($fila);

                // Detecta automáticamente la fila de encabezados aunque haya filas vacías o de título
                if (!$indices) {
                    $indices = $this->detectarIndicesDeEncabezado($valores);
                    continue;
                }

                $productoCreado = $this->procesarFila($valores, $indices, $sucursalDestino, $sucursalesTotales);
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

            if (str_contains($valStr, 'marca')) $tempIndices['marca'] = $k;
            elseif (str_contains($valStr, 'medida')) $tempIndices['medida'] = $k;
            elseif (str_contains($valStr, 'descrip')) $tempIndices['descripcion'] = $k;
            elseif (str_contains($valStr, 'categ') || str_contains($valStr, 'tipo')) $tempIndices['categoria'] = $k;
            elseif (str_contains($valStr, 'mayor') || str_contains($valStr, 'costo')) $tempIndices['mayoreo'] = $k;
            elseif (str_contains($valStr, 'public') || str_contains($valStr, 'menude')) $tempIndices['publico'] = $k;
            elseif (str_contains($valStr, 'stock') || str_contains($valStr, 'actual') || str_contains($valStr, 'cantid')) $tempIndices['stock'] = $k;
        }

        $encabezadoValido = (isset($tempIndices['descripcion']) || (isset($tempIndices['marca']) && isset($tempIndices['medida'])))
            && (isset($tempIndices['mayoreo']) || isset($tempIndices['publico']));

        return $encabezadoValido ? $tempIndices : null;
    }

    private function procesarFila(array $valores, array $indices, int $sucursalDestino, array $sucursalesTotales): bool
    {
        // 1. Obtener Marca y Medida
        $marcaIndex = $indices['marca'] ?? -1;
        $medidaIndex = $indices['medida'] ?? -1;
        $descIndex = $indices['descripcion'] ?? -1;

        $descripcionRaw = ($descIndex !== -1 && isset($valores[$descIndex])) ? trim((string)$valores[$descIndex]) : '';

        if ($marcaIndex !== -1 && $medidaIndex !== -1 && isset($valores[$marcaIndex], $valores[$medidaIndex])) {
            $marca = trim((string)$valores[$marcaIndex]);
            $medida = trim((string)$valores[$medidaIndex]);
        } else {
            if (empty($descripcionRaw)) {
                return false;
            }
            [$marca, $medida] = $this->separarMarcaYMedida($descripcionRaw);
        }

        if (empty($marca) || empty($medida)) {
            return false;
        }

        // 2. Obtener Precios y Categoría
        $catIndex = $indices['categoria'] ?? -1;
        $mayIndex = $indices['mayoreo'] ?? -1;
        $pubIndex = $indices['publico'] ?? -1;

        $categoria = ($catIndex !== -1 && isset($valores[$catIndex])) ? $valores[$catIndex] : 'Llanta';
        $precioMay = ($mayIndex !== -1 && isset($valores[$mayIndex])) ? $valores[$mayIndex] : 0;
        $precioPub = ($pubIndex !== -1 && isset($valores[$pubIndex])) ? $valores[$pubIndex] : 0;

        $precioMay = (float) preg_replace('/[^0-9\.]/', '', (string) $precioMay);
        $precioPub = (float) preg_replace('/[^0-9\.]/', '', (string) $precioPub);
        $stockRaw = $this->extraerStock($valores, $indices);
        $stockLimpio = preg_replace('/[^0-9\.]/', '', (string) $stockRaw);
        $stock = (int) round((float) ($stockLimpio ?: 0));

        // 3. Crear o Actualizar Producto (Upsert)
        $producto = Producto::updateOrCreate(
            [
                'marca'  => mb_strtoupper(mb_substr($marca, 0, 100)),
                'medida' => mb_strtoupper(mb_substr($medida, 0, 100)),
            ],
            [
                'tipo'           => mb_substr((string) $categoria, 0, 50),
                'descripcion'    => mb_substr($descripcionRaw ?: "{$marca} {$medida}", 0, 255),
                'costo'          => $precioMay,
                'precio_mayoreo' => $precioMay,
                'precio_publico' => $precioPub,
                'estado'         => true,
            ]
        );

        // 4. Inicializar en todas las sucursales y actualizar la sucursal destino
        foreach ($sucursalesTotales as $sucursalId) {
            $cantidadAsignar = ($sucursalId === $sucursalDestino) ? $stock : 0;

            StockSucursal::updateOrCreate(
                [
                    'producto_id' => $producto->id,
                    'sucursal_id' => $sucursalId,
                ],
                [
                    'cantidad'     => DB::raw("CASE WHEN sucursal_id = {$sucursalDestino} THEN {$cantidadAsignar} ELSE cantidad END"),
                    'stock_minimo' => 5,
                ]
            );
        }

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
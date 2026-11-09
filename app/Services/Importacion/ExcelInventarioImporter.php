<?php

namespace App\Services\Importacion;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class ExcelInventarioImporter
{
    public const MODO_SUMAR      = 'sumar';
    public const MODO_REEMPLAZAR = 'reemplazar';

    // Un stock igual o mayor a este número se marca con aviso (puede ser un precio mal leído)
    private const STOCK_ALTO = 10000;

    /**
     * Lee el archivo y revisa cada fila SIN guardar nada en la base de datos.
     *
     * @return array{encabezado: bool, columnas: array, filas: array}
     */
    public function analizar(string $path): array
    {
        $coleccion = (new FastExcel)->withoutHeaders()->import($path);

        $indices = null;
        $filas   = [];
        $vistos  = [];
        $linea   = 0;

        // Productos que ya existen, para saber si la fila es nueva o una actualización
        $existentes = [];
        foreach (Producto::get(['id', 'marca', 'medida', 'costo', 'precio_publico', 'precio_mayoreo']) as $p) {
            $existentes[$this->clave((string) $p->marca, (string) $p->medida)] = $p;
        }

        foreach ($coleccion as $fila) {
            $linea++;
            $valores = array_values((array) $fila);

            // Detecta la fila de encabezados aunque haya filas de título o vacías antes
            if ($indices === null) {
                $indices = $this->detectarIndicesDeEncabezado($valores);
                continue;
            }

            if ($this->filaVacia($valores)) {
                continue;
            }

            $filas[] = $this->analizarFila($valores, $indices, $linea, $existentes, $vistos);
        }

        return [
            'encabezado' => $indices !== null,
            'columnas'   => $indices !== null ? array_keys($indices) : [],
            'filas'      => $filas,
        ];
    }

    /**
     * Importa el archivo: solo las filas sin errores.
     *
     * @return array{nuevos: int, actualizados: int, movimientos: int, omitidas: int}
     */
    public function aplicar(string $path, int $sucursalDestino, string $modo, ?int $usuarioId): array
    {
        $analisis = $this->analizar($path);

        if (!$analisis['encabezado']) {
            throw new \RuntimeException('Los encabezados del archivo no coinciden con las columnas esperadas.');
        }

        $modo = $modo === self::MODO_REEMPLAZAR ? self::MODO_REEMPLAZAR : self::MODO_SUMAR;
        $sucursalesTotales = Sucursal::pluck('id')->all();
        $resumen = ['nuevos' => 0, 'actualizados' => 0, 'movimientos' => 0, 'omitidas' => 0];

        DB::transaction(function () use ($analisis, $sucursalDestino, $modo, $usuarioId, $sucursalesTotales, &$resumen) {
            foreach ($analisis['filas'] as $fila) {
                if (!$fila['ok']) {
                    $resumen['omitidas']++;
                    continue;
                }

                $this->aplicarFila($fila, $sucursalDestino, $modo, $usuarioId, $sucursalesTotales, $resumen);
            }
        });

        return $resumen;
    }

    private function aplicarFila(array $f, int $sucursalDestino, string $modo, ?int $usuarioId, array $sucursalesTotales, array &$resumen): void
    {
        // 1. Producto: solo se cambian los datos que el Excel sí trae
        $datos = ['estado' => true];

        if ($f['categoria'] !== '') {
            $datos['tipo'] = mb_substr($f['categoria'], 0, 50);
        }
        if ($f['descripcion'] !== '') {
            $datos['descripcion'] = mb_substr($f['descripcion'], 0, 255);
        }
        if ($f['costo'] !== null) {
            $datos['costo'] = $f['costo'];
        }
        if ($f['precio_mayoreo'] !== null) {
            $datos['precio_mayoreo'] = $f['precio_mayoreo'];
        }
        if ($f['precio_publico'] !== null) {
            $datos['precio_publico'] = $f['precio_publico'];
        }

        if ($f['existe']) {
            $producto = Producto::findOrFail($f['producto_id']);
            $producto->update($datos);
            $resumen['actualizados']++;
        } else {
            $producto = Producto::create([
                'marca'          => $f['marca'],
                'medida'         => $f['medida'],
                'tipo'           => $datos['tipo'] ?? 'Llanta',
                'descripcion'    => $datos['descripcion'] ?? mb_substr($f['marca'] . ' ' . $f['medida'], 0, 255),
                'costo'          => $f['costo'] ?? 0,
                'precio_mayoreo' => $f['precio_mayoreo'] ?? 0,
                'precio_publico' => $f['precio_publico'],
                'estado'         => true,
            ]);
            $resumen['nuevos']++;
        }

        // 2. Todas las sucursales tienen registro de stock (si no existe, se crea en 0)
        foreach ($sucursalesTotales as $sucursalId) {
            StockSucursal::firstOrCreate(
                ['producto_id' => $producto->id, 'sucursal_id' => $sucursalId],
                ['cantidad' => 0, 'stock_minimo' => 5]
            );
        }

        // 3. Stock vacío en el Excel = no se toca el stock
        if ($f['stock'] === null) {
            return;
        }

        $stock = StockSucursal::where('producto_id', $producto->id)
            ->where('sucursal_id', $sucursalDestino)
            ->lockForUpdate()
            ->first();

        $actual = (int) $stock->cantidad;
        $nuevo  = (int) $f['stock'];

        if ($modo === self::MODO_SUMAR) {
            $diferencia = $nuevo;
            $motivo     = 'Importación de Excel';
        } else {
            $diferencia = $nuevo - $actual;
            $motivo     = 'Ajuste por importación';
        }

        if ($diferencia === 0) {
            return;
        }

        $stock->cantidad = $actual + $diferencia;
        $stock->save();

        MovimientoInventario::create([
            'producto_id'    => $producto->id,
            'sucursal_id'    => $sucursalDestino,
            'usuario_id'     => $usuarioId,
            'tipo'           => $diferencia > 0 ? 'entrada' : 'salida',
            'cantidad'       => abs($diferencia),
            'motivo'         => $motivo,
            'costo_unitario' => $diferencia > 0 ? $producto->costo : null,
            'observaciones'  => 'Importación de Excel (modo ' . $modo . ')',
            'fecha'          => now(),
        ]);

        $resumen['movimientos']++;
    }

    private function analizarFila(array $v, array $ix, int $linea, array $existentes, array &$vistos): array
    {
        $errores = [];
        $avisos  = [];

        $descripcion = $this->texto($v, $ix['descripcion'] ?? null);
        $categoria   = $this->texto($v, $ix['categoria'] ?? null);

        // Marca y medida: de sus columnas, o separadas de la descripción si el archivo no las trae
        if (isset($ix['marca'], $ix['medida'])) {
            $marca  = $this->texto($v, $ix['marca']);
            $medida = $this->texto($v, $ix['medida']);
        } elseif ($descripcion !== '') {
            [$marca, $medida] = $this->separarMarcaYMedida($descripcion);
        } else {
            $marca  = '';
            $medida = '';
        }

        $marca  = mb_strtoupper(mb_substr($marca, 0, 100));
        $medida = mb_strtoupper(mb_substr($medida, 0, 100));

        if ($marca === '' || $medida === '') {
            $errores[] = 'Falta la marca o la medida';
        }

        // Precios y stock
        [$publico, $errPublico] = $this->numero($v, $ix['publico'] ?? null);
        [$mayoreo, $errMayoreo] = $this->numero($v, $ix['mayoreo'] ?? null);
        [$costo,   $errCosto]   = $this->numero($v, $ix['costo'] ?? null);
        [$stock,   $errStock]   = $this->numero($v, $ix['stock'] ?? null);

        if ($errPublico) $errores[] = 'Precio público ' . $errPublico;
        if ($errMayoreo) $errores[] = 'Mayoreo ' . $errMayoreo;
        if ($errCosto)   $errores[] = 'Costo ' . $errCosto;
        if ($errStock)   $errores[] = 'Stock ' . $errStock;

        if ($publico !== null && $publico <= 0) $errores[] = 'Precio público debe ser mayor a 0';
        if ($mayoreo !== null && $mayoreo <= 0) $errores[] = 'Mayoreo debe ser mayor a 0';
        if ($costo   !== null && $costo   <= 0) $errores[] = 'Costo debe ser mayor a 0';

        if ($stock !== null) {
            if ($stock < 0) {
                $errores[] = 'Stock negativo';
            } elseif (fmod($stock, 1.0) !== 0.0) {
                $errores[] = 'Stock con decimales';
            } elseif ($stock >= self::STOCK_ALTO) {
                $avisos[] = 'Stock muy alto (' . number_format($stock) . '). Revisa que no sea un precio';
            }
        }

        // ¿Existe ya? ¿Está repetida en el archivo?
        $existente = null;
        if ($marca !== '' && $medida !== '') {
            $clave     = $this->clave($marca, $medida);
            $existente = $existentes[$clave] ?? null;

            if (isset($vistos[$clave])) {
                $errores[] = 'Repetida en el archivo (igual que la fila ' . $vistos[$clave] . ')';
            } else {
                $vistos[$clave] = $linea;
            }

            if (!$existente && $publico === null && !$errPublico) {
                $errores[] = 'Producto nuevo sin precio público';
            }

            if (!$existente && ($mayoreo === null || $costo === null)) {
                $avisos[] = 'Producto nuevo: el costo o el mayoreo quedarán en 0';
            }
        }

        // Público menor que mayoreo (con lo que traiga el Excel o, si no, lo que ya tiene el producto)
        $publicoFinal = $publico ?? ($existente ? (float) $existente->precio_publico : null);
        $mayoreoFinal = $mayoreo ?? ($existente ? (float) $existente->precio_mayoreo : null);
        if ($publicoFinal && $mayoreoFinal && $publicoFinal < $mayoreoFinal) {
            $avisos[] = 'El precio público es menor que el mayoreo';
        }

        return [
            'linea'           => $linea,
            'marca'           => $marca,
            'medida'          => $medida,
            'descripcion'     => $descripcion,
            'categoria'       => $categoria,
            'precio_publico'  => $publico,
            'precio_mayoreo'  => $mayoreo,
            'costo'           => $costo,
            'stock'           => $stock !== null ? (int) $stock : null,
            'existe'          => $existente !== null,
            'producto_id'     => $existente?->id,
            'precio_anterior' => $existente ? (float) $existente->precio_publico : null,
            'errores'         => $errores,
            'avisos'          => $avisos,
            'ok'              => count($errores) === 0,
        ];
    }

    private function detectarIndicesDeEncabezado(array $valores): ?array
    {
        $tempIndices = [];
        foreach ($valores as $k => $v) {
            if ($v === null || trim((string) $v) === '') {
                continue;
            }
            // mb_strtolower primero para que también funcione con acentos en MAYÚSCULA (ej. "PÚBLICO")
            $valStr = preg_replace(
                '/[\s\r\n]+/',
                '',
                str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], mb_strtolower(trim((string) $v)))
            );

            if (str_contains($valStr, 'marca')) $tempIndices['marca'] = $k;
            elseif (str_contains($valStr, 'medida')) $tempIndices['medida'] = $k;
            elseif (str_contains($valStr, 'descrip')) $tempIndices['descripcion'] = $k;
            elseif (str_contains($valStr, 'categ') || str_contains($valStr, 'tipo')) $tempIndices['categoria'] = $k;
            elseif (str_contains($valStr, 'mayor')) $tempIndices['mayoreo'] = $k;
            elseif (str_contains($valStr, 'costo')) $tempIndices['costo'] = $k;
            elseif (str_contains($valStr, 'public') || str_contains($valStr, 'menude')) $tempIndices['publico'] = $k;
            elseif (str_contains($valStr, 'stock') || str_contains($valStr, 'actual') || str_contains($valStr, 'cantid')) $tempIndices['stock'] = $k;
        }

        $identifica = isset($tempIndices['descripcion']) || (isset($tempIndices['marca']) && isset($tempIndices['medida']));
        $traeDatos  = isset($tempIndices['publico']) || isset($tempIndices['mayoreo']) || isset($tempIndices['costo']) || isset($tempIndices['stock']);

        return ($identifica && $traeDatos) ? $tempIndices : null;
    }

    /**
     * @return array{0: ?float, 1: ?string} [valor, mensaje de error]
     */
    private function numero(array $v, ?int $i): array
    {
        if ($i === null || !isset($v[$i])) {
            return [null, null];
        }

        $raw = trim((string) $v[$i]);
        if ($raw === '') {
            return [null, null];
        }

        $limpio = str_replace(['$', ',', ' '], '', $raw);
        if (!is_numeric($limpio)) {
            return [null, 'no es un número ("' . mb_substr($raw, 0, 20) . '")'];
        }

        return [(float) $limpio, null];
    }

    private function texto(array $v, ?int $i): string
    {
        if ($i === null || !isset($v[$i])) {
            return '';
        }

        return trim((string) $v[$i]);
    }

    private function filaVacia(array $valores): bool
    {
        foreach ($valores as $val) {
            if ($val !== null && trim((string) $val) !== '') {
                return false;
            }
        }

        return true;
    }

    private function clave(string $marca, string $medida): string
    {
        return mb_strtoupper(trim(mb_substr(trim($marca), 0, 100))) . '|' . mb_strtoupper(trim(mb_substr(trim($medida), 0, 100)));
    }

    private function separarMarcaYMedida(string $descripcion): array
    {
        $partes = explode(' ', trim($descripcion), 2);

        // Solo se acepta si la primera palabra parece una medida (trae números)
        if (count($partes) > 1 && preg_match('/[0-9]/', $partes[0])) {
            return [trim($partes[1]), $partes[0]];
        }

        return ['', ''];
    }
}
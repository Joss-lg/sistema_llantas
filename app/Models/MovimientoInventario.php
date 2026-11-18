<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'usuario_id',
        'tipo',
        'cantidad',
        'motivo',
        'costo_unitario',
        'observaciones',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relación con el producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Relación con la sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    // Quién registró el movimiento (la columna se llama usuario_id)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
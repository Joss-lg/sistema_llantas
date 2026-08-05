<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockSucursal extends Model
{
    protected $table = 'stock_sucursal';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'cantidad',
        'stock_minimo'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
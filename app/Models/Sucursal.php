<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'activa'
    ];

    public function stocks()
    {
        return $this->hasMany(StockSucursal::class, 'sucursal_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'stock_sucursal', 'sucursal_id', 'producto_id')
                    ->withPivot('cantidad', 'stock_minimo')
                    ->withTimestamps();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'tipo',
        'marca',
        'medida',
        'descripcion',
        'costo',
        'precio_publico',
        'precio_mayoreo',
        'estado'
    ];

    // Relación con la tabla pivote de stock
    public function stock()
    {
        return $this->hasMany(StockSucursal::class, 'producto_id');
    }

    // Relación directa a Sucursales mediante la tabla pivote
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'stock_sucursal', 'producto_id', 'sucursal_id')
                    ->withPivot('cantidad', 'stock_minimo')
                    ->withTimestamps();
    }
}
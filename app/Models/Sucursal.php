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

    public function usuarios()
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }

    public function cortesCaja()
    {
        return $this->hasMany(CorteCaja::class, 'sucursal_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'sucursal_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'sucursal_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'stock_sucursal', 'sucursal_id', 'producto_id')
                    ->withPivot('cantidad', 'stock_minimo')
                    ->withTimestamps();
    }
}

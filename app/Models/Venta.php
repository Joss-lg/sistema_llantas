<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $guarded = ['id'];

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Relación con el cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Nueva relación con el Flujo de Caja (El Turno)
    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    use HasFactory;

    protected $table = 'cortes_caja';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'corte_caja_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'corte_caja_id');
    }
}
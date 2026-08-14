<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';
    protected $guarded = ['id'];

    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }
}
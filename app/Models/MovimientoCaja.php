<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    // Con esta línea forzamos a Laravel a buscar el nombre correcto
    protected $table = 'movimientos_caja';
    
    protected $guarded = ['id'];
}
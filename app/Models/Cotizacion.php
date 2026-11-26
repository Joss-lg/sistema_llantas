<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    // La tabla se llama "cotizaciones" (Laravel por sí solo buscaría "cotizacions").
    protected $table = 'cotizaciones';
}

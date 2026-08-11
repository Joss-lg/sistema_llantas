<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'rfc',
        'razon_social',
        'uso_cfdi',
        'cp',
        'regimen_fiscal',
        'is_vip'
    ];

    protected $casts = [
        'is_vip' => 'boolean',
    ];

    // Relación: Un cliente tiene muchas ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
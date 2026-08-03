<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
  protected $fillable = ['nombre', 'ruta', 'modulo'];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'permiso_user');
    }
}

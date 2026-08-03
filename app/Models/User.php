<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'sucursal_id',
        'rol_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    /* =====================================================================
       RELACIONES
       ===================================================================== */

    /**
     * Relación con la Sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Relación con el Rol (Usamos 'role' o 'rol' según como lo invoques)
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    /**
     * Relación directa de Permisos (vía tabla pivote permiso_user)
     */
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_user', 'user_id', 'permiso_id');
    }

    /* =====================================================================
       MÉTODOS DE ACCESO / PERMISOS
       ===================================================================== */

    /**
     * Método requerido por CheckPermiso Middleware
     */
    public function tienePermiso($nombreRuta)
    {
        // 1. Acceso libre si es el Administrador principal (ID 1) o tiene rol de Administrador
        if ((int)$this->id === 1 || ($this->role && $this->role->nombre === 'Administrador')) {
            return true;
        }

        // 2. Consulta de permisos específicos en la tabla pivote
        return $this->permisos()->where('ruta', $nombreRuta)->exists();
    }
}
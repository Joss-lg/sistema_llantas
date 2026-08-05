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

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_user', 'user_id', 'permiso_id');
    }

    /* =====================================================================
       MÉTODOS DE ACCESO, ROLES Y PERMISOS
       ===================================================================== */

    /**
     * Evalúa si el usuario tiene un rol específico por nombre (o lista de nombres).
     */
    public function tieneRol(...$roles): bool
    {
        if (! $this->role) {
            return false;
        }

        return in_array($this->role->nombre, $roles);
    }

    /**
     * Evalúa permisos específicos por nombre de ruta para el Middleware CheckPermiso
     */
    public function tienePermiso($nombreRuta): bool
    {
        // 1. Acceso total para Administrador General o ID 1
        if ((int)$this->id === 1 || ($this->role && $this->role->nombre === 'Administrador General')) {
            return true;
        }

        // 2. Consulta de permisos específicos asignados
        return $this->permisos()->where('ruta', $nombreRuta)->exists();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'telefono',
        'passw',
    ];

    protected $hidden = [
        'passw',
        'remember_token',
    ];

    /**
     * Get the password for authentication
     */
    public function getAuthPassword()
    {
        return $this->passw;
    }

    /**
     * Get the name of the unique identifier for the user
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Relación muchos a muchos con Rol
     */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'id_usuario', 'id_rol')
                    ->withPivot('detalle');
    }

    /**
     * Relación con Bitacora
     */
    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'id_usuario');
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($roleDescription)
    {
        return $this->roles()->where('descripcion', $roleDescription)->exists();
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission($permissionDescription)
    {
        return $this->roles()
            ->whereHas('permisos', function ($query) use ($permissionDescription) {
                $query->where('descripcion', $permissionDescription);
            })
            ->exists();
    }

    /**
     * Obtener todos los permisos del usuario
     */
    public function getAllPermissions()
    {
        return Permiso::whereIn('id', function ($query) {
            $query->select('id_permiso')
                ->from('rol_permisos')
                ->whereIn('id_rol', function ($subQuery) {
                    $subQuery->select('id_rol')
                        ->from('rol_usuario')
                        ->where('id_usuario', $this->id);
                });
        })->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'sigla',
    ];

    /**
     * Accessor para nrogrupo (alias de sigla para compatibilidad)
     */
    public function getNrogrupoAttribute()
    {
        return $this->sigla;
    }

    /**
     * Relación con Materias
     */
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'grupo_materia', 'id_grupo', 'sigla_materia');
    }

    /**
     * Relación con Docentes (Usuarios)
     */
    public function docentes()
    {
        return $this->belongsToMany(Usuario::class, 'grupo_usuario', 'id_grupo', 'id_usuario');
    }

    /**
     * Relación con Horarios
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_grupo', 'id');
    }
}

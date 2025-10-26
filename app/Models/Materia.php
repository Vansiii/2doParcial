<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materia';
    protected $primaryKey = 'sigla';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'sigla',
        'nombre',
        'id_semestre',
    ];

    /**
     * Relación con Semestre
     */
    public function semestre()
    {
        return $this->belongsTo(Semestre::class, 'id_semestre');
    }

    /**
     * Relación con Grupos
     */
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_materia', 'sigla_materia', 'id_grupo');
    }

    /**
     * Relación con Horarios
     */
    public function horarios()
    {
        return $this->belongsToMany(Horario::class, 'horario_mat', 'sigla_materia', 'id_horario');
    }

    /**
     * Relación con Carreras
     */
    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'carrera_materia', 'sigla_materia', 'cod_carrera');
    }
}

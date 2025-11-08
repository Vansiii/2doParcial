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
     * Relación con GrupoMateria
     */
    public function grupoMaterias()
    {
        return $this->hasMany(GrupoMateria::class, 'sigla_materia', 'sigla');
    }

    /**
     * Relación con Grupos a través de GrupoMateria
     */
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_materia', 'sigla_materia', 'id_grupo')
            ->withPivot('id_docente');
    }

    /**
     * Obtener docente para un grupo específico
     */
    public function getDocenteParaGrupo($idGrupo)
    {
        $grupoMateria = GrupoMateria::where('sigla_materia', $this->sigla)
            ->where('id_grupo', $idGrupo)
            ->first();
        
        return $grupoMateria ? $grupoMateria->docente : null;
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'horaini',
        'horafin',
        'tiempoh',
        'nroaula',
        'id_grupo',
    ];

    /**
     * Relación con Días
     */
    public function dias()
    {
        return $this->belongsToMany(Dia::class, 'dia_horario', 'id_horario', 'id_dia');
    }

    /**
     * Relación con Materias
     */
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'horario_mat', 'id_horario', 'sigla_materia');
    }

    /**
     * Obtener docentes asignados a este horario
     * Se obtienen a través de las materias (Materia_Usuario)
     */
    public function docentes()
    {
        // Los docentes se obtienen de las materias asignadas a este horario
        return Usuario::whereHas('roles', function($q) {
            $q->where('descripcion', 'Docente');
        })->whereIn('id', function($query) {
            $query->select('id_usuario')
                ->from('materia_usuario')
                ->whereIn('sigla_materia', function($subQuery) {
                    $subQuery->select('sigla_materia')
                        ->from('horario_mat')
                        ->where('id_horario', $this->id);
                });
        });
    }

    /**
     * Relación con Aula
     */
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'nroaula', 'nroaula');
    }

    /**
     * Relación con Grupo
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id');
    }

    /**
     * Accessor para hora_inicio (mapea horaini)
     */
    public function getHoraInicioAttribute()
    {
        return $this->attributes['horaini'];
    }

    /**
     * Accessor para hora_fin (mapea horafin)
     */
    public function getHoraFinAttribute()
    {
        return $this->attributes['horafin'];
    }
}

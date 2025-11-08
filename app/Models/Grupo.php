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
     * Relación con GrupoMateria
     */
    public function grupoMaterias()
    {
        return $this->hasMany(GrupoMateria::class, 'id_grupo', 'id');
    }

    /**
     * Obtener materias del grupo a través de GrupoMateria
     * NOTA: No usamos belongsToMany porque grupo_materia requiere id_docente (NOT NULL)
     */
    public function getMaterialesAttribute()
    {
        return Materia::whereIn('sigla', function($query) {
            $query->select('sigla_materia')
                ->from('grupo_materia')
                ->where('id_grupo', $this->id);
        })->get();
    }

    /**
     * Obtener docente para una materia específica
     */
    public function getDocenteParaMateria($siglaMateria)
    {
        $grupoMateria = GrupoMateria::where('id_grupo', $this->id)
            ->where('sigla_materia', $siglaMateria)
            ->first();
        
        return $grupoMateria ? $grupoMateria->docente : null;
    }

    /**
     * Relación con Horarios
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_grupo', 'id');
    }
}

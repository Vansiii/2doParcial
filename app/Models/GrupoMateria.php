<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoMateria extends Model
{
    protected $table = 'grupo_materia';
    
    public $timestamps = false;
    
    // Clave primaria compuesta
    protected $primaryKey = ['id_grupo', 'sigla_materia', 'id_docente'];
    public $incrementing = false;

    protected $fillable = [
        'id_grupo',
        'sigla_materia',
        'id_docente'
    ];

    /**
     * Relación con Grupo
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id');
    }

    /**
     * Relación con Materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'sigla_materia', 'sigla');
    }

    /**
     * Relación con Usuario (Docente)
     */
    public function docente()
    {
        return $this->belongsTo(Usuario::class, 'id_docente', 'id');
    }

    /**
     * Override para usar clave primaria compuesta
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the value for save query
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}

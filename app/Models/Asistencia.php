<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';
    public $timestamps = false;
    
    // Clave compuesta
    protected $primaryKey = ['fecha', 'id_horario', 'id_usuario'];
    public $incrementing = false;

    protected $fillable = [
        'fecha',
        'hora',
        'tipo',
        'id_horario',
        'id_usuario',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Relación con Usuario (Docente)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Relación con Horario
     */
    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario');
    }

    /**
     * Set the keys for a save update query.
     * Para soportar clave primaria compuesta
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the value of the model's primary key.
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dia extends Model
{
    use HasFactory;

    protected $table = 'dia';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'descripcion',
    ];

    /**
     * Accessor para nombre (por compatibilidad con vistas)
     */
    public function getNombreAttribute()
    {
        return $this->descripcion;
    }

    /**
     * Relación con Horarios
     */
    public function horarios()
    {
        return $this->belongsToMany(Horario::class, 'dia_horario', 'id_dia', 'id_horario');
    }
}

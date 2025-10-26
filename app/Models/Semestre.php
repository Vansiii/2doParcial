<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    use HasFactory;

    protected $table = 'semestre';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'abreviatura',
        'fechaini',
        'fechafin',
    ];

    protected $casts = [
        'fechaini' => 'date',
        'fechafin' => 'date',
    ];

    /**
     * Accessor para periodo (por compatibilidad con vistas)
     */
    public function getPeriodoAttribute()
    {
        return $this->abreviatura;
    }

    /**
     * Relación con Materias
     */
    public function materias()
    {
        return $this->hasMany(Materia::class, 'id_semestre');
    }
}

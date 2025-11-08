<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carrera';
    protected $primaryKey = 'cod';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cod',
        'nombre',
    ];

    /**
     * Relación con Materias a través de Carrera_Materia
     */
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'carrera_materia', 'cod_carrera', 'sigla_materia');
    }
}

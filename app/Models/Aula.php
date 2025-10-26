<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aula';
    protected $primaryKey = 'nroaula';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nroaula',
        'capacidad',
        'piso',
        'id_modulo',
    ];

    /**
     * Relación con Modulo
     */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'codigo');
    }

    /**
     * Relación con Horarios (a través de tabla intermedia si existe)
     * Nota: La BD no tiene relación directa, esto es conceptual
     */
    public function horarios()
    {
        // Esta relación puede no existir directamente en la BD actual
        return $this->hasMany(Horario::class, 'nroaula', 'nroaula');
    }
}

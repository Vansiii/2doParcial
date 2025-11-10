<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaPeriodo extends Model
{
    use HasFactory;

    protected $table = 'materia_periodo';
    protected $primaryKey = 'id';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'sigla_materia',
        'id_periodo',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Relación con Materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'sigla_materia', 'sigla');
    }

    /**
     * Relación con Período Académico
     */
    public function periodo()
    {
        return $this->belongsTo(Semestre::class, 'id_periodo', 'id');
    }

    /**
     * Scope para materias activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para un período específico
     */
    public function scopePeriodo($query, $idPeriodo)
    {
        return $query->where('id_periodo', $idPeriodo);
    }

    /**
     * Scope para período activo
     */
    public function scopePeriodoActivo($query)
    {
        return $query->whereHas('periodo', function($q) {
            $q->where('activo', true);
        });
    }
}

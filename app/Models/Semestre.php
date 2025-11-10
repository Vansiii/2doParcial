<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    use HasFactory;

    protected $table = 'periodo_academico';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'abreviatura',
        'fechaini',
        'fechafin',
        'gestion',
        'periodo',
        'activo',
    ];

    protected $casts = [
        'fechaini' => 'date',
        'fechafin' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Accessor para periodo (por compatibilidad con vistas)
     */
    public function getPeriodoAttribute()
    {
        return $this->abreviatura;
    }

    /**
     * Relación con Materias a través de Materia_Periodo
     */
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_periodo', 'id_periodo', 'sigla_materia')
            ->withPivot('activa', 'created_at')
            ->withTimestamps();
    }

    /**
     * Relación con Grupos
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'id_periodo', 'id');
    }

    /**
     * Scope para obtener el período académico activo
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por gestión
     */
    public function scopeGestion($query, $gestion)
    {
        return $query->where('gestion', $gestion);
    }

    /**
     * Verificar si está activo
     */
    public function estaActivo()
    {
        return $this->activo === true;
    }

    /**
     * Obtener nombre completo del período
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->periodo}-{$this->gestion}";
    }
}

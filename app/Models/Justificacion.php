<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificacion extends Model
{
    use HasFactory;

    protected $table = 'justificacion';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'descripcion',
        'archivo',
        'estado',
        'observaciones',
        'id_usuario',
        'aprobado_por',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_aprobacion' => 'datetime',
    ];

    /**
     * Relación con Usuario (docente que solicita)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    /**
     * Relación con Usuario (quien aprobó o rechazó)
     */
    public function aprobadoPor()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por', 'id');
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para justificaciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    /**
     * Scope para justificaciones de un usuario específico
     */
    public function scopeDelUsuario($query, $idUsuario)
    {
        return $query->where('id_usuario', $idUsuario);
    }

    /**
     * Verificar si está pendiente
     */
    public function estaPendiente()
    {
        return $this->estado === 'Pendiente';
    }

    /**
     * Verificar si está aprobada
     */
    public function estaAprobada()
    {
        return $this->estado === 'Aprobada';
    }

    /**
     * Verificar si está rechazada
     */
    public function estaRechazada()
    {
        return $this->estado === 'Rechazada';
    }

    /**
     * Obtener badge color según estado
     */
    public function getBadgeColorAttribute()
    {
        return match($this->estado) {
            'Pendiente' => 'warning',
            'Aprobada' => 'success',
            'Rechazada' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtener ícono según estado
     */
    public function getIconoAttribute()
    {
        return match($this->estado) {
            'Pendiente' => 'fa-clock',
            'Aprobada' => 'fa-check-circle',
            'Rechazada' => 'fa-times-circle',
            default => 'fa-question-circle',
        };
    }

    /**
     * Obtener nombre del archivo sin ruta
     */
    public function getNombreArchivoAttribute()
    {
        return $this->archivo ? basename($this->archivo) : null;
    }
}

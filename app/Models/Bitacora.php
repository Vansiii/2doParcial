<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'ip',
        'accion',
        'estado',
        'detalle',
        'id_usuario',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'boolean',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Registrar una acción en la bitácora
     */
    public static function registrar($accion, $estado, $detalle, $idUsuario = null)
    {
        return self::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => $accion,
            'estado' => $estado,
            'detalle' => $detalle,
            'id_usuario' => $idUsuario ?? auth()->id(),
        ]);
    }
}

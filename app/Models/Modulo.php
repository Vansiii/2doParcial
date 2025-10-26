<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulo';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'ubicacion',
    ];

    /**
     * Relación con Aulas
     */
    public function aulas()
    {
        return $this->hasMany(Aula::class, 'id_modulo', 'codigo');
    }
}

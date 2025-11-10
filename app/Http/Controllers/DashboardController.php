<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Aula;
use App\Models\Grupo;
use App\Models\Semestre;
use App\Models\Modulo;
use App\Models\Carrera;
use App\Models\Usuario;
use App\Models\Justificacion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        $roles = $usuario->roles;
        
        // Estadísticas para el dashboard
        $stats = [];
        if ($usuario->hasRole('Administrador') || $usuario->hasRole('Coordinador')) {
            $stats = [
                'usuarios' => Usuario::count(),
                'materias' => Materia::count(),
                'aulas' => Aula::count(),
                'grupos' => Grupo::count(),
                'semestres' => Semestre::count(),
                'modulos' => Modulo::count(),
                'carreras' => Carrera::count(),
                'justificaciones_pendientes' => Justificacion::where('estado', 'Pendiente')->count(),
            ];
        }
        
        // Estadísticas para docentes
        if ($usuario->hasRole('Docente')) {
            $stats = [
                'mis_justificaciones' => Justificacion::where('id_usuario', $usuario->id)->count(),
                'justificaciones_pendientes' => Justificacion::where('id_usuario', $usuario->id)->where('estado', 'Pendiente')->count(),
                'justificaciones_aprobadas' => Justificacion::where('id_usuario', $usuario->id)->where('estado', 'Aprobada')->count(),
            ];
        }
        
        return view('dashboard', compact('usuario', 'roles', 'stats'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Aula;
use App\Models\Grupo;
use App\Models\Semestre;
use App\Models\Modulo;
use App\Models\Carrera;
use App\Models\Usuario;
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
            ];
        }
        
        return view('dashboard', compact('usuario', 'roles', 'stats'));
    }
}

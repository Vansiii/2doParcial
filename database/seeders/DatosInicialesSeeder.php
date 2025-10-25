<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;

class DatosInicialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Insertar Roles
        $roles = ['Administrador', 'Autoridad', 'Coordinador', 'Docente'];
        foreach ($roles as $rol) {
            Rol::firstOrCreate(['descripcion' => $rol]);
        }

        // 2. Insertar Permisos
        $permisos = [
            'Gestionar Usuarios',
            'Gestionar Docentes',
            'Gestionar Horarios',
            'Ver Reportes',
            'Gestionar Materias',
            'Gestionar Asistencias'
        ];
        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(['descripcion' => $permiso]);
        }

        // 3. Asignar permisos a roles
        $rolAdmin = Rol::where('descripcion', 'Administrador')->first();
        $todosPermisos = Permiso::all();
        $rolAdmin->permisos()->syncWithoutDetaching($todosPermisos);

        $rolCoordinador = Rol::where('descripcion', 'Coordinador')->first();
        $permisosCoordinador = Permiso::whereIn('descripcion', [
            'Gestionar Docentes', 
            'Gestionar Horarios', 
            'Ver Reportes'
        ])->get();
        $rolCoordinador->permisos()->syncWithoutDetaching($permisosCoordinador);

        $rolAutoridad = Rol::where('descripcion', 'Autoridad')->first();
        $permisosAutoridad = Permiso::where('descripcion', 'Ver Reportes')->get();
        $rolAutoridad->permisos()->syncWithoutDetaching($permisosAutoridad);

        // 4. Crear usuarios de prueba
        // Contraseña para todos: password123
        $password = Hash::make('password123');

        // Usuario Administrador
        $admin = Usuario::firstOrCreate(
            ['correo' => 'admin@sistema.com'],
            [
                'nombre' => 'Admin Sistema',
                'telefono' => 71234567,
                'passw' => $password
            ]
        );
        $admin->roles()->syncWithoutDetaching([$rolAdmin->id => ['detalle' => 'Administrador principal del sistema']]);

        // Usuario Coordinador
        $coordinador = Usuario::firstOrCreate(
            ['correo' => 'coordinador@sistema.com'],
            [
                'nombre' => 'María Coordinadora',
                'telefono' => 72345678,
                'passw' => $password
            ]
        );
        $coordinador->roles()->syncWithoutDetaching([$rolCoordinador->id => ['detalle' => 'Coordinador académico']]);

        // Usuario Autoridad
        $autoridad = Usuario::firstOrCreate(
            ['correo' => 'autoridad@sistema.com'],
            [
                'nombre' => 'Carlos Autoridad',
                'telefono' => 73456789,
                'passw' => $password
            ]
        );
        $autoridad->roles()->syncWithoutDetaching([$rolAutoridad->id => ['detalle' => 'Autoridad académica']]);

        // Usuarios Docentes
        $rolDocente = Rol::where('descripcion', 'Docente')->first();
        
        $docentes = [
            ['nombre' => 'Juan Docente Pérez', 'correo' => 'docente1@sistema.com', 'telefono' => 74567890],
            ['nombre' => 'Ana Profesora García', 'correo' => 'docente2@sistema.com', 'telefono' => 75678901],
            ['nombre' => 'Pedro Profesor López', 'correo' => 'docente3@sistema.com', 'telefono' => 76789012],
        ];

        foreach ($docentes as $docenteData) {
            $docente = Usuario::firstOrCreate(
                ['correo' => $docenteData['correo']],
                [
                    'nombre' => $docenteData['nombre'],
                    'telefono' => $docenteData['telefono'],
                    'passw' => $password
                ]
            );
            $docente->roles()->syncWithoutDetaching([$rolDocente->id => ['detalle' => 'Docente de planta']]);
        }

        // 5. Insertar días de la semana
        $dias = [
            ['id' => 1, 'descripcion' => 'Lunes'],
            ['id' => 2, 'descripcion' => 'Martes'],
            ['id' => 3, 'descripcion' => 'Miércoles'],
            ['id' => 4, 'descripcion' => 'Jueves'],
            ['id' => 5, 'descripcion' => 'Viernes'],
            ['id' => 6, 'descripcion' => 'Sábado'],
            ['id' => 7, 'descripcion' => 'Domingo'],
        ];

        foreach ($dias as $dia) {
            DB::table('dia')->insertOrIgnore($dia);
        }

        $this->command->info('Datos iniciales insertados correctamente!');
        $this->command->info('Usuarios creados con contraseña: password123');
    }
}

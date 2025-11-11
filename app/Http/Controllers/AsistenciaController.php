<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Horario;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * CU15: Consultar Asistencias (Admin, Autoridad, Coordinador)
     * Muestra todas las asistencias con filtros
     */
    public function index(Request $request)
    {
        $query = Asistencia::with(['usuario', 'horario.grupo', 'horario.materias']);

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('docente')) {
            $query->where('id_usuario', $request->docente);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $asistencias = $query->orderBy('fecha', 'desc')
                            ->orderBy('hora', 'desc')
                            ->paginate(20);

        $docentes = Usuario::whereHas('roles', function($q) {
            $q->where('descripcion', 'Docente');
        })->orderBy('nombre')->get();

        Bitacora::registrar(
            'Consulta de asistencias',
            true,
            'Usuario consultó el registro de asistencias',
            auth()->id()
        );

        return view('asistencias.index', compact('asistencias', 'docentes'));
    }

    /**
     * CU15: Mis Asistencias (Docente)
     * Muestra las asistencias del docente autenticado
     */
    public function misAsistencias(Request $request)
    {
        $usuario = auth()->user();

        $query = Asistencia::with(['horario.grupo', 'horario.materias', 'horario.aula'])
                          ->where('id_usuario', $usuario->id);

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $asistencias = $query->orderBy('fecha', 'desc')
                            ->orderBy('hora', 'desc')
                            ->paginate(15);

        Bitacora::registrar(
            'Consulta de asistencias personales',
            true,
            'Docente consultó sus asistencias',
            auth()->id()
        );

        return view('asistencias.mis-asistencias', compact('asistencias'));
    }

    /**
     * CU15: Formulario para Marcar Asistencia (Docente)
     * Muestra los horarios del día actual donde el docente puede marcar
     */
    public function mostrarFormulario()
    {
        $usuario = auth()->user();
        $hoy = Carbon::now();
        // Capitalizar la primera letra para coincidir con la BD (Lunes, Martes, Sábado, etc.)
        $diaSemana = ucfirst($hoy->locale('es')->dayName);

        // Obtener horarios del docente para hoy
        // Lógica: Horario -> Grupo -> Grupo_Materia -> Docente
        $horariosHoy = Horario::with(['grupo', 'aula', 'dias', 'materias'])
            ->whereHas('dias', function($query) use ($diaSemana) {
                $query->where('descripcion', $diaSemana);
            })
            ->where(function($query) use ($usuario) {
                // El horario debe tener una materia que esté asignada al docente en ese grupo
                $query->whereIn('id', function($subquery) use ($usuario) {
                    $subquery->select('horario.id')
                        ->from('horario')
                        ->join('horario_mat', 'horario.id', '=', 'horario_mat.id_horario')
                        ->join('grupo_materia', function($join) {
                            $join->on('horario_mat.sigla_materia', '=', 'grupo_materia.sigla_materia')
                                 ->on('horario.id_grupo', '=', 'grupo_materia.id_grupo');
                        })
                        ->where('grupo_materia.id_docente', $usuario->id);
                });
            })
            ->orderBy('horaini')
            ->get();

        // Verificar cuáles ya tienen asistencia marcada hoy
        foreach ($horariosHoy as $horario) {
            $horario->asistencia_hoy = Asistencia::where('id_horario', $horario->id)
                ->where('id_usuario', $usuario->id)
                ->whereDate('fecha', $hoy->toDateString())
                ->first();
        }

        return view('asistencias.marcar', compact('horariosHoy', 'hoy'));
    }

    /**
     * CU15: Marcar Asistencia (Docente)
     * Valida horario y tolerancia de 15 minutos
     */
    public function marcar(Request $request)
    {
        $request->validate([
            'id_horario' => 'required|exists:horario,id',
        ]);

        $usuario = auth()->user();
        $ahora = Carbon::now();
        $horaActual = $ahora->format('H:i:s');
        $fechaActual = $ahora->toDateString();

        // Obtener el horario
        $horario = Horario::findOrFail($request->id_horario);

        // Verificar que el docente esté asignado a este horario
        // El horario debe tener una materia que esté asignada al docente en el grupo
        $asignado = DB::table('horario_mat')
            ->join('grupo_materia', function($join) use ($horario) {
                $join->on('horario_mat.sigla_materia', '=', 'grupo_materia.sigla_materia')
                     ->where('grupo_materia.id_grupo', '=', $horario->id_grupo);
            })
            ->where('horario_mat.id_horario', $horario->id)
            ->where('grupo_materia.id_docente', $usuario->id)
            ->exists();

        if (!$asignado) {
            return back()->withErrors(['error' => 'No está asignado a este horario.']);
        }

        // Verificar día de la semana (capitalizar para coincidir con la BD)
        $diaSemana = ucfirst($ahora->locale('es')->dayName);
        $esDiaValido = $horario->dias()->where('descripcion', $diaSemana)->exists();
        if (!$esDiaValido) {
            return back()->withErrors(['error' => 'Este horario no corresponde al día de hoy (' . $diaSemana . ').']);
        }

        // Verificar si ya marcó asistencia hoy en este horario
        $asistenciaExistente = Asistencia::where('id_horario', $horario->id)
            ->where('id_usuario', $usuario->id)
            ->whereDate('fecha', $fechaActual)
            ->first();

        if ($asistenciaExistente) {
            return back()->withErrors(['error' => 'Ya marcó su asistencia en este horario hoy.']);
        }

        // Validar rango de tiempo (con 15 min de tolerancia)
        $horaInicio = Carbon::createFromFormat('H:i:s', $horario->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i:s', $horario->hora_fin);
        $horaConTolerancia = $horaInicio->copy()->addMinutes(15);

        $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActual);

        // Debe estar dentro del rango: desde hora_inicio hasta (hora_fin)
        // Y tiene 15 minutos de tolerancia después de hora_inicio
        if ($horaActualCarbon->lt($horaInicio) || $horaActualCarbon->gt($horaFin)) {
            return back()->withErrors([
                'error' => 'Fuera del horario permitido. Horario: ' . 
                          $horario->hora_inicio . ' - ' . $horario->hora_fin
            ]);
        }

        // Determinar tipo de asistencia
        $tipo = 'Puntual';
        if ($horaActualCarbon->gt($horaConTolerancia)) {
            $tipo = 'Tardanza';
        }

        try {
            // Registrar asistencia
            Asistencia::create([
                'fecha' => $ahora,
                'hora' => $horaActual,
                'tipo' => $tipo,
                'id_horario' => $horario->id,
                'id_usuario' => $usuario->id,
            ]);

            Bitacora::registrar(
                'Registro de asistencia',
                true,
                'Docente marcó asistencia: ' . $tipo . ' en horario ID: ' . $horario->id,
                $usuario->id
            );

            return redirect()->route('asistencias.marcar')
                ->with('success', '¡Asistencia marcada como ' . $tipo . '!');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al marcar asistencia',
                false,
                'Error: ' . $e->getMessage(),
                $usuario->id
            );

            return back()->withErrors(['error' => 'Error al registrar asistencia: ' . $e->getMessage()]);
        }
    }

    /**
     * CU15: Gestionar Ausencias (Admin, Coordinador)
     * Muestra horarios del día donde no se ha marcado asistencia
     */
    public function gestionarAusencias(Request $request)
    {
        // Fecha a revisar (por defecto hoy, pero puede seleccionar otra)
        $fecha = $request->filled('fecha') ? Carbon::parse($request->fecha) : Carbon::now();
        $fechaStr = $fecha->toDateString();
        
        // Capitalizar día de la semana
        $diaSemana = ucfirst($fecha->locale('es')->dayName);
        
        // Obtener todos los horarios que deberían tener clase ese día
        $horarios = Horario::with(['grupo', 'aula', 'dias', 'materias'])
            ->whereHas('dias', function($query) use ($diaSemana) {
                $query->where('descripcion', $diaSemana);
            })
            ->where('horafin', '<=', Carbon::now()->format('H:i:s')) // Solo horarios que ya pasaron
            ->orderBy('horaini')
            ->get();
        
        $horariosSinAsistencia = [];
        
        foreach ($horarios as $horario) {
            // Obtener docentes asignados a este horario
            $docentes = DB::table('grupo_materia')
                ->join('horario_mat', function($join) use ($horario) {
                    $join->on('horario_mat.sigla_materia', '=', 'grupo_materia.sigla_materia')
                         ->where('grupo_materia.id_grupo', '=', $horario->id_grupo);
                })
                ->join('usuario', 'usuario.id', '=', 'grupo_materia.id_docente')
                ->where('horario_mat.id_horario', $horario->id)
                ->select('usuario.*', 'grupo_materia.sigla_materia')
                ->distinct()
                ->get();
            
            foreach ($docentes as $docente) {
                // Verificar si ya tiene asistencia marcada
                $asistencia = Asistencia::where('id_horario', $horario->id)
                    ->where('id_usuario', $docente->id)
                    ->whereDate('fecha', $fechaStr)
                    ->first();
                
                if (!$asistencia) {
                    // Este docente no marcó asistencia
                    $horariosSinAsistencia[] = [
                        'horario' => $horario,
                        'docente' => $docente,
                        'fecha' => $fechaStr,
                    ];
                }
            }
        }
        
        Bitacora::registrar(
            'Gestión de ausencias',
            true,
            'Consulta horarios sin asistencia para ' . $fechaStr,
            auth()->id()
        );
        
        return view('asistencias.gestionar-ausencias', compact('horariosSinAsistencia', 'fecha'));
    }

    /**
     * CU15: Marcar Ausencia Manualmente (Admin, Coordinador)
     */
    public function marcarAusencia(Request $request)
    {
        $request->validate([
            'id_horario' => 'required|exists:horario,id',
            'id_docente' => 'required|exists:usuario,id',
            'fecha' => 'required|date',
        ]);

        $horario = Horario::findOrFail($request->id_horario);
        $docente = Usuario::findOrFail($request->id_docente);
        $fecha = Carbon::parse($request->fecha);

        // Verificar que no exista ya una asistencia
        $asistenciaExistente = Asistencia::where('id_horario', $request->id_horario)
            ->where('id_usuario', $request->id_docente)
            ->whereDate('fecha', $fecha->toDateString())
            ->first();

        if ($asistenciaExistente) {
            return back()->withErrors(['error' => 'Ya existe un registro de asistencia para este horario y docente.']);
        }

        try {
            // Registrar ausencia
            Asistencia::create([
                'fecha' => $fecha,
                'hora' => $horario->horafin, // Usar hora de fin como referencia
                'tipo' => 'Ausente',
                'id_horario' => $request->id_horario,
                'id_usuario' => $request->id_docente,
            ]);

            Bitacora::registrar(
                'Registro de ausencia',
                true,
                'Ausencia marcada para docente ID: ' . $request->id_docente,
                auth()->id()
            );

            return back()->with('success', 'Ausencia registrada correctamente para ' . $docente->nombre);
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al marcar ausencia',
                false,
                substr($e->getMessage(), 0, 120),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar ausencia: ' . $e->getMessage()]);
        }
    }

    /**
     * CU15: Marcar múltiples ausencias de una vez
     */
    public function marcarAusenciasMasivas(Request $request)
    {
        $request->validate([
            'ausencias' => 'required|array',
            'ausencias.*.id_horario' => 'required|exists:horario,id',
            'ausencias.*.id_docente' => 'required|exists:usuario,id',
            'ausencias.*.fecha' => 'required|date',
        ]);

        $registradas = 0;
        $errores = 0;

        DB::beginTransaction();
        try {
            foreach ($request->ausencias as $ausencia) {
                $fecha = Carbon::parse($ausencia['fecha']);
                
                // Verificar que no exista
                $existe = Asistencia::where('id_horario', $ausencia['id_horario'])
                    ->where('id_usuario', $ausencia['id_docente'])
                    ->whereDate('fecha', $fecha->toDateString())
                    ->exists();

                if (!$existe) {
                    $horario = Horario::find($ausencia['id_horario']);
                    
                    Asistencia::create([
                        'fecha' => $fecha,
                        'hora' => $horario->horafin,
                        'tipo' => 'Ausente',
                        'id_horario' => $ausencia['id_horario'],
                        'id_usuario' => $ausencia['id_docente'],
                    ]);
                    
                    $registradas++;
                } else {
                    $errores++;
                }
            }

            DB::commit();

            Bitacora::registrar(
                'Registro masivo de ausencias',
                true,
                $registradas . ' ausencias registradas',
                auth()->id()
            );

            return back()->with('success', "Se registraron {$registradas} ausencias correctamente." . 
                ($errores > 0 ? " {$errores} ya existían." : ""));
        } catch (\Exception $e) {
            DB::rollBack();
            
            Bitacora::registrar(
                'Error en registro masivo',
                false,
                substr($e->getMessage(), 0, 120),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar ausencias: ' . $e->getMessage()]);
        }
    }
}

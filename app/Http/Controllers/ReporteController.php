<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;
use App\Models\Usuario;
use App\Models\Grupo;
use App\Models\Asistencia;
use App\Models\Aula;
use App\Models\Dia;
use App\Models\Bitacora;
use App\Models\Justificacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Mostrar página principal de reportes
     */
    public function index()
    {
        $usuario = auth()->user();
        
        // Obtener datos para los filtros - Docentes son usuarios con rol "Docente"
        $docentes = Usuario::whereHas('roles', function($query) {
            $query->where('descripcion', 'Docente');
        })->orderBy('nombre')->get();
        
        $grupos = Grupo::with('periodo')->orderBy('sigla')->get();
        $aulas = Aula::orderBy('nroaula')->get();
        $dias = Dia::orderBy('id')->get();
        $periodos = \App\Models\Semestre::orderBy('gestion', 'desc')
            ->orderBy('periodo', 'desc')
            ->get();
        
        // Obtener todos los roles para el filtro de usuarios
        $roles = \App\Models\Rol::orderBy('descripcion')->get();
        
        return view('reportes.index', compact('docentes', 'grupos', 'aulas', 'dias', 'periodos', 'roles'));
    }

    /**
     * Generar Reporte de Horarios Semanales
     */
    public function horariosSemanal(Request $request)
    {
        $request->validate([
            'formato' => 'required|in:pdf,excel,csv',
            'id_docente' => 'nullable|exists:usuario,id',
            'id_grupo' => 'nullable|exists:grupo,id',
        ]);

        $formato = $request->formato;
        $idDocente = $request->id_docente;
        $idGrupo = $request->id_grupo;

        // Construir consulta
        $query = Horario::with([
            'grupo.periodo',
            'aula',
            'dias',
            'materias'
        ]);

        if ($idGrupo) {
            $query->where('id_grupo', $idGrupo);
        }

        if ($idDocente) {
            // Filtrar por docente usando grupo_materia
            // Debe filtrar tanto por grupo como por materia que el docente dicta
            $query->where(function($q) use ($idDocente) {
                $q->whereExists(function($subQuery) use ($idDocente) {
                    $subQuery->select(DB::raw(1))
                        ->from('grupo_materia')
                        ->whereColumn('grupo_materia.id_grupo', 'horario.id_grupo')
                        ->where('grupo_materia.id_docente', $idDocente)
                        ->whereExists(function($materiaQuery) {
                            $materiaQuery->select(DB::raw(1))
                                ->from('horario_mat')
                                ->whereColumn('horario_mat.id_horario', 'horario.id')
                                ->whereColumn('horario_mat.sigla_materia', 'grupo_materia.sigla_materia');
                        });
                });
            });
        }

        $horarios = $query->get();

        // Organizar horarios por día (excluyendo domingo - ID 7)
        $horariosPorDia = [];
        $dias = Dia::where('id', '!=', 7)->orderBy('id')->get();
        
        foreach ($dias as $dia) {
            $horariosPorDia[$dia->nombre] = $horarios->filter(function ($horario) use ($dia) {
                return $horario->dias->contains('id', $dia->id);
            })->sortBy('horaini');
        }

        $docente = $idDocente ? Usuario::find($idDocente) : null;
        $grupo = $idGrupo ? Grupo::find($idGrupo) : null;

        // Registrar en bitácora (máx 128 caracteres)
        $detalle = strtoupper($formato);
        if ($docente) {
            $detalle .= ', Doc: ' . substr($docente->nombre, 0, 40);
        }
        if ($grupo) {
            $detalle .= ', Gpo: ' . $grupo->sigla;
        }
        
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Reporte Horarios Semanales',
            'estado' => true,
            'detalle' => substr($detalle, 0, 128),
            'id_usuario' => auth()->id(),
        ]);

        // Generar reporte según formato
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.horarios-semanal', compact('horariosPorDia', 'docente', 'grupo', 'dias'));
            $pdf->setPaper('letter', 'landscape');
            return $pdf->download('reporte_horarios_semanal_' . now()->format('Y-m-d_His') . '.pdf');
        } else {
            // Preparar datos para Excel/CSV
            $data = [];
            foreach ($horariosPorDia as $nombreDia => $horarios) {
                foreach ($horarios as $horario) {
                    // Obtener el docente correcto desde grupo_materia
                    $materia = $horario->materias->first();
                    $docenteNombre = 'N/A';
                    if ($materia && $horario->grupo) {
                        $gm = \DB::table('grupo_materia')
                            ->where('id_grupo', $horario->grupo->id)
                            ->where('sigla_materia', $materia->sigla)
                            ->first();
                        if ($gm) {
                            $docenteObj = Usuario::find($gm->id_docente);
                            $docenteNombre = $docenteObj ? $docenteObj->nombre : 'N/A';
                        }
                    }
                    
                    $materiaNombre = $materia ? $materia->nombre : 'N/A';
                    
                    $periodo = 'N/A';
                    if ($horario->grupo && $horario->grupo->periodo) {
                        $periodo = $horario->grupo->periodo->gestion . '/' . $horario->grupo->periodo->periodo;
                    }
                    
                    $data[] = [
                        'Día' => $nombreDia,
                        'Horario' => $horario->horaini . ' - ' . $horario->horafin,
                        'Docente' => $docenteNombre,
                        'Grupo' => $horario->grupo->sigla ?? 'N/A',
                        'Materia' => $materiaNombre,
                        'Aula' => $horario->aula->nroaula ?? 'N/A',
                        'Semestre' => $periodo,
                    ];
                }
            }
            
            $filename = 'reporte_horarios_semanal_' . now()->format('Y-m-d_His');
            return (new FastExcel(collect($data)))->download($filename . ($formato === 'csv' ? '.csv' : '.xlsx'));
        }
    }

    /**
     * Generar Reporte de Carga Horaria
     */
    public function cargaHoraria(Request $request)
    {
        $request->validate([
            'formato' => 'required|in:pdf,excel,csv',
            'id_docente' => 'nullable|exists:usuario,id',
        ]);

        $formato = $request->formato;
        $idDocente = $request->id_docente;

        // Obtener docentes (usuarios con rol Docente)
        $query = Usuario::whereHas('roles', function($q) {
            $q->where('descripcion', 'Docente');
        });

        if ($idDocente) {
            $query->where('id', $idDocente);
        }

        $docentes = $query->get();

        // Calcular carga horaria por docente
        $cargaHoraria = [];
        foreach ($docentes as $docente) {
            $totalPeriodos = 0;
            $materias = [];
            
            // Obtener asignaciones de grupo_materia para este docente
            $asignaciones = DB::table('grupo_materia')
                ->where('id_docente', $docente->id)
                ->get();
            
            $horariosDocente = collect();
            $diasLaboralesSet = collect();
            
            foreach ($asignaciones as $asignacion) {
                // Obtener horarios de esta materia en este grupo
                $horarios = Horario::where('id_grupo', $asignacion->id_grupo)
                    ->whereHas('materias', function($q) use ($asignacion) {
                        $q->where('materia.sigla', $asignacion->sigla_materia);
                    })
                    ->with(['materias', 'dias', 'aula.modulo', 'grupo'])
                    ->get();
                
                foreach ($horarios as $horario) {
                    // Verificar que sea la materia correcta
                    $materiasHorario = $horario->materias->where('sigla', $asignacion->sigla_materia);
                    
                    if ($materiasHorario->count() > 0) {
                        $horariosDocente->push($horario);
                        
                        // Contar períodos de 45 minutos
                        $tiempoTotal = $horario->tiempoh ?? 0; // tiempo en minutos
                        $periodos = ceil($tiempoTotal / 45); // cada período es 45 minutos
                        $diasTrabajados = $horario->dias->count();
                        
                        $totalPeriodos += ($periodos * $diasTrabajados);
                        
                        // Agregar días laborales únicos
                        foreach ($horario->dias as $dia) {
                            $diasLaboralesSet->push($dia->id);
                        }
                        
                        // Agrupar por materia
                        $materia = $materiasHorario->first();
                        $nombreMateria = $materia->nombre;
                        if (!isset($materias[$nombreMateria])) {
                            $materias[$nombreMateria] = 0;
                        }
                        $materias[$nombreMateria] += ($periodos * $diasTrabajados);
                    }
                }
            }
            
            // Calcular días laborales únicos
            $diasLaborales = $diasLaboralesSet->unique()->count();
            
            // Calcular horas trabajadas, ausencias y horas extras
            // Horas programadas = total_periodos * 45 minutos / 60
            $horasProgramadas = round(($totalPeriodos * 45) / 60, 2);
            
            // Obtener asistencias del docente (todas las fechas disponibles)
            $asistencias = Asistencia::where('id_usuario', $docente->id)
                ->with('horario.dias')
                ->get();
            
            // Contar tipos de asistencia registradas
            $asistenciasPuntuales = $asistencias->filter(function($a) {
                return strtolower(trim($a->tipo)) == 'puntual';
            })->count();
            
            $ausenciasRegistradas = $asistencias->filter(function($a) {
                return strtolower(trim($a->tipo)) == 'ausente';
            })->count();
            
            // CALCULAR AUSENCIAS DINÁMICAMENTE
            // Contar cuántos horarios pasados debió tener el docente
            $horariosEsperados = 0;
            $hoy = \Carbon\Carbon::now()->startOfDay();
            
            foreach ($horariosDocente as $horario) {
                // Por cada día que tiene este horario
                foreach ($horario->dias as $dia) {
                    // Contar cuántas veces ha ocurrido este día desde alguna fecha de inicio
                    // Asumiendo que los horarios empezaron al inicio del semestre
                    // Por simplicidad, contamos las últimas 16 semanas (un semestre típico)
                    $fechaInicio = $hoy->copy()->subWeeks(16);
                    $fechaActual = $fechaInicio->copy();
                    
                    while ($fechaActual->lte($hoy)) {
                        // Si el día de la semana coincide (1=Lunes, 7=Domingo)
                        if ($fechaActual->dayOfWeek == $dia->id || 
                            ($dia->id == 7 && $fechaActual->dayOfWeek == 0)) {
                            
                            // Verificar que la hora del horario ya haya pasado
                            $fechaHoraFin = $fechaActual->copy()->setTimeFromTimeString($horario->horafin);
                            if ($fechaHoraFin->lt(\Carbon\Carbon::now())) {
                                $horariosEsperados++;
                            }
                        }
                        $fechaActual->addDay();
                    }
                }
            }
            
            // Ausencias = horarios esperados - asistencias registradas (puntuales + tardanzas)
            $asistenciasRegistradasTotal = $asistencias->filter(function($a) {
                $tipo = strtolower(trim($a->tipo));
                return $tipo == 'puntual' || $tipo == 'tardanza';
            })->count();
            
            $ausencias = max(0, $horariosEsperados - $asistenciasRegistradasTotal - $ausenciasRegistradas);
            
            $tardanzas = $asistencias->filter(function($a) {
                return strtolower(trim($a->tipo)) == 'tardanza';
            })->count();
            
            $licencias = $asistencias->filter(function($a) {
                return strtolower(trim($a->tipo)) == 'licencia';
            })->count();
            
            // Horas trabajadas efectivas (asumiendo que cada asistencia puntual = horario completo)
            $horasTrabajadas = 0;
            foreach ($asistencias as $asistencia) {
                if (strtolower(trim($asistencia->tipo)) == 'puntual' || strtolower(trim($asistencia->tipo)) == 'tardanza') {
                    $horario = $asistencia->horario;
                    if ($horario && $horario->tiempoh) {
                        $horasTrabajadas += $horario->tiempoh / 60; // convertir minutos a horas
                    }
                }
            }
            $horasTrabajadas = round($horasTrabajadas, 2);
            
            // Horas extras (si trabajó más de lo programado)
            $horasExtras = max(0, $horasTrabajadas - $horasProgramadas);
            
            // Horas por ausencias (ausencias * promedio de horas por clase)
            $promedioHorasPorClase = $totalPeriodos > 0 ? $horasProgramadas / $totalPeriodos : 0;
            $horasAusencias = round($ausencias * $promedioHorasPorClase, 2);

            $cargaHoraria[] = [
                'docente' => $docente,
                'total_periodos' => $totalPeriodos,
                'materias' => $materias,
                'horarios' => $horariosDocente,
                'dias_laborales' => $diasLaborales,
                'horas_programadas' => $horasProgramadas,
                'horas_trabajadas' => $horasTrabajadas,
                'horas_extras' => $horasExtras,
                'horas_ausencias' => $horasAusencias,
                'asistencias_puntuales' => $asistenciasPuntuales,
                'ausencias' => $ausencias,
                'tardanzas' => $tardanzas,
                'licencias' => $licencias,
            ];
        }

        // Registrar en bitácora (máx 128 caracteres)
        $detalle = strtoupper($formato);
        if ($idDocente && $docentes->isNotEmpty()) {
            $detalle .= ', Doc: ' . substr($docentes->first()->nombre, 0, 40);
        }
        
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Reporte Carga Horaria',
            'estado' => true,
            'detalle' => substr($detalle, 0, 128),
            'id_usuario' => auth()->id(),
        ]);

        // Generar reporte según formato
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.carga-horaria', compact('cargaHoraria'));
            $pdf->setPaper('letter', 'portrait');
            return $pdf->download('reporte_carga_horaria_' . now()->format('Y-m-d_His') . '.pdf');
        } else {
            // Preparar datos para Excel/CSV
            $data = [];
            foreach ($cargaHoraria as $carga) {
                $materias = implode(', ', array_keys($carga['materias']));
                $data[] = [
                    'Docente' => $carga['docente']->nombre,
                    'Total Períodos' => $carga['total_periodos'],
                    'Días Laborales' => $carga['dias_laborales'],
                    'Horas Programadas' => $carga['horas_programadas'],
                    'Horas Trabajadas' => $carga['horas_trabajadas'],
                    'Horas Extras' => $carga['horas_extras'],
                    'Horas Ausencias' => $carga['horas_ausencias'],
                    'Asistencias Puntuales' => $carga['asistencias_puntuales'],
                    'Tardanzas' => $carga['tardanzas'],
                    'Ausencias' => $carga['ausencias'],
                    'Licencias' => $carga['licencias'],
                    'Materias' => $materias,
                    'Cant. Materias' => count($carga['materias']),
                ];
            }
            
            $filename = 'reporte_carga_horaria_' . now()->format('Y-m-d_His');
            return (new FastExcel(collect($data)))->download($filename . ($formato === 'csv' ? '.csv' : '.xlsx'));
        }
    }

    /**
     * Generar Reporte de Asistencia por Docente y Grupo
     */
    public function asistencia(Request $request)
    {
        $request->validate([
            'formato' => 'required|in:pdf,excel,csv',
            'id_docente' => 'nullable|exists:usuario,id',
            'id_grupo' => 'nullable|exists:grupo,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $formato = $request->formato;
        $idDocente = $request->id_docente;
        $idGrupo = $request->id_grupo;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        // Construir consulta - SOLO registros de asistencia existentes
        $query = Asistencia::with([
            'horario.grupo.periodo',
            'horario.materias',
            'horario.aula',
            'horario.dias',
            'usuario'
        ]);

        if ($idDocente) {
            // Filtrar asistencias del docente Y verificar que el horario corresponda
            // a una materia/grupo asignada a ese docente
            $query->where('id_usuario', $idDocente)
                ->whereHas('horario', function($q) use ($idDocente) {
                    $q->whereExists(function($subQuery) use ($idDocente) {
                        $subQuery->select(DB::raw(1))
                            ->from('grupo_materia')
                            ->whereColumn('grupo_materia.id_grupo', 'horario.id_grupo')
                            ->where('grupo_materia.id_docente', $idDocente)
                            ->whereExists(function($materiaQuery) {
                                $materiaQuery->select(DB::raw(1))
                                    ->from('horario_mat')
                                    ->whereColumn('horario_mat.id_horario', 'horario.id')
                                    ->whereColumn('horario_mat.sigla_materia', 'grupo_materia.sigla_materia');
                            });
                    });
                });
        }

        if ($idGrupo) {
            $query->whereHas('horario', function ($q) use ($idGrupo) {
                $q->where('id_grupo', $idGrupo);
            });
        }

        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        $asistencias = $query->orderBy('fecha', 'desc')->get();

        // Obtener justificaciones aprobadas SOLO del docente filtrado
        $justificaciones = collect();
        if ($idDocente) {
            $justificacionesQuery = Justificacion::where('estado', 'Aprobada')
                ->where('id_usuario', $idDocente);
            
            if ($fechaInicio && $fechaFin) {
                $justificacionesQuery->where(function($q) use ($fechaInicio, $fechaFin) {
                    // Justificación que solapa con el rango de fechas
                    $q->where(function($subQ) use ($fechaInicio, $fechaFin) {
                        $subQ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                             ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                             ->orWhere(function($innerQ) use ($fechaInicio, $fechaFin) {
                                 $innerQ->where('fecha_inicio', '<=', $fechaInicio)
                                        ->where('fecha_fin', '>=', $fechaFin);
                             });
                    });
                });
            }
            
            $justificaciones = $justificacionesQuery->get();
        }

        // NO modificar las asistencias existentes
        // Las justificaciones NO cambian registros existentes, son información adicional
        
        // Calcular estadísticas SIMPLES basadas SOLO en registros existentes
        $totalAsistencias = $asistencias->count();
        
        $presentes = $asistencias->filter(function($a) { 
            return trim(strtolower($a->tipo)) == 'puntual'; 
        })->count();
        
        $ausentes = $asistencias->filter(function($a) { 
            return trim(strtolower($a->tipo)) == 'ausente'; 
        })->count();
        
        $licencias = $asistencias->filter(function($a) { 
            return trim(strtolower($a->tipo)) == 'licencia'; 
        })->count();
        
        $retrasos = $asistencias->filter(function($a) { 
            return trim(strtolower($a->tipo)) == 'tardanza'; 
        })->count();

        $porcentajeAsistencia = $totalAsistencias > 0 ? round(($presentes / $totalAsistencias) * 100, 2) : 0;

        $docente = $idDocente ? Usuario::find($idDocente) : null;
        $grupo = $idGrupo ? Grupo::find($idGrupo) : null;

        // Registrar en bitácora (máx 128 caracteres)
        $detalle = strtoupper($formato);
        if ($docente) {
            $detalle .= ', Doc: ' . substr($docente->nombre, 0, 30);
        }
        if ($grupo) {
            $detalle .= ', Gpo: ' . $grupo->sigla;
        }
        if ($fechaInicio && $fechaFin) {
            $detalle .= ', ' . date('d/m', strtotime($fechaInicio)) . '-' . date('d/m', strtotime($fechaFin));
        }
        
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Reporte de Asistencia',
            'estado' => true,
            'detalle' => substr($detalle, 0, 128),
            'id_usuario' => auth()->id(),
        ]);

        // Generar reporte según formato
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.asistencia', compact(
                'asistencias', 'docente', 'grupo', 'fechaInicio', 'fechaFin',
                'totalAsistencias', 'presentes', 'ausentes', 'licencias', 'retrasos', 'porcentajeAsistencia'
            ));
            $pdf->setPaper('letter', 'portrait');
            return $pdf->download('reporte_asistencia_' . now()->format('Y-m-d_His') . '.pdf');
        } else {
            // Preparar datos para Excel/CSV
            $data = [];
            foreach ($asistencias as $asistencia) {
                // Obtener materia correcta desde horario_mat
                $materiaNombre = 'N/A';
                if ($asistencia->horario && $asistencia->horario->materias->isNotEmpty()) {
                    $materiaNombre = $asistencia->horario->materias->first()->nombre;
                }
                
                $data[] = [
                    'Fecha' => \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y'),
                    'Hora' => \Carbon\Carbon::parse($asistencia->hora)->format('H:i'),
                    'Docente' => $asistencia->usuario->nombre ?? 'N/A',
                    'Grupo' => $asistencia->horario->grupo->sigla ?? 'N/A',
                    'Materia' => $materiaNombre,
                    'Aula' => $asistencia->horario->aula->nroaula ?? 'N/A',
                    'Tipo' => ucfirst($asistencia->tipo),
                ];
            }
            
            $filename = 'reporte_asistencia_' . now()->format('Y-m-d_His');
            return (new FastExcel(collect($data)))->download($filename . ($formato === 'csv' ? '.csv' : '.xlsx'));
        }
    }

    /**
     * Generar Reporte de Aulas Disponibles
     */
    public function aulasDisponibles(Request $request)
    {
        $request->validate([
            'formato' => 'required|in:pdf,excel,csv',
            'id_dia' => 'nullable|exists:dia,id',
        ]);

        $formato = $request->formato;
        $idDia = $request->id_dia;

        // Obtener todas las aulas
        $aulas = Aula::orderBy('nroaula')->get();

        // Obtener horarios
        $horariosQuery = Horario::with(['grupo.periodo', 'materias', 'aula', 'dias']);

        if ($idDia) {
            $horariosQuery->whereHas('dias', function ($q) use ($idDia) {
                $q->where('dia.id', $idDia);
            });
        }

        $horarios = $horariosQuery->get();

        // Calcular disponibilidad por aula
        $disponibilidadAulas = [];
        foreach ($aulas as $aula) {
            $horariosAula = $horarios->where('nroaula', $aula->nroaula);
            $periodosOcupados = $horariosAula->count();
            $periodosDisponibles = 24 - $periodosOcupados; // Asumiendo 24 períodos por semana

            $disponibilidadAulas[] = [
                'aula' => $aula,
                'horarios' => $horariosAula,
                'periodos_ocupados' => $periodosOcupados,
                'periodos_disponibles' => $periodosDisponibles,
                'porcentaje_ocupacion' => $periodosOcupados > 0 ? round(($periodosOcupados / 24) * 100, 2) : 0,
            ];
        }

        $dia = $idDia ? Dia::find($idDia) : null;

        // Registrar en bitácora (máx 128 caracteres)
        $detalle = strtoupper($formato);
        if ($dia) {
            $detalle .= ', Día: ' . $dia->descripcion;
        }
        
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Reporte Aulas Disponibles',
            'estado' => true,
            'detalle' => substr($detalle, 0, 128),
            'id_usuario' => auth()->id(),
        ]);

        // Generar reporte según formato
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.aulas-disponibles', compact('disponibilidadAulas', 'dia'));
            $pdf->setPaper('letter', 'portrait');
            return $pdf->download('reporte_aulas_disponibles_' . now()->format('Y-m-d_His') . '.pdf');
        } else {
            // Preparar datos para Excel/CSV
            $data = [];
            foreach ($disponibilidadAulas as $disponibilidad) {
                $data[] = [
                    'Aula' => $disponibilidad['aula']->nroaula,
                    'Capacidad' => $disponibilidad['aula']->capacidad,
                    'Períodos Ocupados' => $disponibilidad['periodos_ocupados'],
                    'Períodos Disponibles' => $disponibilidad['periodos_disponibles'],
                    '% Ocupación' => $disponibilidad['porcentaje_ocupacion'] . '%',
                ];
            }
            
            $filename = 'reporte_aulas_disponibles_' . now()->format('Y-m-d_His');
            return (new FastExcel(collect($data)))->download($filename . ($formato === 'csv' ? '.csv' : '.xlsx'));
        }
    }

    /**
     * Generar Reporte Personalizado con columnas seleccionables
     */
    public function personalizado(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|in:usuarios,materias,grupos,horarios,asistencias',
            'formato' => 'required|in:pdf,excel,csv',
            'columnas' => 'required|array|min:1',
            'id_periodo' => 'nullable|exists:periodo_academico,id',
            'id_rol' => 'nullable|exists:rol,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $tipoReporte = $request->tipo_reporte;
        $formato = $request->formato;
        $columnasSeleccionadas = $request->columnas;
        $idPeriodo = $request->id_periodo;
        $idRol = $request->id_rol;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $data = [];
        $titulo = '';

        switch ($tipoReporte) {
            case 'usuarios':
                $data = $this->generarReporteUsuarios($columnasSeleccionadas, $idRol);
                $titulo = 'Reporte de Usuarios';
                break;
            
            case 'materias':
                $data = $this->generarReporteMaterias($columnasSeleccionadas, $idPeriodo);
                $titulo = 'Reporte de Materias';
                break;
            
            case 'grupos':
                $data = $this->generarReporteGrupos($columnasSeleccionadas, $idPeriodo);
                $titulo = 'Reporte de Grupos';
                break;
            
            case 'horarios':
                $data = $this->generarReporteHorarios($columnasSeleccionadas, $idPeriodo);
                $titulo = 'Reporte de Horarios';
                break;
            
            case 'asistencias':
                $data = $this->generarReporteAsistencias($columnasSeleccionadas, $fechaInicio, $fechaFin);
                $titulo = 'Reporte de Asistencias';
                break;
        }

        // Registrar en bitácora (máx 128 caracteres)
        $detalle = strtoupper($formato) . ', Cols: ' . count($columnasSeleccionadas);
        
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Reporte personalizado',
            'estado' => true,
            'detalle' => substr($detalle, 0, 128),
            'id_usuario' => auth()->id(),
        ]);

        // Preparar información de filtros para el PDF
        $filtroInfo = '';
        if ($idRol) {
            $rol = \App\Models\Rol::find($idRol);
            if ($rol) {
                $filtroInfo .= 'Rol: ' . $rol->descripcion;
            }
        }
        if ($idPeriodo) {
            $periodo = Semestre::find($idPeriodo);
            if ($periodo) {
                $filtroInfo .= ($filtroInfo ? ' | ' : '') . 'Periodo: ' . $periodo->abreviatura . ' (' . $periodo->gestion . '-' . $periodo->periodo . ')';
            }
        }
        if ($fechaInicio && $fechaFin) {
            $filtroInfo .= ($filtroInfo ? ' | ' : '') . 'Fechas: ' . \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y');
        }

        // Generar según formato
        if ($formato === 'pdf') {
            // Extraer las claves (nombres de columnas) del primer registro
            $columnas = count($data) > 0 ? array_keys($data[0]) : [];
            
            $pdf = Pdf::loadView('reportes.pdf.personalizado', [
                'datos' => $data,
                'tipoReporte' => $titulo,
                'columnas' => $columnas,
                'filtroInfo' => $filtroInfo ?: null
            ]);
            $pdf->setPaper('letter', 'landscape');
            return $pdf->download('reporte_personalizado_' . $tipoReporte . '_' . now()->format('Y-m-d_His') . '.pdf');
        } else {
            $filename = 'reporte_personalizado_' . $tipoReporte . '_' . now()->format('Y-m-d_His');
            return (new FastExcel(collect($data)))->download($filename . ($formato === 'csv' ? '.csv' : '.xlsx'));
        }
    }

    private function generarReporteUsuarios($columnas, $idRol = null)
    {
        $query = Usuario::with('roles');
        
        // Filtrar por rol si se especifica
        if ($idRol) {
            $query->whereHas('roles', function($q) use ($idRol) {
                $q->where('rol.id', $idRol);
            });
        }
        
        $usuarios = $query->orderBy('nombre')->get();
        
        $data = [];
        foreach ($usuarios as $usuario) {
            $row = [];
            foreach ($columnas as $columna) {
                switch ($columna) {
                    case 'codigo':
                        $row['Código'] = $usuario->codigo;
                        break;
                    case 'ci':
                        $row['CI'] = $usuario->ci;
                        break;
                    case 'nombre':
                        $row['Nombre'] = $usuario->nombre;
                        break;
                    case 'correo':
                        $row['Correo'] = $usuario->correo;
                        break;
                    case 'telefono':
                        $row['Teléfono'] = $usuario->telefono;
                        break;
                    case 'roles':
                        $row['Roles'] = $usuario->roles->pluck('descripcion')->implode(', ');
                        break;
                }
            }
            $data[] = $row;
        }
        return $data;
    }

    private function generarReporteMaterias($columnas, $idPeriodo = null)
    {
        $query = \App\Models\Materia::with(['carreras', 'periodos']);
        
        if ($idPeriodo) {
            $query->whereHas('periodos', function($q) use ($idPeriodo) {
                $q->where('id_periodo', $idPeriodo);
            });
        }
        
        $materias = $query->orderBy('sigla')->get();
        
        $data = [];
        foreach ($materias as $materia) {
            $row = [];
            foreach ($columnas as $columna) {
                switch ($columna) {
                    case 'sigla':
                        $row['Sigla'] = $materia->sigla;
                        break;
                    case 'nombre':
                        $row['Nombre'] = $materia->nombre;
                        break;
                    case 'nivel':
                        $row['Nivel'] = $materia->nivel;
                        break;
                    case 'carreras':
                        $row['Carreras'] = $materia->carreras->pluck('nombre')->implode(', ') ?: 'N/A';
                        break;
                    case 'periodos':
                        $periodos = $materia->periodos->map(function($p) {
                            return $p->gestion . '/' . $p->periodo;
                        })->implode(', ');
                        $row['Períodos'] = $periodos ?: 'N/A';
                        break;
                }
            }
            $data[] = $row;
        }
        return $data;
    }

    private function generarReporteGrupos($columnas, $idPeriodo = null)
    {
        $query = Grupo::with(['periodo', 'materias.carreras', 'docentes']);
        
        if ($idPeriodo) {
            $query->where('id_periodo', $idPeriodo);
        }
        
        $grupos = $query->orderBy('sigla')->get();
        
        $data = [];
        foreach ($grupos as $grupo) {
            $row = [];
            foreach ($columnas as $columna) {
                switch ($columna) {
                    case 'sigla':
                        $row['Sigla'] = $grupo->sigla;
                        break;
                    case 'periodo':
                        if ($grupo->periodo) {
                            $row['Período'] = $grupo->periodo->gestion . '/' . $grupo->periodo->periodo;
                        } else {
                            $row['Período'] = 'N/A';
                        }
                        break;
                    case 'materias':
                        $row['Materias'] = $grupo->materias->pluck('nombre')->implode(', ') ?: 'N/A';
                        break;
                    case 'docentes':
                        $row['Docentes'] = $grupo->docentes->pluck('nombre')->unique()->implode(', ') ?: 'N/A';
                        break;
                    case 'cantidad_materias':
                        $row['Cant. Materias'] = $grupo->materias->count();
                        break;
                }
            }
            $data[] = $row;
        }
        return $data;
    }

    private function generarReporteHorarios($columnas, $idPeriodo = null)
    {
        $query = Horario::with(['grupo.periodo', 'materias', 'aula', 'dias']);
        
        if ($idPeriodo) {
            $query->whereHas('grupo', function($q) use ($idPeriodo) {
                $q->where('id_periodo', $idPeriodo);
            });
        }
        
        $horarios = $query->get();
        
        $data = [];
        foreach ($horarios as $horario) {
            $row = [];
            foreach ($columnas as $columna) {
                switch ($columna) {
                    case 'grupo':
                        $row['Grupo'] = $horario->grupo->sigla ?? 'N/A';
                        break;
                    case 'periodo':
                        if ($horario->grupo && $horario->grupo->periodo) {
                            $row['Período'] = $horario->grupo->periodo->gestion . '/' . $horario->grupo->periodo->periodo;
                        } else {
                            $row['Período'] = 'N/A';
                        }
                        break;
                    case 'materia':
                        $row['Materia'] = $horario->materias->first()->nombre ?? 'N/A';
                        break;
                    case 'docente':
                        // Obtener el docente correcto desde grupo_materia
                        $materia = $horario->materias->first();
                        $docente = 'N/A';
                        if ($materia && $horario->grupo) {
                            $gm = DB::table('grupo_materia')
                                ->where('id_grupo', $horario->grupo->id)
                                ->where('sigla_materia', $materia->sigla)
                                ->first();
                            if ($gm) {
                                $docenteObj = Usuario::find($gm->id_docente);
                                $docente = $docenteObj ? $docenteObj->nombre : 'N/A';
                            }
                        }
                        $row['Docente'] = $docente;
                        break;
                    case 'aula':
                        $row['Aula'] = $horario->aula->nroaula ?? 'N/A';
                        break;
                    case 'dias':
                        $row['Días'] = $horario->dias->pluck('descripcion')->implode(', ') ?: 'N/A';
                        break;
                    case 'hora_inicio':
                        $row['Hora Inicio'] = \Carbon\Carbon::parse($horario->horaini)->format('H:i');
                        break;
                    case 'hora_fin':
                        $row['Hora Fin'] = \Carbon\Carbon::parse($horario->horafin)->format('H:i');
                        break;
                }
            }
            $data[] = $row;
        }
        return $data;
    }

    private function generarReporteAsistencias($columnas, $fechaInicio = null, $fechaFin = null)
    {
        $query = Asistencia::with(['usuario', 'horario.grupo.periodo', 'horario.materias', 'horario.aula']);
        
        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }
        
        $asistencias = $query->orderBy('fecha', 'desc')->get();
        
        $data = [];
        foreach ($asistencias as $asistencia) {
            $row = [];
            foreach ($columnas as $columna) {
                switch ($columna) {
                    case 'fecha':
                        $row['Fecha'] = \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y');
                        break;
                    case 'hora':
                        $row['Hora'] = \Carbon\Carbon::parse($asistencia->hora)->format('H:i');
                        break;
                    case 'docente_codigo':
                        $row['Código Docente'] = $asistencia->usuario->codigo ?? 'N/A';
                        break;
                    case 'docente_ci':
                        $row['CI Docente'] = $asistencia->usuario->ci ?? 'N/A';
                        break;
                    case 'docente_nombre':
                        $row['Docente'] = $asistencia->usuario->nombre ?? 'N/A';
                        break;
                    case 'grupo':
                        $row['Grupo'] = $asistencia->horario->grupo->sigla ?? 'N/A';
                        break;
                    case 'periodo':
                        if ($asistencia->horario->grupo && $asistencia->horario->grupo->periodo) {
                            $row['Período'] = $asistencia->horario->grupo->periodo->gestion . '/' . 
                                            $asistencia->horario->grupo->periodo->periodo;
                        } else {
                            $row['Período'] = 'N/A';
                        }
                        break;
                    case 'materia':
                        $row['Materia'] = $asistencia->horario->materias->first()->nombre ?? 'N/A';
                        break;
                    case 'aula':
                        $row['Aula'] = $asistencia->horario->aula->nroaula ?? 'N/A';
                        break;
                    case 'tipo':
                        $row['Tipo'] = $asistencia->tipo;
                        break;
                }
            }
            $data[] = $row;
        }
        return $data;
    }
}


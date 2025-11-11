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
            'grupo.docentes',
            'grupo.materias',
            'aula',
            'dias',
            'materias'
        ]);

        if ($idGrupo) {
            $query->where('id_grupo', $idGrupo);
        }

        if ($idDocente) {
            $query->whereHas('grupo.docentes', function($q) use ($idDocente) {
                $q->where('usuario.id', $idDocente);
            });
        }

        $horarios = $query->get();

        // Organizar horarios por día
        $horariosPorDia = [];
        $dias = Dia::orderBy('id')->get();
        
        foreach ($dias as $dia) {
            $horariosPorDia[$dia->nombre] = $horarios->filter(function ($horario) use ($dia) {
                return $horario->dias->contains('id', $dia->id);
            })->sortBy('horaini');
        }

        $docente = $idDocente ? Usuario::find($idDocente) : null;
        $grupo = $idGrupo ? Grupo::find($idGrupo) : null;

        // Registrar en bitácora
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Generó reporte de Horarios Semanales',
            'estado' => true,
            'detalle' => 'Formato: ' . strtoupper($formato) . 
                         ($docente ? ', Docente: ' . $docente->nombre : '') .
                         ($grupo ? ', Grupo: ' . $grupo->sigla : ''),
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
                    $docenteNombre = ($horario->grupo && $horario->grupo->docentes->isNotEmpty()) 
                        ? $horario->grupo->docentes->first()->nombre 
                        : 'N/A';
                    
                    $materiaNombre = ($horario->grupo && $horario->grupo->materias->isNotEmpty()) 
                        ? $horario->grupo->materias->first()->nombre 
                        : 'N/A';
                    
                    $data[] = [
                        'Día' => $nombreDia,
                        'Horario' => $horario->horaini . ' - ' . $horario->horafin,
                        'Docente' => $docenteNombre,
                        'Grupo' => $horario->grupo->sigla ?? 'N/A',
                        'Materia' => $materiaNombre,
                        'Aula' => $horario->aula->nroaula ?? 'N/A',
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
            
            // Obtener horarios donde este docente está asignado a través de grupo_materia
            $horarios = Horario::whereHas('grupo.docentes', function($q) use ($docente) {
                $q->where('usuario.id', $docente->id);
            })->with(['grupo.materias', 'dias', 'aula.modulo'])->get();

            foreach ($horarios as $horario) {
                // Contar períodos (cada día que trabaja)
                $diasTrabajados = $horario->dias->count();
                $totalPeriodos += $diasTrabajados;

                // Agrupar por materia
                foreach ($horario->grupo->materias as $materia) {
                    $nombreMateria = $materia->nombre;
                    if (!isset($materias[$nombreMateria])) {
                        $materias[$nombreMateria] = 0;
                    }
                    $materias[$nombreMateria] += $diasTrabajados;
                }
            }

            $cargaHoraria[] = [
                'docente' => $docente,
                'total_periodos' => $totalPeriodos,
                'materias' => $materias,
                'horarios' => $horarios,
            ];
        }

        // Registrar en bitácora
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Generó reporte de Carga Horaria',
            'estado' => true,
            'detalle' => 'Formato: ' . strtoupper($formato) . 
                         ($idDocente ? ', Docente: ' . $docentes->first()->nombre : ''),
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

        // Construir consulta
        $query = Asistencia::with([
            'horario.grupo.docentes',
            'horario.grupo.materias',
            'horario.aula',
            'usuario'
        ]);

        if ($idDocente) {
            $query->where('id_usuario', $idDocente);
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

        // Obtener justificaciones aprobadas del periodo (para todos los docentes o el filtrado)
        $justificacionesQuery = Justificacion::where('estado', 'Aprobada');
        
        if ($idDocente) {
            $justificacionesQuery->where('id_usuario', $idDocente);
        }
        
        if ($fechaInicio) {
            $justificacionesQuery->where(function($q) use ($fechaInicio) {
                $q->where('fecha_inicio', '<=', $fechaInicio)
                  ->where('fecha_fin', '>=', $fechaInicio);
            })->orWhere(function($q) use ($fechaInicio) {
                $q->where('fecha_inicio', '>=', $fechaInicio);
            });
        }
        
        if ($fechaFin) {
            $justificacionesQuery->where('fecha_inicio', '<=', $fechaFin);
        }
        
        $justificaciones = $justificacionesQuery->get();

        // Marcar asistencias que tienen justificación aprobada
        $asistencias = $asistencias->map(function($asistencia) use ($justificaciones) {
            // Verificar si la fecha de esta asistencia está dentro de alguna justificación aprobada
            $tieneJustificacion = $justificaciones->first(function($justificacion) use ($asistencia) {
                $fechaAsistencia = \Carbon\Carbon::parse($asistencia->fecha);
                return $fechaAsistencia->between($justificacion->fecha_inicio, $justificacion->fecha_fin);
            });
            
            // Si tiene justificación aprobada, cambiar el tipo a "Licencia"
            if ($tieneJustificacion) {
                $asistencia->tipo_original = $asistencia->tipo;
                $asistencia->tipo = 'Licencia';
                $asistencia->tiene_justificacion = true;
                $asistencia->justificacion = $tieneJustificacion;
            } else {
                $asistencia->tiene_justificacion = false;
            }
            
            return $asistencia;
        });

        // Calcular estadísticas basadas en el campo 'tipo' ajustado (case-insensitive)
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

        // Registrar en bitácora
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Generó reporte de Asistencia',
            'estado' => true,
            'detalle' => 'Formato: ' . strtoupper($formato) . 
                         ($docente ? ', Docente: ' . $docente->nombre : '') .
                         ($grupo ? ', Grupo: ' . $grupo->sigla : '') .
                         ($fechaInicio ? ', Desde: ' . $fechaInicio : '') .
                         ($fechaFin ? ', Hasta: ' . $fechaFin : ''),
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
                $docenteNombre = $asistencia->usuario->nombre ?? 'N/A';
                $materiaNombre = ($asistencia->horario->grupo && $asistencia->horario->grupo->materias->isNotEmpty()) 
                    ? $asistencia->horario->grupo->materias->first()->nombre 
                    : 'N/A';
                
                $tipoDisplay = $asistencia->tipo;
                if ($asistencia->tiene_justificacion) {
                    $tipoDisplay .= ' (JUSTIFICADA)';
                }
                
                $data[] = [
                    'Fecha' => \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y'),
                    'Hora' => \Carbon\Carbon::parse($asistencia->hora)->format('H:i'),
                    'Docente' => $docenteNombre,
                    'Grupo' => $asistencia->horario->grupo->sigla ?? 'N/A',
                    'Materia' => $materiaNombre,
                    'Aula' => $asistencia->horario->aula->nroaula ?? 'N/A',
                    'Tipo' => $tipoDisplay,
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
        $horariosQuery = Horario::with(['grupo.docentes', 'grupo.materias', 'aula', 'dias']);

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

        // Registrar en bitácora
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Generó reporte de Aulas Disponibles',
            'estado' => true,
            'detalle' => 'Formato: ' . strtoupper($formato) . 
                         ($dia ? ', Día: ' . $dia->descripcion : ''),
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

        // Registrar en bitácora
        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Generó reporte personalizado: ' . $titulo,
            'estado' => true,
            'detalle' => 'Formato: ' . strtoupper($formato) . ', Columnas: ' . count($columnasSeleccionadas),
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
        $query = Horario::with(['grupo.periodo', 'grupo.materias', 'grupo.docentes', 'aula', 'dias']);
        
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
                        $row['Docente'] = $horario->grupo->docentes->first()->nombre ?? 'N/A';
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


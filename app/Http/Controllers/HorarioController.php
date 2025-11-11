<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Grupo;
use App\Models\Dia;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    /**
     * CU12: Mostrar formulario para asignar horario
     * Actores: Administrador, Coordinador
     */
    public function asignar(Request $request)
    {
        $materias = Materia::orderBy('sigla')->get();
        $aulas = Aula::with('modulo')->orderBy('nroaula')->get();
        $grupos = Grupo::with('grupoMaterias.docente')->orderBy('sigla')->get();
        $dias = Dia::orderBy('id')->get();

        // Si hay filtros, mostrar horarios existentes
        $horarios = collect();
        if ($request->filled('sigla_materia') || $request->filled('id_grupo')) {
            $query = Horario::with(['materias', 'aula', 'grupo.grupoMaterias.docente', 'dias']);
            
            if ($request->filled('sigla_materia')) {
                $query->whereHas('materias', function($q) use ($request) {
                    $q->where('sigla', $request->sigla_materia);
                });
            }
            
            if ($request->filled('id_grupo')) {
                $query->where('id_grupo', $request->id_grupo);
            }
            
            $horarios = $query->orderBy('horaini')->get();
        }

        Bitacora::registrar(
            'Acceso a asignación de horarios',
            true,
            'Usuario accedió a la interfaz de asignación de horarios',
            auth()->id()
        );

        return view('horarios.asignar', compact('materias', 'aulas', 'grupos', 'dias', 'horarios'));
    }
    
    /**
     * API: Verificar disponibilidad de aulas en tiempo real
     */
    public function verificarDisponibilidad(Request $request)
    {
        $diaId = $request->dia_id;
        $horaIni = $request->hora_ini;
        $horaFin = $request->hora_fin;
        
        if (!$diaId || !$horaIni || !$horaFin) {
            return response()->json(['error' => 'Parámetros incompletos'], 400);
        }
        
        // Obtener todas las aulas
        $aulas = Aula::with('modulo')->orderBy('nroaula')->get();
        
        $disponibilidad = [];
        
        foreach ($aulas as $aula) {
            // Verificar si el aula está ocupada en este horario
            $conflicto = Horario::where('nroaula', $aula->nroaula)
                ->whereHas('dias', function($q) use ($diaId) {
                    $q->where('dia.id', $diaId);
                })
                ->where(function($query) use ($horaIni, $horaFin) {
                    $query->where(function($q) use ($horaIni, $horaFin) {
                        $q->where('horaini', '<=', $horaIni)
                          ->where('horafin', '>', $horaIni);
                    })->orWhere(function($q) use ($horaIni, $horaFin) {
                        $q->where('horaini', '<', $horaFin)
                          ->where('horafin', '>=', $horaFin);
                    })->orWhere(function($q) use ($horaIni, $horaFin) {
                        $q->where('horaini', '>=', $horaIni)
                          ->where('horafin', '<=', $horaFin);
                    });
                })
                ->with(['materias', 'grupo'])
                ->first();
            
            $disponibilidad[] = [
                'nroaula' => $aula->nroaula,
                'capacidad' => $aula->capacidad,
                'modulo' => $aula->modulo ? $aula->modulo->codigo : 'N/A',
                'disponible' => !$conflicto,
                'conflicto' => $conflicto ? [
                    'materia' => $conflicto->materias->first()->nombre ?? 'N/A',
                    'grupo' => $conflicto->grupo->sigla ?? 'N/A',
                    'horario' => $conflicto->horaini . ' - ' . $conflicto->horafin
                ] : null
            ];
        }
        
        return response()->json($disponibilidad);
    }

    /**
     * CU12: Guardar horario asignado
     */
    public function guardar(Request $request)
    {
        // Validación básica
        $request->validate([
            'sigla_materia' => 'required|exists:materia,sigla',
            'id_grupo' => 'required|exists:grupo,id',
            'dias_seleccionados' => 'required|array|min:1',
            'dias_seleccionados.*' => 'exists:dia,id',
        ], [
            'sigla_materia.required' => 'Debe seleccionar una materia',
            'id_grupo.required' => 'Debe seleccionar un grupo',
            'dias_seleccionados.required' => 'Debe seleccionar al menos un día',
            'dias_seleccionados.min' => 'Debe seleccionar al menos un día',
        ]);
        
        // Validación manual de aulas, horaini y horafin para cada día seleccionado
        foreach ($request->dias_seleccionados as $diaId) {
            if (!isset($request->nroaula[$diaId]) || empty($request->nroaula[$diaId])) {
                $dia = Dia::find($diaId);
                return back()->withErrors(['error' => "Debe seleccionar un aula para {$dia->descripcion}"])->withInput();
            }
            
            // Validar que el aula existe
            $aulaExiste = Aula::where('nroaula', $request->nroaula[$diaId])->exists();
            if (!$aulaExiste) {
                $dia = Dia::find($diaId);
                return back()->withErrors(['error' => "El aula seleccionada para {$dia->descripcion} no es válida"])->withInput();
            }
            
            if (!isset($request->horaini[$diaId]) || empty($request->horaini[$diaId])) {
                $dia = Dia::find($diaId);
                return back()->withErrors(['error' => "Debe especificar hora de inicio para {$dia->descripcion}"])->withInput();
            }
            
            if (!isset($request->horafin[$diaId]) || empty($request->horafin[$diaId])) {
                $dia = Dia::find($diaId);
                return back()->withErrors(['error' => "Debe especificar hora de fin para {$dia->descripcion}"])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $horariosCreados = [];
            $materia = Materia::find($request->sigla_materia);
            $grupo = Grupo::with('docentes')->find($request->id_grupo);

            // Crear un horario para cada día seleccionado con su hora y aula específicas
            foreach ($request->dias_seleccionados as $diaId) {
                // Validar que existan las horas y aula para este día
                if (!isset($request->horaini[$diaId]) || !isset($request->horafin[$diaId])) {
                    throw new \Exception("Faltan horas para el día ID: {$diaId}");
                }

                if (!isset($request->nroaula[$diaId]) || empty($request->nroaula[$diaId])) {
                    $dia = Dia::find($diaId);
                    throw new \Exception("Falta seleccionar aula para {$dia->descripcion}");
                }

                $nroAula = $request->nroaula[$diaId];
                $horaIni = $request->horaini[$diaId];
                $horaFin = $request->horafin[$diaId];

                // Validar que la hora fin sea posterior a la hora inicio
                if ($horaIni >= $horaFin) {
                    $dia = Dia::find($diaId);
                    throw new \Exception("La hora de fin debe ser posterior a la hora de inicio para {$dia->descripcion}");
                }

                // ===== VALIDACIÓN DE CONFLICTOS =====
                
                // 1. Conflicto de AULA (misma aula, mismo día, horas que se cruzan)
                $conflictoAula = Horario::where('nroaula', $nroAula)
                    ->whereHas('dias', function($q) use ($diaId) {
                        $q->where('dia.id', $diaId);
                    })
                    ->where(function($query) use ($horaIni, $horaFin) {
                        // Detectar cruce de horarios
                        $query->where(function($q) use ($horaIni, $horaFin) {
                            // El horario nuevo empieza durante un horario existente
                            $q->where('horaini', '<=', $horaIni)
                              ->where('horafin', '>', $horaIni);
                        })->orWhere(function($q) use ($horaIni, $horaFin) {
                            // El horario nuevo termina durante un horario existente
                            $q->where('horaini', '<', $horaFin)
                              ->where('horafin', '>=', $horaFin);
                        })->orWhere(function($q) use ($horaIni, $horaFin) {
                            // El horario nuevo contiene completamente a un horario existente
                            $q->where('horaini', '>=', $horaIni)
                              ->where('horafin', '<=', $horaFin);
                        });
                    })
                    ->with(['materias', 'grupo'])
                    ->first();

                if ($conflictoAula) {
                    $dia = Dia::find($diaId);
                    $materiaConflicto = $conflictoAula->materias->first();
                    throw new \Exception(
                        "CONFLICTO DE AULA: El aula {$nroAula} ya está ocupada el {$dia->descripcion} " .
                        "de {$conflictoAula->horaini} a {$conflictoAula->horafin} " .
                        "por {$materiaConflicto->nombre} (Grupo {$conflictoAula->grupo->sigla})"
                    );
                }

                // 2. Conflicto de GRUPO (mismo grupo, mismo día, horas que se cruzan)
                $conflictoGrupo = Horario::where('id_grupo', $request->id_grupo)
                    ->whereHas('dias', function($q) use ($diaId) {
                        $q->where('dia.id', $diaId);
                    })
                    ->where(function($query) use ($horaIni, $horaFin) {
                        $query->where(function($q) use ($horaIni, $horaFin) {
                            $q->where('horaini', '<=', $horaIni)
                              ->where('horafin', '>', $horaIni);
                        })->orWhere(function($q) use ($horaIni, $horaFin) {
                            $q->where('horaini', '<', $horaFin)
                              ->where('horafin', '>=', $horaFin);
                        })->orWhere(function($q) use ($horaIni, $horaFin) {
                            $q->where('horaini', '>=', $horaIni)
                              ->where('horafin', '<=', $horaFin);
                        });
                    })
                    ->with(['materias', 'aula'])
                    ->first();

                if ($conflictoGrupo) {
                    $dia = Dia::find($diaId);
                    $materiaConflicto = $conflictoGrupo->materias->first();
                    throw new \Exception(
                        "CONFLICTO DE GRUPO: El grupo {$grupo->sigla} ya tiene clase el {$dia->descripcion} " .
                        "de {$conflictoGrupo->horaini} a {$conflictoGrupo->horafin} " .
                        "({$materiaConflicto->nombre} en aula {$conflictoGrupo->nroaula})"
                    );
                }

                // 3. Conflicto de DOCENTE (mismo docente, mismo día, horas que se cruzan)
                // Obtener el docente asignado a esta combinación grupo-materia
                $grupoMateria = \App\Models\GrupoMateria::where('id_grupo', $request->id_grupo)
                    ->where('sigla_materia', $request->sigla_materia)
                    ->first();

                if ($grupoMateria && $grupoMateria->docente) {
                    $docente = $grupoMateria->docente;
                    
                    // Buscar si el docente tiene otro horario en el mismo día y hora
                    // El docente puede tener conflicto con cualquier otra materia que dicte
                    $conflictoDocente = Horario::whereHas('materias', function($queryMat) use ($docente) {
                            // Buscar horarios de materias donde este docente está asignado
                            $queryMat->whereIn('sigla', function($subQuery) use ($docente) {
                                $subQuery->select('sigla_materia')
                                    ->from('grupo_materia')
                                    ->where('id_docente', $docente->id);
                            });
                        })
                        ->whereIn('id_grupo', function($queryGrupo) use ($docente) {
                            // Y que sean de grupos donde este docente está asignado
                            $queryGrupo->select('id_grupo')
                                ->from('grupo_materia')
                                ->where('id_docente', $docente->id);
                        })
                        ->whereHas('dias', function($q) use ($diaId) {
                            $q->where('dia.id', $diaId);
                        })
                        ->where(function($query) use ($horaIni, $horaFin) {
                            $query->where(function($q) use ($horaIni, $horaFin) {
                                $q->where('horaini', '<=', $horaIni)
                                  ->where('horafin', '>', $horaIni);
                            })->orWhere(function($q) use ($horaIni, $horaFin) {
                                $q->where('horaini', '<', $horaFin)
                                  ->where('horafin', '>=', $horaFin);
                            })->orWhere(function($q) use ($horaIni, $horaFin) {
                                $q->where('horaini', '>=', $horaIni)
                                  ->where('horafin', '<=', $horaFin);
                            });
                        })
                        ->with(['materias', 'grupo', 'aula'])
                        ->first();

                    if ($conflictoDocente) {
                        $dia = Dia::find($diaId);
                        $materiaConflicto = $conflictoDocente->materias->first();
                        throw new \Exception(
                            "CONFLICTO DE DOCENTE: El docente {$docente->nombre} ya tiene clase el {$dia->descripcion} " .
                            "de {$conflictoDocente->horaini} a {$conflictoDocente->horafin} " .
                            "({$materiaConflicto->nombre}, Grupo {$conflictoDocente->grupo->sigla}, Aula {$conflictoDocente->nroaula})"
                        );
                    }
                }

                // ===== FIN DE VALIDACIÓN DE CONFLICTOS =====

                // Calcular tiempo en horas
                $horaIniCarbon = \Carbon\Carbon::parse($horaIni);
                $horaFinCarbon = \Carbon\Carbon::parse($horaFin);
                $tiempoH = $horaFinCarbon->diffInMinutes($horaIniCarbon) / 60;

                // Crear el horario para este día específico con su aula correspondiente
                $horario = Horario::create([
                    'horaini' => $horaIni,
                    'horafin' => $horaFin,
                    'tiempoh' => $tiempoH,
                    'nroaula' => $nroAula,
                    'id_grupo' => $request->id_grupo,
                ]);

                // Asignar materia
                $horario->materias()->attach($request->sigla_materia);

                // Asignar solo este día
                $horario->dias()->attach($diaId);

                $horariosCreados[] = [
                    'id' => $horario->id,
                    'dia' => Dia::find($diaId)->descripcion,
                    'aula' => $nroAula
                ];
            }

            DB::commit();

            // Construir detalle de aulas por día para bitácora
            $aulasDetalle = collect($horariosCreados)->map(function($h) {
                return "{$h['dia']}: Aula {$h['aula']}";
            })->implode(', ');

            Bitacora::registrar(
                'Asignación de horarios',
                true,
                'Se asignaron ' . count($horariosCreados) . ' horarios: Materia ' . $materia->nombre . 
                ' (' . $request->sigla_materia . '), Grupo ' . $grupo->sigla . '. Aulas: ' . $aulasDetalle,
                auth()->id()
            );

            return redirect()->route('horarios.asignar')
                ->with('success', 'Horarios asignados correctamente (' . count($horariosCreados) . ' horarios creados)');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Bitacora::registrar(
                'Error al asignar horarios',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * CU13: Consultar Horario por Docente
     * Actores: Todos
     */
    public function porDocente(Request $request, $id = null)
    {
        $docentes = Usuario::whereHas('roles', function($q) {
            $q->where('descripcion', 'Docente');
        })->orderBy('nombre')->get();

        $docenteSeleccionado = null;
        $horarios = collect();

        // Obtener ID desde la URL o desde query string
        if (!$id && $request->has('id')) {
            $id = $request->input('id');
        }

        // Si no se proporciona ID y el usuario es docente, mostrar su propio horario
        if (!$id && auth()->user()->hasRole('Docente')) {
            $id = auth()->id();
        }

        if ($id) {
            $docenteSeleccionado = Usuario::findOrFail($id);
            
            // Obtener horarios del docente a través de grupo_materia
            $horarios = Horario::with(['materias', 'aula', 'grupo', 'dias'])
                ->whereHas('materias', function($queryMat) use ($id) {
                    // Buscar horarios de materias donde este docente está asignado
                    $queryMat->whereIn('sigla', function($subQuery) use ($id) {
                        $subQuery->select('sigla_materia')
                            ->from('grupo_materia')
                            ->where('id_docente', $id);
                    });
                })
                ->whereIn('id_grupo', function($queryGrupo) use ($id) {
                    // Y que sean de grupos donde este docente está asignado
                    $queryGrupo->select('id_grupo')
                        ->from('grupo_materia')
                        ->where('id_docente', $id);
                })
                ->orderBy('horaini')
                ->get();

            Bitacora::registrar(
                'Consulta de horario por docente',
                true,
                'Usuario consultó el horario del docente: ' . $docenteSeleccionado->nombre,
                auth()->id()
            );
        }

        return view('horarios.docente', compact('docentes', 'docenteSeleccionado', 'horarios'));
    }

    /**
     * CU14: Consultar Horario por Grupo
     * Actores: Administrador, Coordinador, Docente
     */
    public function porGrupo(Request $request, $id = null)
    {
        $grupos = Grupo::orderBy('sigla')->get();

        $grupoSeleccionado = null;
        $horarios = collect();

        // Obtener ID desde la URL o desde query string
        if (!$id && $request->has('id')) {
            $id = $request->input('id');
        }

        if ($id) {
            $grupoSeleccionado = Grupo::with(['materias', 'grupoMaterias.docente'])->findOrFail($id);
            $horarios = Horario::with(['materias', 'aula', 'dias'])
                ->where('id_grupo', $id)
                ->orderBy('horaini')
                ->get();

            Bitacora::registrar(
                'Consulta de horario por grupo',
                true,
                'Usuario consultó el horario del grupo ID: ' . $id . ' (Sigla: ' . $grupoSeleccionado->sigla . ')',
                auth()->id()
            );
        }

        return view('horarios.grupo', compact('grupos', 'grupoSeleccionado', 'horarios'));
    }

    /**
     * CU12: Eliminar un horario
     */
    public function destroy($id)
    {
        try {
            $horario = Horario::findOrFail($id);
            $horario->delete();

            Bitacora::registrar(
                'Eliminación de horario',
                true,
                'Se eliminó el horario ID: ' . $id,
                auth()->id()
            );

            return back()->with('success', 'Horario eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar horario',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el horario']);
        }
    }
}

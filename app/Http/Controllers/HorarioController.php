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
     * CU10: Mostrar formulario para asignar horario
     * Actores: Administrador, Coordinador
     */
    public function asignar(Request $request)
    {
        $materias = Materia::orderBy('sigla')->get();
        $aulas = Aula::orderBy('nroaula')->get();
        $grupos = Grupo::with('docentes')->orderBy('sigla')->get();
        $dias = Dia::orderBy('id')->get();

        // Si hay filtros, mostrar horarios existentes
        $horarios = collect();
        if ($request->filled('sigla_materia') || $request->filled('id_grupo')) {
            $query = Horario::with(['materias', 'aula', 'grupo.docentes', 'dias']);
            
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
     * CU10: Guardar horario asignado
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'sigla_materia' => 'required|exists:materia,sigla',
            'nroaula' => 'required|exists:aula,nroaula',
            'id_grupo' => 'required|exists:grupo,id',
            'horaini' => 'required|date_format:H:i',
            'horafin' => 'required|date_format:H:i|after:horaini',
            'dias' => 'required|array|min:1',
            'dias.*' => 'exists:dia,id',
        ], [
            'sigla_materia.required' => 'Debe seleccionar una materia',
            'nroaula.required' => 'Debe seleccionar un aula',
            'id_grupo.required' => 'Debe seleccionar un grupo',
            'horaini.required' => 'Debe ingresar la hora de inicio',
            'horafin.required' => 'Debe ingresar la hora de fin',
            'horafin.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'dias.required' => 'Debe seleccionar al menos un día',
        ]);

        try {
            DB::beginTransaction();

            // Calcular tiempo en horas
            $horaIni = \Carbon\Carbon::parse($request->horaini);
            $horaFin = \Carbon\Carbon::parse($request->horafin);
            $tiempoH = $horaFin->diffInMinutes($horaIni) / 60;

            // Crear el horario
            $horario = Horario::create([
                'horaini' => $request->horaini,
                'horafin' => $request->horafin,
                'tiempoh' => $tiempoH,
                'nroaula' => $request->nroaula,
                'id_grupo' => $request->id_grupo,
            ]);

            // Asignar materia
            $horario->materias()->attach($request->sigla_materia);

            // Asignar días
            $horario->dias()->attach($request->dias);

            DB::commit();

            $materia = Materia::find($request->sigla_materia);
            
            Bitacora::registrar(
                'Asignación de horario',
                true,
                'Se asignó horario: Materia ' . $materia->nombre . ' (' . $request->sigla_materia . '), Grupo ID ' . $request->id_grupo . ', Aula ' . $request->nroaula,
                auth()->id()
            );

            return redirect()->route('horarios.asignar')
                ->with('success', 'Horario asignado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Bitacora::registrar(
                'Error al asignar horario',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al asignar el horario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * CU11: Consultar Horario por Docente
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
            
            // Obtener horarios del docente a través de sus grupos asignados
            $horarios = Horario::with(['materias', 'aula', 'grupo', 'dias'])
                ->whereHas('grupo.docentes', function($q) use ($id) {
                    $q->where('id_usuario', $id);
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
     * CU12: Consultar Horario por Grupo
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
            $grupoSeleccionado = Grupo::with(['materias', 'docentes'])->findOrFail($id);
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
     * Eliminar un horario
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

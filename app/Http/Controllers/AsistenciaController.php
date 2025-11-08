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
        $horariosHoy = Horario::with(['grupo', 'aula', 'dias', 'materias'])
            ->whereHas('dias', function($query) use ($diaSemana) {
                $query->where('descripcion', $diaSemana);
            })
            ->whereHas('materias.grupoMaterias', function($query) use ($usuario) {
                $query->where('id_docente', $usuario->id);
            })
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
        $asignado = DB::table('horario_mat')
            ->join('grupo_materia', function($join) use ($usuario, $horario) {
                $join->on('horario_mat.sigla_materia', '=', 'grupo_materia.sigla_materia')
                     ->where('grupo_materia.id_docente', '=', $usuario->id)
                     ->where('grupo_materia.id_grupo', '=', $horario->id_grupo);
            })
            ->where('horario_mat.id_horario', $horario->id)
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
}

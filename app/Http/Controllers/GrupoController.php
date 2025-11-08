<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * CU09: Gestionar Grupos - Listar
     * Actores: Administrador, Coordinador
     */
    public function index(Request $request)
    {
        $query = Grupo::with(['grupoMaterias.docente', 'grupoMaterias.materia']);

        // Filtros de búsqueda
        if ($request->filled('sigla')) {
            $query->where('sigla', 'ILIKE', '%' . $request->sigla . '%');
        }

        if ($request->filled('sigla_materia')) {
            $query->whereHas('grupoMaterias', function($q) use ($request) {
                $q->where('sigla_materia', 'ILIKE', '%' . $request->sigla_materia . '%');
            });
        }

        $grupos = $query->orderBy('sigla')->paginate(10);
        $materias = Materia::orderBy('sigla')->get();

        Bitacora::registrar(
            'Consulta de grupos',
            true,
            'Usuario consultó la lista de grupos',
            auth()->id()
        );

        return view('grupos.index', compact('grupos', 'materias'));
    }

    /**
     * Mostrar formulario para crear grupo
     */
    public function create()
    {
        $materias = Materia::orderBy('sigla')->get();
        return view('grupos.create', compact('materias'));
    }

    /**
     * CU09: Registrar Grupo
     */
    public function store(Request $request)
    {
        $request->validate([
            'sigla' => 'required|string|max:3|unique:grupo,sigla',
            'materias' => 'nullable|array',
            'materias.*' => 'exists:materia,sigla',
        ], [
            'sigla.required' => 'La sigla del grupo es obligatoria',
            'sigla.unique' => 'Esta sigla ya está registrada',
            'sigla.max' => 'La sigla no puede tener más de 3 caracteres',
        ]);

        try {
            $grupo = Grupo::create([
                'sigla' => strtoupper($request->sigla),
            ]);

            // NOTA: No asignamos materias aquí porque grupo_materia requiere id_docente
            // Las materias se asignan junto con docentes en "Gestionar Docentes"

            Bitacora::registrar(
                'Registro de grupo',
                true,
                'Se registró el grupo: ' . $grupo->sigla,
                auth()->id()
            );

            return redirect()->route('grupos.asignar-docentes', $grupo->sigla)
                ->with('success', 'Grupo registrado correctamente. Ahora asigne materias y docentes.');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al registrar grupo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar el grupo'])
                ->withInput();
        }
    }

    /**
     * Mostrar un grupo específico
     */
    public function show($sigla)
    {
        $grupo = Grupo::with(['horarios', 'grupoMaterias.docente', 'grupoMaterias.materia'])->where('sigla', $sigla)->firstOrFail();

        Bitacora::registrar(
            'Consulta de grupo',
            true,
            'Usuario consultó el grupo: ' . $sigla,
            auth()->id()
        );

        return view('grupos.show', compact('grupo'));
    }

    /**
     * Mostrar formulario para editar grupo
     */
    public function edit($sigla)
    {
        $grupo = Grupo::with(['grupoMaterias.materia'])->where('sigla', $sigla)->firstOrFail();
        $materias = Materia::orderBy('sigla')->get();
        return view('grupos.edit', compact('grupo', 'materias'));
    }

    /**
     * CU09: Actualizar Grupo
     */
    public function update(Request $request, $sigla)
    {
        $grupo = Grupo::where('sigla', $sigla)->firstOrFail();

        $request->validate([
            'materias' => 'nullable|array',
            'materias.*' => 'exists:materia,sigla',
        ]);

        try {
            // IMPORTANTE: Como grupo_materia requiere id_docente (NOT NULL),
            // no podemos simplemente sincronizar materias aquí.
            // Las materias se asignan junto con los docentes en "Gestionar Docentes"
            
            // No hay nada que actualizar en este método excepto validar
            // que el grupo existe (ya lo hicimos arriba)
            
            Bitacora::registrar(
                'Consulta de grupo para edición',
                true,
                'Usuario accedió a editar grupo: ' . $grupo->sigla,
                auth()->id()
            );

            // Redirigir a gestionar docentes donde se hace la asignación real
            return redirect()->route('grupos.asignar-docentes', $grupo->sigla)
                ->with('info', 'Para asignar materias al grupo, debe asignarlas junto con un docente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al procesar grupo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al procesar el grupo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * CU09: Eliminar Grupo
     */
    public function destroy($sigla)
    {
        $grupo = Grupo::where('sigla', $sigla)->firstOrFail();

        try {
            $grupo->delete();

            Bitacora::registrar(
                'Eliminación de grupo',
                true,
                'Se eliminó el grupo: ' . $sigla,
                auth()->id()
            );

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar grupo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el grupo. Puede tener horarios asignados.']);
        }
    }

    /**
     * Mostrar formulario para asignar docentes a grupo-materia
     */
    public function asignarDocentes($sigla)
    {
        $grupo = Grupo::with(['grupoMaterias.docente', 'grupoMaterias.materia'])
            ->where('sigla', $sigla)
            ->firstOrFail();
        
        // Obtener todos los usuarios con rol de Docente
        $docentes = Usuario::whereHas('roles', function($query) {
            $query->where('descripcion', 'Docente');
        })->orderBy('nombre')->get();

        return view('grupos.asignar-docentes', compact('grupo', 'docentes'));
    }

    /**
     * Guardar la asignación de docente a grupo-materia
     */
    public function guardarDocentes(Request $request, $sigla)
    {
        $grupo = Grupo::where('sigla', $sigla)->firstOrFail();

        $request->validate([
            'sigla_materia' => 'required|exists:materia,sigla',
            'id_docente' => 'required|exists:usuario,id',
        ], [
            'sigla_materia.required' => 'Debe seleccionar una materia',
            'id_docente.required' => 'Debe seleccionar un docente',
        ]);

        try {
            // Verificar si ya existe la asignación grupo-materia
            $existente = \App\Models\GrupoMateria::where('id_grupo', $grupo->id)
                ->where('sigla_materia', $request->sigla_materia)
                ->first();

            if ($existente) {
                // Actualizar el docente existente
                $existente->id_docente = $request->id_docente;
                $existente->save();
                $mensaje = 'Materia-Docente actualizado correctamente para el grupo ' . $grupo->sigla;
            } else {
                // Crear nueva asignación (esto asigna la materia al grupo junto con el docente)
                \App\Models\GrupoMateria::create([
                    'id_grupo' => $grupo->id,
                    'sigla_materia' => $request->sigla_materia,
                    'id_docente' => $request->id_docente,
                ]);
                $mensaje = 'Materia y Docente asignados correctamente al grupo ' . $grupo->sigla;
            }

            Bitacora::registrar(
                'Asignación de docente a grupo-materia',
                true,
                "Grupo {$grupo->sigla} - Materia {$request->sigla_materia} - Docente ID {$request->id_docente}",
                auth()->id()
            );

            return redirect()->route('grupos.asignar-docentes', $grupo->sigla)
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al asignar docente',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al asignar docente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Eliminar asignación de docente a grupo-materia
     */
    public function eliminarDocente($sigla, $siglaMateria)
    {
        $grupo = Grupo::where('sigla', $sigla)->firstOrFail();

        try {
            $eliminados = \App\Models\GrupoMateria::where('id_grupo', $grupo->id)
                ->where('sigla_materia', $siglaMateria)
                ->delete();

            if ($eliminados > 0) {
                Bitacora::registrar(
                    'Eliminación de docente de grupo-materia',
                    true,
                    "Grupo {$grupo->sigla} - Materia {$siglaMateria}",
                    auth()->id()
                );

                return redirect()->route('grupos.asignar-docentes', $grupo->sigla)
                    ->with('success', 'Docente eliminado correctamente');
            } else {
                return redirect()->route('grupos.asignar-docentes', $grupo->sigla)
                    ->with('info', 'No se encontró la asignación a eliminar');
            }
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar docente',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar docente']);
        }
    }
}


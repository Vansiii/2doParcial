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
        $query = Grupo::with(['materias', 'docentes']);

        // Filtros de búsqueda
        if ($request->filled('sigla')) {
            $query->where('sigla', 'ILIKE', '%' . $request->sigla . '%');
        }

        if ($request->filled('sigla_materia')) {
            $query->whereHas('materias', function($q) use ($request) {
                $q->where('sigla', 'ILIKE', '%' . $request->sigla_materia . '%');
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

            // Asignar materias si se seleccionaron
            if ($request->has('materias') && is_array($request->materias)) {
                $grupo->materias()->sync($request->materias);
            }

            Bitacora::registrar(
                'Registro de grupo',
                true,
                'Se registró el grupo: ' . $grupo->sigla,
                auth()->id()
            );

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo registrado correctamente');
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
        $grupo = Grupo::with(['materias', 'horarios', 'docentes'])->where('sigla', $sigla)->firstOrFail();

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
        $grupo = Grupo::with(['materias', 'docentes'])->where('sigla', $sigla)->firstOrFail();
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
            // Actualizar materias
            if ($request->has('materias') && is_array($request->materias)) {
                $grupo->materias()->sync($request->materias);
            } else {
                $grupo->materias()->detach();
            }

            Bitacora::registrar(
                'Actualización de grupo',
                true,
                'Se actualizó el grupo: ' . $grupo->sigla,
                auth()->id()
            );

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo actualizado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al actualizar grupo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar el grupo'])
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
     * Mostrar formulario para asignar docentes a un grupo
     */
    public function asignarDocentes($sigla)
    {
        $grupo = Grupo::with(['materias', 'docentes'])->where('sigla', $sigla)->firstOrFail();
        
        // Obtener todos los usuarios con rol de Docente
        $docentes = Usuario::whereHas('roles', function($query) {
            $query->where('descripcion', 'Docente');
        })->orderBy('nombre')->get();

        return view('grupos.asignar-docentes', compact('grupo', 'docentes'));
    }

    /**
     * Guardar la asignación de docentes a un grupo
     */
    public function guardarDocentes(Request $request, $sigla)
    {
        $grupo = Grupo::where('sigla', $sigla)->firstOrFail();

        $request->validate([
            'docentes' => 'nullable|array',
            'docentes.*' => 'exists:usuario,id',
        ], [
            'docentes.*.exists' => 'Uno o más docentes seleccionados no son válidos',
        ]);

        try {
            // Sincronizar docentes (elimina los que no están y añade los nuevos)
            if ($request->has('docentes') && is_array($request->docentes)) {
                $grupo->docentes()->sync($request->docentes);
                $mensaje = 'Docentes asignados correctamente al grupo ' . $grupo->sigla;
            } else {
                $grupo->docentes()->detach();
                $mensaje = 'Se eliminaron todos los docentes del grupo ' . $grupo->sigla;
            }

            Bitacora::registrar(
                'Asignación de docentes a grupo',
                true,
                $mensaje,
                auth()->id()
            );

            return redirect()->route('grupos.show', $grupo->sigla)
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al asignar docentes',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al asignar docentes'])
                ->withInput();
        }
    }
}


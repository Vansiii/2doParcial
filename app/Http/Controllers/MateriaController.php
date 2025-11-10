<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Semestre;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    /**
     * CU07: Gestionar Materias - Listar
     * Actores: Administrador, Coordinador
     */
    public function index(Request $request)
    {
        $query = Materia::query();

        // Filtros de búsqueda
        if ($request->filled('sigla')) {
            $query->where('sigla', 'ILIKE', '%' . $request->sigla . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('nombre', 'ILIKE', '%' . $request->nombre . '%');
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        $materias = $query->orderBy('sigla')->paginate(10);
        
        // Obtener niveles únicos para filtro
        $niveles = Materia::distinct()->pluck('nivel')->sort();

        Bitacora::registrar(
            'Consulta de materias',
            true,
            'Usuario consultó la lista de materias',
            auth()->id()
        );

        return view('materias.index', compact('materias', 'niveles'));
    }

    /**
     * Mostrar formulario para crear materia
     */
    public function create()
    {
        return view('materias.create');
    }

    /**
     * CU07: Registrar Materia
     */
    public function store(Request $request)
    {
        $request->validate([
            'sigla' => 'required|string|max:6|unique:materia,sigla',
            'nombre' => 'required|string|max:30',
            'nivel' => 'nullable|integer|min:0|max:10',
        ], [
            'sigla.required' => 'La sigla es obligatoria',
            'sigla.unique' => 'Esta sigla ya está registrada',
            'nombre.required' => 'El nombre es obligatorio',
            'nivel.integer' => 'El nivel debe ser un número entero',
        ]);

        try {
            $materia = Materia::create([
                'sigla' => strtoupper($request->sigla),
                'nombre' => $request->nombre,
                'nivel' => $request->nivel ?? 0,
            ]);

            Bitacora::registrar(
                'Registro de materia',
                true,
                'Se registró la materia: ' . $materia->sigla . ' - ' . $materia->nombre . ' (Nivel: ' . $materia->nivel . ')',
                auth()->id()
            );

            return redirect()->route('materias.index')
                ->with('success', 'Materia registrada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al registrar materia',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar la materia'])
                ->withInput();
        }
    }

    /**
     * Mostrar una materia específica
     */
    public function show($sigla)
    {
        $materia = Materia::with(['periodos', 'grupos.docentes'])->findOrFail($sigla);

        Bitacora::registrar(
            'Consulta de materia',
            true,
            'Usuario consultó la materia: ' . $sigla,
            auth()->id()
        );

        return view('materias.show', compact('materia'));
    }

    /**
     * Mostrar formulario para editar materia
     */
    public function edit($sigla)
    {
        $materia = Materia::findOrFail($sigla);
        return view('materias.edit', compact('materia'));
    }

    /**
     * CU07: Actualizar Materia
     */
    public function update(Request $request, $sigla)
    {
        $materia = Materia::findOrFail($sigla);

        $request->validate([
            'nombre' => 'required|string|max:30',
            'nivel' => 'nullable|integer|min:0|max:10',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nivel.integer' => 'El nivel debe ser un número entero',
        ]);

        try {
            $materia->nombre = $request->nombre;
            $materia->nivel = $request->nivel ?? 0;
            $materia->save();

            Bitacora::registrar(
                'Actualización de materia',
                true,
                'Se actualizó la materia: ' . $materia->sigla . ' (Nivel: ' . $materia->nivel . ')',
                auth()->id()
            );

            return redirect()->route('materias.index')
                ->with('success', 'Materia actualizada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al actualizar materia',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar la materia'])
                ->withInput();
        }
    }

    /**
     * CU07: Eliminar Materia
     */
    public function destroy($sigla)
    {
        $materia = Materia::findOrFail($sigla);

        try {
            $nombreMateria = $materia->sigla . ' - ' . $materia->nombre;
            $materia->delete();

            Bitacora::registrar(
                'Eliminación de materia',
                true,
                'Se eliminó la materia: ' . $nombreMateria,
                auth()->id()
            );

            return redirect()->route('materias.index')
                ->with('success', 'Materia eliminada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar materia',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar la materia. Puede tener horarios o grupos asignados.']);
        }
    }
}

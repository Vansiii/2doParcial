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
        $query = Materia::with('semestre');

        // Filtros de búsqueda
        if ($request->filled('sigla')) {
            $query->where('sigla', 'ILIKE', '%' . $request->sigla . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('nombre', 'ILIKE', '%' . $request->nombre . '%');
        }

        if ($request->filled('semestre')) {
            $query->where('id_semestre', $request->semestre);
        }

        $materias = $query->orderBy('sigla')->paginate(10);
        $semestres = Semestre::orderBy('fechaini', 'desc')->get();

        Bitacora::registrar(
            'Consulta de materias',
            true,
            'Usuario consultó la lista de materias',
            auth()->id()
        );

        return view('materias.index', compact('materias', 'semestres'));
    }

    /**
     * Mostrar formulario para crear materia
     */
    public function create()
    {
        $semestres = Semestre::orderBy('fechaini', 'desc')->get();
        return view('materias.create', compact('semestres'));
    }

    /**
     * CU07: Registrar Materia
     */
    public function store(Request $request)
    {
        $request->validate([
            'sigla' => 'required|string|max:6|unique:materia,sigla',
            'nombre' => 'required|string|max:30',
            'id_semestre' => 'nullable|exists:semestre,id',
        ], [
            'sigla.required' => 'La sigla es obligatoria',
            'sigla.unique' => 'Esta sigla ya está registrada',
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        try {
            $materia = Materia::create([
                'sigla' => strtoupper($request->sigla),
                'nombre' => $request->nombre,
                'id_semestre' => $request->id_semestre,
            ]);

            Bitacora::registrar(
                'Registro de materia',
                true,
                'Se registró la materia: ' . $materia->sigla . ' - ' . $materia->nombre,
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
        $materia = Materia::with(['semestre', 'docentes', 'grupos'])->findOrFail($sigla);

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
        $semestres = Semestre::orderBy('fechaini', 'desc')->get();
        return view('materias.edit', compact('materia', 'semestres'));
    }

    /**
     * CU07: Actualizar Materia
     */
    public function update(Request $request, $sigla)
    {
        $materia = Materia::findOrFail($sigla);

        $request->validate([
            'nombre' => 'required|string|max:30',
            'id_semestre' => 'nullable|exists:semestre,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        try {
            $materia->nombre = $request->nombre;
            $materia->id_semestre = $request->id_semestre;
            $materia->save();

            Bitacora::registrar(
                'Actualización de materia',
                true,
                'Se actualizó la materia: ' . $materia->sigla,
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

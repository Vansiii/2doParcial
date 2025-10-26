<?php

namespace App\Http\Controllers;

use App\Models\Semestre;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class SemestreController extends Controller
{
    /**
     * Listar semestres
     */
    public function index(Request $request)
    {
        $query = Semestre::query();

        // Filtros de búsqueda
        if ($request->filled('abreviatura')) {
            $query->where('abreviatura', 'ILIKE', '%' . $request->abreviatura . '%');
        }

        $semestres = $query->orderBy('fechaini', 'desc')->paginate(10);

        Bitacora::registrar(
            'Consulta de semestres',
            true,
            'Usuario consultó la lista de semestres',
            auth()->id()
        );

        return view('semestres.index', compact('semestres'));
    }

    /**
     * Mostrar formulario para crear semestre
     */
    public function create()
    {
        return view('semestres.create');
    }

    /**
     * Registrar nuevo semestre
     */
    public function store(Request $request)
    {
        $request->validate([
            'abreviatura' => 'required|string|max:10|unique:semestre,abreviatura',
            'fechaini' => 'required|date',
            'fechafin' => 'required|date|after:fechaini',
        ], [
            'abreviatura.required' => 'La abreviatura es obligatoria',
            'abreviatura.unique' => 'Esta abreviatura ya está registrada',
            'abreviatura.max' => 'La abreviatura no puede tener más de 10 caracteres',
            'fechaini.required' => 'La fecha de inicio es obligatoria',
            'fechafin.required' => 'La fecha de fin es obligatoria',
            'fechafin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        try {
            $semestre = Semestre::create([
                'abreviatura' => strtoupper($request->abreviatura),
                'fechaini' => $request->fechaini,
                'fechafin' => $request->fechafin,
            ]);

            Bitacora::registrar(
                'Registro de semestre',
                true,
                'Se registró el semestre: ' . $semestre->abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', 'Semestre registrado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al registrar semestre',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar el semestre'])
                ->withInput();
        }
    }

    /**
     * Mostrar un semestre específico
     */
    public function show($id)
    {
        $semestre = Semestre::with('materias')->findOrFail($id);

        Bitacora::registrar(
            'Consulta de semestre',
            true,
            'Usuario consultó el semestre: ' . $semestre->abreviatura,
            auth()->id()
        );

        return view('semestres.show', compact('semestre'));
    }

    /**
     * Mostrar formulario para editar semestre
     */
    public function edit($id)
    {
        $semestre = Semestre::findOrFail($id);
        return view('semestres.edit', compact('semestre'));
    }

    /**
     * Actualizar semestre
     */
    public function update(Request $request, $id)
    {
        $semestre = Semestre::findOrFail($id);

        $request->validate([
            'abreviatura' => 'required|string|max:10|unique:semestre,abreviatura,' . $id,
            'fechaini' => 'required|date',
            'fechafin' => 'required|date|after:fechaini',
        ], [
            'abreviatura.required' => 'La abreviatura es obligatoria',
            'abreviatura.unique' => 'Esta abreviatura ya está registrada',
            'fechaini.required' => 'La fecha de inicio es obligatoria',
            'fechafin.required' => 'La fecha de fin es obligatoria',
            'fechafin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        try {
            $semestre->abreviatura = strtoupper($request->abreviatura);
            $semestre->fechaini = $request->fechaini;
            $semestre->fechafin = $request->fechafin;
            $semestre->save();

            Bitacora::registrar(
                'Actualización de semestre',
                true,
                'Se actualizó el semestre: ' . $semestre->abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', 'Semestre actualizado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al actualizar semestre',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar el semestre'])
                ->withInput();
        }
    }

    /**
     * Eliminar semestre
     */
    public function destroy($id)
    {
        $semestre = Semestre::findOrFail($id);

        try {
            $abreviatura = $semestre->abreviatura;
            $semestre->delete();

            Bitacora::registrar(
                'Eliminación de semestre',
                true,
                'Se eliminó el semestre: ' . $abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', 'Semestre eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar semestre',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el semestre. Puede tener materias asignadas.']);
        }
    }
}

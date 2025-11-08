<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller
{
    /**
     * CU19: Gestionar Carreras - Listar
     * Actores: Administrador, Coordinador
     */
    public function index(Request $request)
    {
        $query = Carrera::with(['materias']);

        // Filtros de búsqueda
        if ($request->filled('cod')) {
            $query->where('cod', 'ILIKE', '%' . $request->cod . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('nombre', 'ILIKE', '%' . $request->nombre . '%');
        }

        $carreras = $query->orderBy('cod')->paginate(10);

        Bitacora::registrar(
            'Consulta de carreras',
            true,
            'Usuario consultó la lista de carreras',
            auth()->id()
        );

        return view('carreras.index', compact('carreras'));
    }

    /**
     * Mostrar formulario para crear carrera
     */
    public function create()
    {
        $materias = Materia::orderBy('sigla')->get();
        return view('carreras.create', compact('materias'));
    }

    /**
     * CU19: Registrar Carrera
     */
    public function store(Request $request)
    {
        $request->validate([
            'cod' => 'required|string|max:7|unique:carrera,cod',
            'nombre' => 'required|string|max:50',
            'materias' => 'nullable|array',
            'materias.*' => 'exists:materia,sigla',
        ], [
            'cod.required' => 'El código de la carrera es obligatorio',
            'cod.unique' => 'Este código ya está registrado',
            'cod.max' => 'El código no puede tener más de 7 caracteres',
            'nombre.required' => 'El nombre de la carrera es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
        ]);

        try {
            DB::beginTransaction();

            $carrera = Carrera::create([
                'cod' => strtoupper($request->cod),
                'nombre' => $request->nombre,
            ]);

            // Asignar materias si se seleccionaron
            if ($request->has('materias') && is_array($request->materias)) {
                $carrera->materias()->sync($request->materias);
            }

            DB::commit();

            Bitacora::registrar(
                'Registro de carrera',
                true,
                'Se registró la carrera: ' . $carrera->cod . ' - ' . $carrera->nombre,
                auth()->id()
            );

            return redirect()->route('carreras.index')
                ->with('success', 'Carrera registrada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            Bitacora::registrar(
                'Error al registrar carrera',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar la carrera: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar una carrera específica
     */
    public function show($cod)
    {
        $carrera = Carrera::with(['materias.semestre'])
            ->where('cod', $cod)
            ->firstOrFail();

        Bitacora::registrar(
            'Consulta de carrera',
            true,
            'Usuario consultó la carrera: ' . $cod,
            auth()->id()
        );

        return view('carreras.show', compact('carrera'));
    }

    /**
     * Mostrar formulario para editar carrera
     */
    public function edit($cod)
    {
        $carrera = Carrera::with(['materias'])
            ->where('cod', $cod)
            ->firstOrFail();
        $materias = Materia::orderBy('sigla')->get();
        return view('carreras.edit', compact('carrera', 'materias'));
    }

    /**
     * CU19: Actualizar Carrera
     */
    public function update(Request $request, $cod)
    {
        $carrera = Carrera::where('cod', $cod)->firstOrFail();

        $request->validate([
            'nombre' => 'required|string|max:50',
            'materias' => 'nullable|array',
            'materias.*' => 'exists:materia,sigla',
        ], [
            'nombre.required' => 'El nombre de la carrera es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
        ]);

        try {
            DB::beginTransaction();

            $carrera->update([
                'nombre' => $request->nombre,
            ]);

            // Actualizar materias
            if ($request->has('materias') && is_array($request->materias)) {
                $carrera->materias()->sync($request->materias);
            } else {
                $carrera->materias()->detach();
            }

            DB::commit();

            Bitacora::registrar(
                'Actualización de carrera',
                true,
                'Se actualizó la carrera: ' . $carrera->cod . ' - ' . $carrera->nombre,
                auth()->id()
            );

            return redirect()->route('carreras.index')
                ->with('success', 'Carrera actualizada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            Bitacora::registrar(
                'Error al actualizar carrera',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar la carrera: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * CU19: Eliminar Carrera
     */
    public function destroy($cod)
    {
        $carrera = Carrera::where('cod', $cod)->firstOrFail();

        try {
            $nombre = $carrera->nombre;
            $carrera->delete();

            Bitacora::registrar(
                'Eliminación de carrera',
                true,
                'Se eliminó la carrera: ' . $cod . ' - ' . $nombre,
                auth()->id()
            );

            return redirect()->route('carreras.index')
                ->with('success', 'Carrera eliminada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar carrera',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar la carrera. Puede tener datos relacionados.']);
        }
    }
}

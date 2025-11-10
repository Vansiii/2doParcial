<?php

namespace App\Http\Controllers;

use App\Models\Semestre;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SemestreController extends Controller
{
    /**
     * Listar períodos académicos
     */
    public function index(Request $request)
    {
        $query = Semestre::withCount(['grupos', 'materias']);

        // Filtros de búsqueda
        if ($request->filled('abreviatura')) {
            $query->where('abreviatura', 'ILIKE', '%' . $request->abreviatura . '%');
        }

        if ($request->filled('gestion')) {
            $query->where('gestion', $request->gestion);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        $semestres = $query->orderBy('gestion', 'desc')
                          ->orderBy('periodo', 'desc')
                          ->paginate(10);
        
        // Obtener gestiones únicas para filtro
        $gestiones = Semestre::distinct()->pluck('gestion')->sort()->reverse();

        Bitacora::registrar(
            'Consulta de períodos académicos',
            true,
            'Usuario consultó la lista de períodos académicos',
            auth()->id()
        );

        return view('semestres.index', compact('semestres', 'gestiones'));
    }

    /**
     * Mostrar formulario para crear semestre
     */
    public function create()
    {
        return view('semestres.create');
    }

    /**
     * Registrar nuevo período académico
     */
    public function store(Request $request)
    {
        $request->validate([
            'gestion' => 'required|integer|min:2020|max:2100',
            'periodo' => 'required|integer|in:1,2',
            'fechaini' => 'required|date',
            'fechafin' => 'required|date|after:fechaini',
            'activo' => 'nullable|boolean',
        ], [
            'gestion.required' => 'La gestión es obligatoria',
            'gestion.integer' => 'La gestión debe ser un año válido',
            'periodo.required' => 'El período es obligatorio',
            'periodo.in' => 'El período debe ser 1 o 2',
            'fechaini.required' => 'La fecha de inicio es obligatoria',
            'fechafin.required' => 'La fecha de fin es obligatoria',
            'fechafin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        try {
            DB::beginTransaction();

            // Verificar si ya existe este período
            $existe = Semestre::where('gestion', $request->gestion)
                              ->where('periodo', $request->periodo)
                              ->exists();
            
            if ($existe) {
                throw new \Exception('Este período académico ya está registrado');
            }

            // Si se marca como activo, desactivar otros
            if ($request->activo) {
                Semestre::where('activo', true)->update(['activo' => false]);
            }

            $abreviatura = $request->periodo . '-' . $request->gestion;

            $semestre = Semestre::create([
                'gestion' => $request->gestion,
                'periodo' => $request->periodo,
                'abreviatura' => $abreviatura,
                'fechaini' => $request->fechaini,
                'fechafin' => $request->fechafin,
                'activo' => $request->activo ?? false,
            ]);

            DB::commit();

            Bitacora::registrar(
                'Registro de período académico',
                true,
                'Se registró el período: ' . $semestre->abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', 'Período académico registrado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Bitacora::registrar(
                'Error al registrar período académico',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar un período académico específico
     */
    public function show($id)
    {
        $semestre = Semestre::with(['materias', 'grupos.grupoMaterias'])->findOrFail($id);

        Bitacora::registrar(
            'Consulta de período académico',
            true,
            'Usuario consultó el período: ' . $semestre->abreviatura,
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
     * Actualizar período académico
     */
    public function update(Request $request, $id)
    {
        $semestre = Semestre::findOrFail($id);

        $request->validate([
            'gestion' => 'required|integer|min:2020|max:2100',
            'periodo' => 'required|integer|in:1,2',
            'fechaini' => 'required|date',
            'fechafin' => 'required|date|after:fechaini',
            'activo' => 'nullable|boolean',
        ], [
            'gestion.required' => 'La gestión es obligatoria',
            'periodo.required' => 'El período es obligatorio',
            'periodo.in' => 'El período debe ser 1 o 2',
            'fechaini.required' => 'La fecha de inicio es obligatoria',
            'fechafin.required' => 'La fecha de fin es obligatoria',
            'fechafin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        try {
            DB::beginTransaction();

            // Verificar si otro período tiene la misma gestion+periodo
            $existe = Semestre::where('gestion', $request->gestion)
                              ->where('periodo', $request->periodo)
                              ->where('id', '!=', $id)
                              ->exists();
            
            if ($existe) {
                throw new \Exception('Ya existe otro período con esta gestión y período');
            }

            // Si se marca como activo, desactivar otros
            if ($request->activo && !$semestre->activo) {
                Semestre::where('activo', true)->update(['activo' => false]);
            }

            $semestre->gestion = $request->gestion;
            $semestre->periodo = $request->periodo;
            $semestre->abreviatura = $request->periodo . '-' . $request->gestion;
            $semestre->fechaini = $request->fechaini;
            $semestre->fechafin = $request->fechafin;
            $semestre->activo = $request->activo ?? false;
            $semestre->save();

            DB::commit();

            Bitacora::registrar(
                'Actualización de período académico',
                true,
                'Se actualizó el período: ' . $semestre->abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', 'Período académico actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Bitacora::registrar(
                'Error al actualizar período académico',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Eliminar período académico
     */
    public function destroy($id)
    {
        $semestre = Semestre::findOrFail($id);

        // Verificar que no sea el período activo
        if ($semestre->activo) {
            return back()->withErrors(['error' => 'No se puede eliminar el período académico activo']);
        }

        try {
            $abreviatura = $semestre->abreviatura;
            
            // Verificar si tiene grupos o materias asignadas
            $tieneGrupos = $semestre->grupos()->exists();
            $tieneMaterias = $semestre->materias()->exists();
            
            if ($tieneGrupos || $tieneMaterias) {
                throw new \Exception('No se puede eliminar. Tiene grupos o materias asignadas.');
            }
            
            $semestre->delete();

            Bitacora::registrar(
                'Eliminación de período académico',
                true,
                'Se eliminó el período: ' . $abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', 'Período académico eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar período académico',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Activar/Desactivar período académico
     */
    public function toggleActivo($id)
    {
        try {
            DB::beginTransaction();
            
            $semestre = Semestre::findOrFail($id);
            
            if (!$semestre->activo) {
                // Desactivar todos los demás
                Semestre::where('activo', true)->update(['activo' => false]);
                $semestre->activo = true;
                $mensaje = 'Período académico activado correctamente';
            } else {
                $semestre->activo = false;
                $mensaje = 'Período académico desactivado correctamente';
            }
            
            $semestre->save();
            
            DB::commit();

            Bitacora::registrar(
                'Cambio de estado de período académico',
                true,
                'Se ' . ($semestre->activo ? 'activó' : 'desactivó') . ' el período: ' . $semestre->abreviatura,
                auth()->id()
            );

            return redirect()->route('semestres.index')
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => 'Error al cambiar el estado del período']);
        }
    }
}

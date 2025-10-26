<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Modulo;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    /**
     * CU08: Gestionar Aulas - Listar
     * Actores: Administrador, Coordinador
     */
    public function index(Request $request)
    {
        $query = Aula::with('modulo');

        // Filtros de búsqueda
        if ($request->filled('nroaula')) {
            $query->where('nroaula', 'ILIKE', '%' . $request->nroaula . '%');
        }

        if ($request->filled('piso')) {
            $query->where('piso', $request->piso);
        }

        if ($request->filled('capacidad')) {
            $query->where('capacidad', '>=', $request->capacidad);
        }

        if ($request->filled('id_modulo')) {
            $query->where('id_modulo', $request->id_modulo);
        }

        $aulas = $query->orderBy('nroaula')->paginate(10);
        $modulos = Modulo::orderBy('ubicacion')->get();

        Bitacora::registrar(
            'Consulta de aulas',
            true,
            'Usuario consultó la lista de aulas',
            auth()->id()
        );

        return view('aulas.index', compact('aulas', 'modulos'));
    }

    /**
     * Mostrar formulario para crear aula
     */
    public function create()
    {
        $modulos = Modulo::orderBy('ubicacion')->get();
        return view('aulas.create', compact('modulos'));
    }

    /**
     * CU08: Registrar Aula
     */
    public function store(Request $request)
    {
        $request->validate([
            'nroaula' => 'required|integer|unique:aula,nroaula',
            'capacidad' => 'nullable|integer|min:1|max:200',
            'piso' => 'required|integer|min:1|max:10',
            'id_modulo' => 'nullable|exists:modulo,codigo',
        ], [
            'nroaula.required' => 'El número de aula es obligatorio',
            'nroaula.unique' => 'Este número de aula ya está registrado',
            'nroaula.integer' => 'El número de aula debe ser un entero',
            'capacidad.min' => 'La capacidad debe ser al menos 1',
            'capacidad.max' => 'La capacidad no puede ser mayor a 200',
            'piso.required' => 'El piso es obligatorio',
        ]);

        try {
            $aula = Aula::create([
                'nroaula' => $request->nroaula,
                'capacidad' => $request->capacidad,
                'piso' => $request->piso,
                'id_modulo' => $request->id_modulo,
            ]);

            Bitacora::registrar(
                'Registro de aula',
                true,
                'Se registró el aula: ' . $aula->nroaula . ' - Piso ' . $aula->piso . ' - Capacidad: ' . $aula->capacidad,
                auth()->id()
            );

            return redirect()->route('aulas.index')
                ->with('success', 'Aula registrada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al registrar aula',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar el aula'])
                ->withInput();
        }
    }

    /**
     * Mostrar un aula específica
     */
    public function show($nroaula)
    {
        $aula = Aula::with(['modulo', 'horarios'])->findOrFail($nroaula);

        Bitacora::registrar(
            'Consulta de aula',
            true,
            'Usuario consultó el aula: ' . $nroaula,
            auth()->id()
        );

        return view('aulas.show', compact('aula'));
    }

    /**
     * Mostrar formulario para editar aula
     */
    public function edit($nroaula)
    {
        $aula = Aula::findOrFail($nroaula);
        $modulos = Modulo::orderBy('ubicacion')->get();
        return view('aulas.edit', compact('aula', 'modulos'));
    }

    /**
     * CU08: Actualizar Aula
     */
    public function update(Request $request, $nroaula)
    {
        $aula = Aula::findOrFail($nroaula);

        $request->validate([
            'capacidad' => 'nullable|integer|min:1|max:200',
            'piso' => 'required|integer|min:1|max:10',
            'id_modulo' => 'nullable|exists:modulo,codigo',
        ], [
            'capacidad.min' => 'La capacidad debe ser al menos 1',
            'capacidad.max' => 'La capacidad no puede ser mayor a 200',
            'piso.required' => 'El piso es obligatorio',
        ]);

        try {
            $aula->capacidad = $request->capacidad;
            $aula->piso = $request->piso;
            $aula->id_modulo = $request->id_modulo;
            $aula->save();

            Bitacora::registrar(
                'Actualización de aula',
                true,
                'Se actualizó el aula: ' . $aula->nroaula,
                auth()->id()
            );

            return redirect()->route('aulas.index')
                ->with('success', 'Aula actualizada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al actualizar aula',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar el aula'])
                ->withInput();
        }
    }

    /**
     * CU08: Eliminar Aula
     */
    public function destroy($nroaula)
    {
        $aula = Aula::findOrFail($nroaula);

        try {
            $infoAula = $aula->nroaula . ' - Piso ' . $aula->piso;
            $aula->delete();

            Bitacora::registrar(
                'Eliminación de aula',
                true,
                'Se eliminó el aula: ' . $infoAula,
                auth()->id()
            );

            return redirect()->route('aulas.index')
                ->with('success', 'Aula eliminada correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar aula',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el aula. Puede tener horarios asignados.']);
        }
    }
}

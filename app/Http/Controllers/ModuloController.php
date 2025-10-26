<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    /**
     * Listar módulos
     */
    public function index(Request $request)
    {
        $query = Modulo::withCount('aulas');

        // Filtros de búsqueda
        if ($request->filled('ubicacion')) {
            $query->where('ubicacion', 'ILIKE', '%' . $request->ubicacion . '%');
        }

        $modulos = $query->orderBy('ubicacion')->paginate(10);

        Bitacora::registrar(
            'Consulta de módulos',
            true,
            'Usuario consultó la lista de módulos',
            auth()->id()
        );

        return view('modulos.index', compact('modulos'));
    }

    /**
     * Mostrar formulario para crear módulo
     */
    public function create()
    {
        return view('modulos.create');
    }

    /**
     * Registrar nuevo módulo
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|integer|unique:modulo,codigo',
            'ubicacion' => 'required|string|max:50',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.integer' => 'El código debe ser un número entero',
            'codigo.unique' => 'Este código ya está registrado',
            'ubicacion.required' => 'La ubicación es obligatoria',
            'ubicacion.max' => 'La ubicación no puede tener más de 50 caracteres',
        ]);

        try {
            $modulo = Modulo::create([
                'codigo' => $request->codigo,
                'ubicacion' => $request->ubicacion,
            ]);

            Bitacora::registrar(
                'Registro de módulo',
                true,
                'Se registró el módulo: ' . $modulo->ubicacion . ' (Código: ' . $modulo->codigo . ')',
                auth()->id()
            );

            return redirect()->route('modulos.index')
                ->with('success', 'Módulo registrado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al registrar módulo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar el módulo'])
                ->withInput();
        }
    }

    /**
     * Mostrar un módulo específico
     */
    public function show($codigo)
    {
        $modulo = Modulo::with('aulas')->findOrFail($codigo);

        Bitacora::registrar(
            'Consulta de módulo',
            true,
            'Usuario consultó el módulo: ' . $modulo->ubicacion,
            auth()->id()
        );

        return view('modulos.show', compact('modulo'));
    }

    /**
     * Mostrar formulario para editar módulo
     */
    public function edit($codigo)
    {
        $modulo = Modulo::findOrFail($codigo);
        return view('modulos.edit', compact('modulo'));
    }

    /**
     * Actualizar módulo
     */
    public function update(Request $request, $codigo)
    {
        $modulo = Modulo::findOrFail($codigo);

        $request->validate([
            'ubicacion' => 'required|string|max:50',
        ], [
            'ubicacion.required' => 'La ubicación es obligatoria',
            'ubicacion.max' => 'La ubicación no puede tener más de 50 caracteres',
        ]);

        try {
            $modulo->ubicacion = $request->ubicacion;
            $modulo->save();

            Bitacora::registrar(
                'Actualización de módulo',
                true,
                'Se actualizó el módulo: ' . $modulo->ubicacion . ' (Código: ' . $modulo->codigo . ')',
                auth()->id()
            );

            return redirect()->route('modulos.index')
                ->with('success', 'Módulo actualizado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al actualizar módulo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar el módulo'])
                ->withInput();
        }
    }

    /**
     * Eliminar módulo
     */
    public function destroy($codigo)
    {
        $modulo = Modulo::findOrFail($codigo);

        try {
            $ubicacion = $modulo->ubicacion;
            $modulo->delete();

            Bitacora::registrar(
                'Eliminación de módulo',
                true,
                'Se eliminó el módulo: ' . $ubicacion,
                auth()->id()
            );

            return redirect()->route('modulos.index')
                ->with('success', 'Módulo eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar módulo',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el módulo. Puede tener aulas asignadas.']);
        }
    }
}

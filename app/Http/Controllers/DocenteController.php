<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DocenteController extends Controller
{
    /**
     * CU05: Consultar Docentes
     * Actores: Administrador, Coordinador
     */
    public function index(Request $request)
    {
        $query = Usuario::whereHas('roles', function ($q) {
            $q->where('descripcion', 'Docente');
        })->with('roles');

        // Filtros de búsqueda
        if ($request->filled('nombre')) {
            $query->where('nombre', 'ILIKE', '%' . $request->nombre . '%');
        }

        if ($request->filled('correo')) {
            $query->where('correo', 'ILIKE', '%' . $request->correo . '%');
        }

        if ($request->filled('telefono')) {
            $query->where('telefono', 'LIKE', '%' . $request->telefono . '%');
        }

        $docentes = $query->orderBy('nombre')->paginate(10);

        Bitacora::registrar(
            'Consulta de docentes',
            true,
            'Usuario consultó la lista de docentes',
            auth()->id()
        );

        return view('docentes.index', compact('docentes'));
    }

    /**
     * Mostrar formulario para crear docente
     */
    public function create()
    {
        return view('docentes.create');
    }

    /**
     * CU04: Registrar Docente
     * Actores: Administrador, Coordinador
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:40',
            'correo' => 'required|email|max:40|unique:usuario,correo',
            'telefono' => 'required|numeric|digits_between:8,15',
            'password' => 'required|min:6|confirmed',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no debe exceder 40 caracteres',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Ingrese un correo válido',
            'correo.unique' => 'Este correo ya está registrado',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.numeric' => 'El teléfono debe ser numérico',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        DB::beginTransaction();

        try {
            // Crear usuario
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'passw' => Hash::make($request->password),
            ]);

            // Obtener o crear el rol de Docente
            $rolDocente = Rol::firstOrCreate(
                ['descripcion' => 'Docente']
            );

            // Asignar rol de Docente
            $usuario->roles()->attach($rolDocente->id, [
                'detalle' => $request->detalle ?? 'Docente registrado por ' . auth()->user()->nombre
            ]);

            Bitacora::registrar(
                'Registro de docente',
                true,
                'Se registró al docente: ' . $usuario->nombre . ' (ID: ' . $usuario->id . ')',
                auth()->id()
            );

            DB::commit();

            return redirect()->route('docentes.index')
                ->with('success', 'Docente registrado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            Bitacora::registrar(
                'Error al registrar docente',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar el docente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar un docente específico
     */
    public function show($id)
    {
        $docente = Usuario::whereHas('roles', function ($q) {
            $q->where('descripcion', 'Docente');
        })->with('roles')->findOrFail($id);

        Bitacora::registrar(
            'Consulta de docente',
            true,
            'Usuario consultó el docente ID: ' . $id,
            auth()->id()
        );

        return view('docentes.show', compact('docente'));
    }

    /**
     * Mostrar formulario para editar docente
     */
    public function edit($id)
    {
        $docente = Usuario::whereHas('roles', function ($q) {
            $q->where('descripcion', 'Docente');
        })->findOrFail($id);

        return view('docentes.edit', compact('docente'));
    }

    /**
     * CU06: Modificar Docente
     * Actores: Administrador, Coordinador
     */
    public function update(Request $request, $id)
    {
        $docente = Usuario::whereHas('roles', function ($q) {
            $q->where('descripcion', 'Docente');
        })->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:40',
            'correo' => 'required|email|max:40|unique:usuario,correo,' . $id,
            'telefono' => 'required|numeric|digits_between:8,15',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'correo.required' => 'El correo es obligatorio',
            'correo.unique' => 'Este correo ya está registrado',
            'telefono.required' => 'El teléfono es obligatorio',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        try {
            $docente->nombre = $request->nombre;
            $docente->correo = $request->correo;
            $docente->telefono = $request->telefono;

            if ($request->filled('password')) {
                $docente->passw = Hash::make($request->password);
            }

            $docente->save();

            Bitacora::registrar(
                'Actualización de docente',
                true,
                'Se actualizó al docente: ' . $docente->nombre . ' (ID: ' . $docente->id . ')',
                auth()->id()
            );

            return redirect()->route('docentes.index')
                ->with('success', 'Docente actualizado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al actualizar docente',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar el docente'])
                ->withInput();
        }
    }

    /**
     * Eliminar docente
     */
    public function destroy($id)
    {
        $docente = Usuario::whereHas('roles', function ($q) {
            $q->where('descripcion', 'Docente');
        })->findOrFail($id);

        try {
            $nombreDocente = $docente->nombre;
            $docente->delete();

            Bitacora::registrar(
                'Eliminación de docente',
                true,
                'Se eliminó al docente: ' . $nombreDocente . ' (ID: ' . $id . ')',
                auth()->id()
            );

            return redirect()->route('docentes.index')
                ->with('success', 'Docente eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar docente',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el docente']);
        }
    }
}

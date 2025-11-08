<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * CU18: Gestionar Usuarios - Listar
     * Actor: Administrador
     */
    public function index(Request $request)
    {
        $query = Usuario::with(['roles']);

        // Filtros de búsqueda
        if ($request->filled('codigo')) {
            $query->where('codigo', 'LIKE', '%' . $request->codigo . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('nombre', 'ILIKE', '%' . $request->nombre . '%');
        }

        if ($request->filled('rol')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('id', $request->rol);
            });
        }

        $usuarios = $query->orderBy('nombre')->paginate(15);
        $roles = Rol::orderBy('descripcion')->get();

        Bitacora::registrar(
            'Consulta de usuarios',
            true,
            'Administrador consultó la lista de usuarios',
            auth()->id()
        );

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Mostrar formulario para crear usuario
     */
    public function create()
    {
        $roles = Rol::orderBy('descripcion')->get();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * CU18: Registrar Usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|numeric|unique:usuario,codigo',
            'nombre' => 'required|string|max:40',
            'correo' => 'required|email|max:40',
            'telefono' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:rol,id',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.numeric' => 'El código debe ser numérico',
            'codigo.unique' => 'Este código ya está registrado',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 40 caracteres',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Ingrese un correo válido',
            'correo.max' => 'El correo no puede tener más de 40 caracteres',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.numeric' => 'El teléfono debe ser numérico',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'roles.required' => 'Debe asignar al menos un rol',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'passw' => Hash::make($request->password),
            ]);

            // Asignar roles
            $usuario->roles()->sync($request->roles);

            DB::commit();

            Bitacora::registrar(
                'Registro de usuario',
                true,
                'Se registró el usuario: ' . $usuario->codigo . ' - ' . $usuario->nombre,
                auth()->id()
            );

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario registrado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            Bitacora::registrar(
                'Error al registrar usuario',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al registrar el usuario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        $usuario = Usuario::with(['roles'])->findOrFail($id);

        Bitacora::registrar(
            'Consulta de usuario',
            true,
            'Administrador consultó el usuario: ' . $usuario->codigo,
            auth()->id()
        );

        return view('usuarios.show', compact('usuario'));
    }

    /**
     * Mostrar formulario para editar usuario
     */
    public function edit($id)
    {
        $usuario = Usuario::with(['roles'])->findOrFail($id);
        $roles = Rol::orderBy('descripcion')->get();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * CU18: Actualizar Usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'codigo' => 'required|numeric|unique:usuario,codigo,' . $id,
            'nombre' => 'required|string|max:40',
            'correo' => 'required|email|max:40',
            'telefono' => 'required|numeric',
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:rol,id',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.numeric' => 'El código debe ser numérico',
            'codigo.unique' => 'Este código ya está registrado',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 40 caracteres',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Ingrese un correo válido',
            'correo.max' => 'El correo no puede tener más de 40 caracteres',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.numeric' => 'El teléfono debe ser numérico',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'roles.required' => 'Debe asignar al menos un rol',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
            ];

            // Solo actualizar contraseña si se proporcionó una nueva
            if ($request->filled('password')) {
                $data['passw'] = Hash::make($request->password);
            }

            $usuario->update($data);

            // Actualizar roles
            $usuario->roles()->sync($request->roles);

            DB::commit();

            Bitacora::registrar(
                'Actualización de usuario',
                true,
                'Se actualizó el usuario: ' . $usuario->codigo . ' - ' . $usuario->nombre,
                auth()->id()
            );

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            Bitacora::registrar(
                'Error al actualizar usuario',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al actualizar el usuario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * CU18: Eliminar Usuario
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        // No permitir eliminar al usuario actual
        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puede eliminar su propia cuenta.']);
        }

        try {
            $codigo = $usuario->codigo;
            $nombre = $usuario->nombre;
            $usuario->delete();

            Bitacora::registrar(
                'Eliminación de usuario',
                true,
                'Se eliminó el usuario: ' . $codigo . ' - ' . $nombre,
                auth()->id()
            );

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al eliminar usuario',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al eliminar el usuario. Puede tener datos relacionados.']);
        }
    }
}

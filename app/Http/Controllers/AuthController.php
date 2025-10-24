<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * CU01: Iniciar Sesión
     * Todos los actores: Administrador, Autoridad, Coordinador, Docente
     */
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ], [
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Ingrese un correo válido',
            'password.required' => 'La contraseña es obligatoria',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario) {
            Bitacora::registrar(
                'Intento de inicio de sesión',
                false,
                'Usuario no encontrado: ' . $request->correo,
                null
            );

            return back()->withErrors([
                'correo' => 'Las credenciales no coinciden con nuestros registros.',
            ])->withInput();
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $usuario->passw)) {
            Bitacora::registrar(
                'Intento de inicio de sesión',
                false,
                'Contraseña incorrecta para: ' . $request->correo,
                $usuario->id
            );

            return back()->withErrors([
                'correo' => 'Las credenciales no coinciden con nuestros registros.',
            ])->withInput();
        }

        // Autenticar usuario
        Auth::login($usuario, $request->filled('remember'));

        $request->session()->regenerate();

        // Registrar en bitácora
        Bitacora::registrar(
            'Inicio de sesión exitoso',
            true,
            'Usuario ' . $usuario->nombre . ' ha iniciado sesión',
            $usuario->id
        );

        return redirect()->intended('/dashboard')->with('success', 'Bienvenido ' . $usuario->nombre);
    }

    /**
     * CU02: Cerrar Sesión
     * Todos los actores: Administrador, Autoridad, Coordinador, Docente
     */
    public function logout(Request $request)
    {
        $usuario = Auth::user();

        if ($usuario) {
            Bitacora::registrar(
                'Cierre de sesión',
                true,
                'Usuario ' . $usuario->nombre . ' ha cerrado sesión',
                $usuario->id
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Sesión cerrada correctamente');
    }

    /**
     * Mostrar formulario de cambio de contraseña
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * CU03: Cambiar Contraseña
     * Todos los actores: Administrador, Autoridad, Coordinador, Docente
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria',
            'new_password.required' => 'La nueva contraseña es obligatoria',
            'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
            'new_password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        $usuario = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $usuario->passw)) {
            Bitacora::registrar(
                'Intento de cambio de contraseña',
                false,
                'Contraseña actual incorrecta',
                $usuario->id
            );

            return back()->withErrors([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        // Actualizar contraseña
        $usuario->passw = Hash::make($request->new_password);
        $usuario->save();

        Bitacora::registrar(
            'Cambio de contraseña',
            true,
            'Usuario ' . $usuario->nombre . ' cambió su contraseña',
            $usuario->id
        );

        return redirect('/dashboard')->with('success', 'Contraseña actualizada correctamente');
    }
}

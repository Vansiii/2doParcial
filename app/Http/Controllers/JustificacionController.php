<?php

namespace App\Http\Controllers;

use App\Models\Justificacion;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class JustificacionController extends Controller
{
    /**
     * CU16: Listar todas las justificaciones (Admin, Autoridad, Coordinador)
     */
    public function index(Request $request)
    {
        $query = Justificacion::with(['usuario', 'aprobadoPor']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('docente')) {
            $query->where('id_usuario', $request->docente);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }

        $justificaciones = $query->orderBy('created_at', 'desc')->paginate(15);

        $docentes = Usuario::whereHas('roles', function($q) {
            $q->where('descripcion', 'Docente');
        })->orderBy('nombre')->get();

        Bitacora::registrar(
            'Consulta de justificaciones',
            true,
            'Usuario consultó el listado de justificaciones',
            auth()->id()
        );

        return view('justificaciones.index', compact('justificaciones', 'docentes'));
    }

    /**
     * CU16: Mis justificaciones (Docente)
     */
    public function misJustificaciones(Request $request)
    {
        $usuario = auth()->user();

        $query = Justificacion::with(['aprobadoPor'])
                             ->where('id_usuario', $usuario->id);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }

        $justificaciones = $query->orderBy('created_at', 'desc')->paginate(15);

        Bitacora::registrar(
            'Consulta de justificaciones personales',
            true,
            'Docente consultó sus justificaciones',
            auth()->id()
        );

        return view('justificaciones.mis-justificaciones', compact('justificaciones'));
    }

    /**
     * CU16: Mostrar formulario para crear justificación
     */
    public function create()
    {
        return view('justificaciones.create');
    }

    /**
     * CU16: Guardar nueva justificación
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'motivo.required' => 'El motivo es obligatorio',
            'archivo.required' => 'Debe adjuntar un archivo de respaldo',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG',
            'archivo.max' => 'El archivo no debe superar los 5MB',
        ]);

        try {
            $usuario = auth()->user();

            // Guardar archivo
            $archivoPath = null;
            if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
                $nombreArchivo = time() . '_' . $usuario->id . '_' . $archivo->getClientOriginalName();
                $archivoPath = $archivo->storeAs('justificaciones', $nombreArchivo, 'public');
            }

            // Crear justificación
            $justificacion = Justificacion::create([
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'motivo' => $request->motivo,
                'descripcion' => $request->descripcion,
                'archivo' => $archivoPath,
                'estado' => 'Pendiente',
                'id_usuario' => $usuario->id,
            ]);

            Bitacora::registrar(
                'Creación de justificación',
                true,
                'Docente creó solicitud de justificación ID: ' . $justificacion->id . 
                ' para el periodo: ' . $request->fecha_inicio . ' - ' . $request->fecha_fin,
                $usuario->id
            );

            return redirect()->route('justificaciones.mis-justificaciones')
                ->with('success', '¡Justificación enviada correctamente! Está pendiente de aprobación.');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al crear justificación',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al guardar la justificación: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * CU16: Ver detalle de justificación
     */
    public function show($id)
    {
        $justificacion = Justificacion::with(['usuario', 'aprobadoPor'])->findOrFail($id);

        // Verificar permisos
        $usuario = auth()->user();
        $esAdmin = $usuario->hasAnyRole(['Administrador', 'Autoridad', 'Coordinador']);
        $esPropietario = $justificacion->id_usuario === $usuario->id;

        if (!$esAdmin && !$esPropietario) {
            abort(403, 'No tiene permiso para ver esta justificación.');
        }

        Bitacora::registrar(
            'Consulta de detalle de justificación',
            true,
            'Usuario consultó justificación ID: ' . $id,
            auth()->id()
        );

        return view('justificaciones.show', compact('justificacion'));
    }

    /**
     * CU16: Aprobar justificación (Admin, Autoridad, Coordinador)
     */
    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $justificacion = Justificacion::findOrFail($id);

            if (!$justificacion->estaPendiente()) {
                return back()->withErrors(['error' => 'Esta justificación ya fue procesada.']);
            }

            $justificacion->update([
                'estado' => 'Aprobada',
                'observaciones' => $request->observaciones,
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => Carbon::now(),
            ]);

            Bitacora::registrar(
                'Aprobación de justificación',
                true,
                'Usuario aprobó justificación ID: ' . $id . ' del docente: ' . $justificacion->usuario->nombre,
                auth()->id()
            );

            return back()->with('success', '¡Justificación aprobada exitosamente!');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al aprobar justificación',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al aprobar la justificación.']);
        }
    }

    /**
     * CU16: Rechazar justificación (Admin, Autoridad, Coordinador)
     */
    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'required|string|max:500',
        ], [
            'observaciones.required' => 'Debe especificar el motivo del rechazo',
        ]);

        try {
            $justificacion = Justificacion::findOrFail($id);

            if (!$justificacion->estaPendiente()) {
                return back()->withErrors(['error' => 'Esta justificación ya fue procesada.']);
            }

            $justificacion->update([
                'estado' => 'Rechazada',
                'observaciones' => $request->observaciones,
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => Carbon::now(),
            ]);

            Bitacora::registrar(
                'Rechazo de justificación',
                true,
                'Usuario rechazó justificación ID: ' . $id . ' del docente: ' . $justificacion->usuario->nombre,
                auth()->id()
            );

            return back()->with('success', 'Justificación rechazada.');
        } catch (\Exception $e) {
            Bitacora::registrar(
                'Error al rechazar justificación',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al rechazar la justificación.']);
        }
    }

    /**
     * CU16: Descargar archivo adjunto
     */
    public function descargarArchivo($id)
    {
        $justificacion = Justificacion::findOrFail($id);

        // Verificar permisos
        $usuario = auth()->user();
        $esAdmin = $usuario->hasAnyRole(['Administrador', 'Autoridad', 'Coordinador']);
        $esPropietario = $justificacion->id_usuario === $usuario->id;

        if (!$esAdmin && !$esPropietario) {
            abort(403, 'No tiene permiso para descargar este archivo.');
        }

        if (!$justificacion->archivo || !Storage::disk('public')->exists($justificacion->archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        Bitacora::registrar(
            'Descarga de archivo de justificación',
            true,
            'Usuario descargó archivo de justificación ID: ' . $id,
            auth()->id()
        );

        return Storage::disk('public')->download($justificacion->archivo);
    }
}

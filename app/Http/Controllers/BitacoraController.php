<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    /**
     * Mostrar listado de bitácora (solo Administrador)
     */
    public function index(Request $request)
    {
        $query = Bitacora::with('usuario')->orderBy('fecha', 'desc');

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('usuario')) {
            $query->where('id_usuario', $request->usuario);
        }

        if ($request->filled('accion')) {
            $query->where('accion', 'ILIKE', '%' . $request->accion . '%');
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado === 'exitoso');
        }

        $registros = $query->paginate(50);
        
        // Para el select de usuarios
        $usuarios = \App\Models\Usuario::orderBy('nombre')->get();

        return view('bitacora.index', compact('registros', 'usuarios'));
    }

    /**
     * Obtener registros nuevos para actualización en tiempo real
     * Retorna JSON con los últimos registros
     */
    public function obtenerNuevos(Request $request)
    {
        $ultimoId = $request->get('ultimo_id', 0);
        
        $nuevosRegistros = Bitacora::with('usuario')
            ->where('id', '>', $ultimoId)
            ->orderBy('fecha', 'desc')
            ->limit(10)
            ->get()
            ->map(function($registro) {
                return [
                    'id' => $registro->id,
                    'fecha' => $registro->fecha->format('d/m/Y H:i:s'),
                    'usuario' => $registro->usuario ? $registro->usuario->nombre : 'Sistema',
                    'ip' => $registro->ip,
                    'accion' => $registro->accion,
                    'estado' => $registro->estado,
                    'detalle' => $registro->detalle,
                    'badge_class' => $registro->estado ? 'success' : 'danger',
                    'estado_texto' => $registro->estado ? 'Exitoso' : 'Fallido',
                ];
            });

        return response()->json([
            'registros' => $nuevosRegistros,
            'ultimo_id' => $nuevosRegistros->isNotEmpty() ? $nuevosRegistros->first()['id'] : $ultimoId,
        ]);
    }

    /**
     * Limpiar bitácora antigua (opcional)
     */
    public function limpiar(Request $request)
    {
        $request->validate([
            'dias' => 'required|integer|min:1|max:365',
        ]);

        $fecha = now()->subDays($request->dias);
        $eliminados = Bitacora::where('fecha', '<', $fecha)->delete();

        Bitacora::create([
            'fecha' => now(),
            'ip' => request()->ip(),
            'accion' => 'Limpieza de bitácora',
            'estado' => true,
            'detalle' => "Se eliminaron {$eliminados} registros anteriores a {$fecha->format('d/m/Y')}",
            'id_usuario' => auth()->id(),
        ]);

        return back()->with('success', "Se eliminaron {$eliminados} registros de la bitácora.");
    }

    /**
     * Exportar bitácora a CSV
     */
    public function exportar(Request $request)
    {
        $query = Bitacora::with('usuario')->orderBy('fecha', 'desc');

        // Aplicar mismos filtros que en index
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('usuario')) {
            $query->where('id_usuario', $request->usuario);
        }

        if ($request->filled('accion')) {
            $query->where('accion', 'ILIKE', '%' . $request->accion . '%');
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado === 'exitoso');
        }

        $registros = $query->get();

        $filename = 'bitacora_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($registros) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, ['Fecha', 'Hora', 'Usuario', 'IP', 'Acción', 'Estado', 'Detalle']);

            // Datos
            foreach ($registros as $registro) {
                fputcsv($file, [
                    $registro->fecha->format('d/m/Y'),
                    $registro->fecha->format('H:i:s'),
                    $registro->usuario ? $registro->usuario->nombre : 'Sistema',
                    $registro->ip,
                    $registro->accion,
                    $registro->estado ? 'Exitoso' : 'Fallido',
                    $registro->detalle,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Semestre;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class CargaMasivaGrupoController extends Controller
{
    /**
     * CU20C: Mostrar formulario de carga masiva de grupos
     */
    public function index()
    {
        $periodos = Semestre::orderBy('gestion', 'desc')
            ->orderBy('periodo', 'desc')
            ->get();
        
        return view('carga-masiva.grupos', compact('periodos'));
    }

    /**
     * CU20C: Procesar archivo de carga masiva de grupos
     */
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv)',
            'archivo.mimetypes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv)',
            'archivo.max' => 'El archivo no debe superar 10MB',
        ]);

        try {
            $archivo = $request->file('archivo');
            $extension = $archivo->getClientOriginalExtension();
            
            // Guardar el archivo temporalmente en storage/app/temp
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $tempFileName = 'import_grupos_' . time() . '_' . uniqid() . '.' . $extension;
            $tempFilePath = $tempPath . '/' . $tempFileName;
            
            // Mover el archivo subido al directorio temporal
            move_uploaded_file($archivo->getPathname(), $tempFilePath);
            
            // Leer el archivo usando FastExcel desde el storage local
            $registros = (new FastExcel)->import($tempFilePath);
            
            $resultados = [
                'exitosos' => 0,
                'fallidos' => 0,
                'errores' => [],
            ];

            DB::beginTransaction();

            foreach ($registros as $index => $fila) {
                $numeroFila = $index + 2; // +2 porque empieza en 1 y hay encabezado
                
                try {
                    // Validar datos requeridos
                    $validacion = $this->validarFila($fila, $numeroFila);
                    if (!$validacion['valido']) {
                        $resultados['fallidos']++;
                        $resultados['errores'][] = "Fila {$numeroFila}: " . $validacion['error'];
                        continue;
                    }

                    // Preparar datos del grupo
                    $datosGrupo = $this->prepararDatosGrupo($fila);
                    
                    // Buscar el período académico
                    $periodo = Semestre::where('gestion', $datosGrupo['periodo_gestion'])
                        ->where('periodo', $datosGrupo['periodo_numero'])
                        ->first();

                    if (!$periodo) {
                        $resultados['fallidos']++;
                        $resultados['errores'][] = "Fila {$numeroFila}: No existe el período {$datosGrupo['periodo_gestion']}/{$datosGrupo['periodo_numero']}";
                        continue;
                    }

                    // Verificar si el grupo ya existe (por sigla en el mismo período)
                    $grupoExistente = Grupo::where('sigla', $datosGrupo['sigla'])
                        ->where('id_periodo', $periodo->id)
                        ->first();

                    if ($grupoExistente) {
                        $resultados['fallidos']++;
                        $resultados['errores'][] = "Fila {$numeroFila}: Grupo {$datosGrupo['sigla']} ya existe en el período {$datosGrupo['periodo_gestion']}/{$datosGrupo['periodo_numero']}";
                        continue;
                    }

                    // Crear grupo
                    Grupo::create([
                        'sigla' => $datosGrupo['sigla'],
                        'id_periodo' => $periodo->id,
                    ]);

                    $resultados['exitosos']++;

                } catch (\Exception $e) {
                    $resultados['fallidos']++;
                    $resultados['errores'][] = "Fila {$numeroFila}: Error al procesar - " . $e->getMessage();
                }
            }

            DB::commit();

            // Registrar en bitácora
            Bitacora::registrar(
                'Carga masiva de grupos',
                true,
                "Se procesaron {$resultados['exitosos']} grupos exitosamente, {$resultados['fallidos']} fallidos",
                auth()->id()
            );

            // Preparar mensaje de respuesta
            $mensaje = "Carga completada: {$resultados['exitosos']} grupos creados exitosamente";
            if ($resultados['fallidos'] > 0) {
                $mensaje .= ", {$resultados['fallidos']} registros fallidos";
            }

            // Limpiar archivo temporal
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            return redirect()->route('carga-masiva.grupos')
                ->with('success', $mensaje)
                ->with('resultados', $resultados);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar archivo temporal en caso de error
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            
            Bitacora::registrar(
                'Error en carga masiva de grupos',
                false,
                'Error: ' . $e->getMessage(),
                auth()->id()
            );

            return back()->withErrors(['error' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar datos de una fila
     */
    private function validarFila($fila, $numeroFila)
    {
        // Normalizar claves
        $fila = array_change_key_case($fila, CASE_LOWER);
        
        // Campos requeridos
        $camposRequeridos = ['sigla', 'periodo_gestion', 'periodo_numero'];
        
        foreach ($camposRequeridos as $campo) {
            if (empty($fila[$campo])) {
                return [
                    'valido' => false,
                    'error' => "El campo '{$campo}' es obligatorio"
                ];
            }
        }

        // Validar formato de sigla
        if (strlen($fila['sigla']) > 3) {
            return [
                'valido' => false,
                'error' => "La sigla no puede exceder 3 caracteres"
            ];
        }

        // Validar período_gestion (debe ser un año válido)
        if (!is_numeric($fila['periodo_gestion']) || $fila['periodo_gestion'] < 2000 || $fila['periodo_gestion'] > 2100) {
            return [
                'valido' => false,
                'error' => "El periodo_gestion debe ser un año válido entre 2000 y 2100"
            ];
        }

        // Validar periodo_numero (debe ser 1 o 2)
        if (!in_array($fila['periodo_numero'], [1, 2, '1', '2'])) {
            return [
                'valido' => false,
                'error' => "El periodo_numero debe ser 1 o 2"
            ];
        }

        return ['valido' => true];
    }

    /**
     * Preparar datos del grupo para crear
     */
    private function prepararDatosGrupo($fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);

        return [
            'sigla' => strtoupper(trim($fila['sigla'])),
            'periodo_gestion' => (int) $fila['periodo_gestion'],
            'periodo_numero' => (int) $fila['periodo_numero'],
        ];
    }

    /**
     * Descargar plantilla de ejemplo
     */
    public function descargarPlantilla()
    {
        // Obtener el período activo como referencia
        $periodoActivo = Semestre::where('activo', true)->first();
        
        $gestion = $periodoActivo ? $periodoActivo->gestion : date('Y');
        $periodo = $periodoActivo ? $periodoActivo->periodo : 2;
        
        // Crear datos de ejemplo
        $datos = [
            ['sigla' => 'F1', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'SZ', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'CI', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'I2', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'SF', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'SG', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'SI', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
            ['sigla' => 'SP', 'periodo_gestion' => $gestion, 'periodo_numero' => $periodo],
        ];

        Bitacora::registrar(
            'Descarga de plantilla de grupos',
            true,
            'Usuario descargó plantilla de carga masiva de grupos',
            auth()->id()
        );

        try {
            // Crear directorio temporal si no existe
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Generar archivo temporal
            $tempFileName = 'plantilla_grupos_' . time() . '.xlsx';
            $tempFilePath = $tempPath . '/' . $tempFileName;
            
            // Exportar a archivo temporal
            (new FastExcel(collect($datos)))->export($tempFilePath);
            
            // Descargar y eliminar archivo temporal
            return response()->download($tempFilePath, 'plantilla_carga_grupos.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar la plantilla: ' . $e->getMessage()]);
        }
    }
}

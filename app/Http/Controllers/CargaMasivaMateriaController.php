<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Carrera;
use App\Models\Semestre;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class CargaMasivaMateriaController extends Controller
{
    /**
     * CU20A: Mostrar formulario de carga masiva de materias
     */
    public function index()
    {
        $carreras = Carrera::orderBy('nombre')->get();
        return view('carga-masiva.materias', compact('carreras'));
    }

    /**
     * CU20A: Procesar archivo de carga masiva de materias
     */
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv)',
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
            
            $tempFileName = 'import_materias_' . time() . '_' . uniqid() . '.' . $extension;
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

            // Obtener período activo
            $periodoActivo = Semestre::where('activo', true)->first();

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

                    // Preparar datos de la materia
                    $datosMateria = $this->prepararDatosMateria($fila);
                    
                    // Verificar si la materia ya existe
                    $materiaExistente = Materia::find($datosMateria['sigla']);

                    if ($materiaExistente) {
                        $resultados['fallidos']++;
                        $resultados['errores'][] = "Fila {$numeroFila}: Materia con sigla {$datosMateria['sigla']} ya existe";
                        continue;
                    }

                    // Crear materia
                    $materia = Materia::create($datosMateria);

                    // Asignar al período activo si corresponde
                    if ($periodoActivo && $this->debeAsignarPeriodoActivo($fila)) {
                        $materia->periodos()->attach($periodoActivo->id, [
                            'activa' => true,
                            'created_at' => now(),
                        ]);
                    }

                    // Asignar carreras
                    $carrerasAsignadas = $this->asignarCarreras($materia, $fila);

                    $resultados['exitosos']++;

                } catch (\Exception $e) {
                    $resultados['fallidos']++;
                    $resultados['errores'][] = "Fila {$numeroFila}: Error al procesar - " . $e->getMessage();
                }
            }

            DB::commit();

            // Registrar en bitácora
            Bitacora::registrar(
                'Carga masiva de materias',
                true,
                "Se procesaron {$resultados['exitosos']} materias exitosamente, {$resultados['fallidos']} fallidas",
                auth()->id()
            );

            // Preparar mensaje de respuesta
            $mensaje = "Carga completada: {$resultados['exitosos']} materias creadas exitosamente";
            if ($resultados['fallidos'] > 0) {
                $mensaje .= ", {$resultados['fallidos']} registros fallidos";
            }

            // Limpiar archivo temporal
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            return redirect()->route('carga-masiva.materias')
                ->with('success', $mensaje)
                ->with('resultados', $resultados);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar archivo temporal en caso de error
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            
            Bitacora::registrar(
                'Error en carga masiva de materias',
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
        $camposRequeridos = ['sigla', 'nombre', 'nivel'];
        
        foreach ($camposRequeridos as $campo) {
            if (empty($fila[$campo])) {
                return [
                    'valido' => false,
                    'error' => "El campo '{$campo}' es obligatorio"
                ];
            }
        }

        // Validar formato de sigla
        if (strlen($fila['sigla']) > 6) {
            return [
                'valido' => false,
                'error' => "La sigla no puede exceder 6 caracteres"
            ];
        }

        // Validar formato de nombre
        if (strlen($fila['nombre']) > 50) {
            return [
                'valido' => false,
                'error' => "El nombre no puede exceder 50 caracteres"
            ];
        }

        // Validar nivel
        if (!is_numeric($fila['nivel']) || $fila['nivel'] <= 0 || $fila['nivel'] > 10) {
            return [
                'valido' => false,
                'error' => "El nivel debe ser un número entre 1 y 10"
            ];
        }

        return ['valido' => true];
    }

    /**
     * Preparar datos de la materia para crear
     */
    private function prepararDatosMateria($fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);

        return [
            'sigla' => strtoupper(trim($fila['sigla'])),
            'nombre' => ucwords(strtolower(trim($fila['nombre']))),
            'nivel' => (int) $fila['nivel'],
        ];
    }

    /**
     * Verificar si debe asignarse al período activo
     */
    private function debeAsignarPeriodoActivo($fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);
        
        if (isset($fila['asignar_periodo_activo']) && !empty($fila['asignar_periodo_activo'])) {
            $valor = strtoupper(trim($fila['asignar_periodo_activo']));
            return in_array($valor, ['X', '1', 'SI', 'TRUE', 'YES', 'S']);
        }
        
        return false;
    }

    /**
     * Asignar carreras a la materia según las columnas del archivo
     */
    private function asignarCarreras($materia, $fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);
        $carrerasAsignadas = [];

        // Las columnas fijas que NO son códigos de carrera
        $columnasExcluidas = ['sigla', 'nombre', 'nivel', 'asignar_periodo_activo'];

        // Recorrer todas las columnas del archivo
        foreach ($fila as $columna => $valor) {
            // Si no es una columna fija, asumimos que es un código de carrera
            if (!in_array($columna, $columnasExcluidas) && !empty($valor)) {
                $valorNormalizado = strtoupper(trim($valor));
                
                // Si tiene marcador (X, 1, SI, etc.), asignar carrera
                if (in_array($valorNormalizado, ['X', '1', 'SI', 'TRUE', 'YES', 'S'])) {
                    // Buscar la carrera por código (la columna es el código)
                    $codigoCarrera = strtoupper(trim($columna));
                    $carrera = Carrera::find($codigoCarrera);
                    
                    if ($carrera) {
                        // Asignar materia a carrera
                        $materia->carreras()->attach($carrera->cod);
                        $carrerasAsignadas[] = $carrera->nombre;
                    }
                }
            }
        }

        return $carrerasAsignadas;
    }

    /**
     * Descargar plantilla de ejemplo
     */
    public function descargarPlantilla()
    {
        // Obtener todas las carreras para crear las columnas dinámicamente
        $carreras = Carrera::orderBy('cod')->get();
        
        // Crear datos de ejemplo
        $datos = [
            $this->crearFilaEjemplo('INF220', 'Base de Datos I', 2, 'X', $carreras, ['187-3', '187-4', '187-5', '320-0']),
            $this->crearFilaEjemplo('INF320', 'Base de Datos II', 3, 'X', $carreras, ['187-3', '187-4', '187-5', '320-0']),
            $this->crearFilaEjemplo('MAT101', 'Cálculo I', 1, 'X', $carreras, ['187-3', '187-5', '320-0']),
            $this->crearFilaEjemplo('MAT102', 'Cálculo II', 2, 'X', $carreras, ['187-3', '187-4', '187-5', '320-0']),
            $this->crearFilaEjemplo('FIS110', 'Física General', 1, '', $carreras, ['187-3', '187-4', '187-5']),
            $this->crearFilaEjemplo('PRG110', 'Programación I', 1, 'X', $carreras, ['187-3', '187-4', '187-5', '320-0']),
            $this->crearFilaEjemplo('PRG210', 'Programación II', 2, 'X', $carreras, ['187-3', '187-5', '320-0']),
        ];

        Bitacora::registrar(
            'Descarga de plantilla de materias',
            true,
            'Usuario descargó plantilla de carga masiva de materias',
            auth()->id()
        );

        try {
            // Crear directorio temporal si no existe
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Generar archivo temporal
            $tempFileName = 'plantilla_materias_' . time() . '.xlsx';
            $tempFilePath = $tempPath . '/' . $tempFileName;
            
            // Exportar a archivo temporal
            (new FastExcel(collect($datos)))->export($tempFilePath);
            
            // Descargar y eliminar archivo temporal
            return response()->download($tempFilePath, 'plantilla_carga_materias.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar la plantilla: ' . $e->getMessage()]);
        }
    }

    /**
     * Crear una fila de ejemplo para la plantilla
     */
    private function crearFilaEjemplo($sigla, $nombre, $nivel, $asignarPeriodo, $carreras, $carrerasAsignadas)
    {
        $fila = [
            'sigla' => $sigla,
            'nombre' => $nombre,
            'nivel' => $nivel,
            'asignar_periodo_activo' => $asignarPeriodo,
        ];

        // Agregar columnas de carreras
        foreach ($carreras as $carrera) {
            $fila[$carrera->cod] = in_array($carrera->cod, $carrerasAsignadas) ? '1' : '';
        }

        return $fila;
    }
}

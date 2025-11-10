<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class CargaMasivaController extends Controller
{
    /**
     * CU20: Mostrar formulario de carga masiva
     */
    public function index()
    {
        return view('carga-masiva.index');
    }

    /**
     * CU20: Procesar archivo de carga masiva
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
            
            $tempFileName = 'import_' . time() . '_' . uniqid() . '.' . $extension;
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

                    // Preparar datos del usuario
                    $datosUsuario = $this->prepararDatosUsuario($fila);
                    
                    // Verificar si el usuario ya existe (por CI, código o correo)
                    $usuarioExistente = Usuario::where('ci', $datosUsuario['ci'])
                        ->orWhere('codigo', $datosUsuario['codigo'])
                        ->orWhere('correo', $datosUsuario['correo'])
                        ->first();

                    if ($usuarioExistente) {
                        $resultados['fallidos']++;
                        $resultados['errores'][] = "Fila {$numeroFila}: Usuario con CI {$datosUsuario['ci']}, código {$datosUsuario['codigo']} o correo {$datosUsuario['correo']} ya existe";
                        continue;
                    }

                    // Crear usuario
                    $usuario = Usuario::create($datosUsuario);

                    // Asignar roles
                    $rolesAsignados = $this->asignarRoles($usuario, $fila);
                    
                    if (empty($rolesAsignados)) {
                        $resultados['fallidos']++;
                        $resultados['errores'][] = "Fila {$numeroFila}: No se especificó ningún rol válido";
                        $usuario->delete();
                        continue;
                    }

                    $resultados['exitosos']++;

                } catch (\Exception $e) {
                    $resultados['fallidos']++;
                    $resultados['errores'][] = "Fila {$numeroFila}: Error al procesar - " . $e->getMessage();
                }
            }

            DB::commit();

            // Registrar en bitácora
            Bitacora::registrar(
                'Carga masiva de usuarios',
                true,
                "Se procesaron {$resultados['exitosos']} usuarios exitosamente, {$resultados['fallidos']} fallidos",
                auth()->id()
            );

            // Preparar mensaje de respuesta
            $mensaje = "Carga completada: {$resultados['exitosos']} usuarios creados exitosamente";
            if ($resultados['fallidos'] > 0) {
                $mensaje .= ", {$resultados['fallidos']} registros fallidos";
            }

            // Limpiar archivo temporal
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            return redirect()->route('carga-masiva.index')
                ->with('success', $mensaje)
                ->with('resultados', $resultados);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar archivo temporal en caso de error
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            
            Bitacora::registrar(
                'Error en carga masiva',
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
        // Normalizar claves (pueden venir con mayúsculas, minúsculas, espacios)
        $fila = array_change_key_case($fila, CASE_LOWER);
        
        // Campos requeridos
        $camposRequeridos = ['nombre', 'ci', 'correo', 'telefono'];
        
        foreach ($camposRequeridos as $campo) {
            if (empty($fila[$campo])) {
                return [
                    'valido' => false,
                    'error' => "El campo '{$campo}' es obligatorio"
                ];
            }
        }

        // Validar formato de CI (debe ser positivo y razonable)
        if (!is_numeric($fila['ci']) || $fila['ci'] <= 0 || $fila['ci'] > 99999999) {
            return [
                'valido' => false,
                'error' => "El CI debe ser un número válido entre 1 y 99999999"
            ];
        }

        // Validar formato de teléfono
        if (!is_numeric($fila['telefono']) || strlen($fila['telefono']) < 8) {
            return [
                'valido' => false,
                'error' => "El teléfono debe ser un número válido de al menos 8 dígitos"
            ];
        }

        // Validar formato de correo
        if (!filter_var($fila['correo'], FILTER_VALIDATE_EMAIL)) {
            return [
                'valido' => false,
                'error' => "El correo electrónico no tiene un formato válido"
            ];
        }

        // Validar longitud de campos
        if (strlen($fila['nombre']) > 40) {
            return [
                'valido' => false,
                'error' => "El nombre no puede exceder 40 caracteres"
            ];
        }

        if (strlen($fila['correo']) > 40) {
            return [
                'valido' => false,
                'error' => "El correo no puede exceder 40 caracteres"
            ];
        }

        return ['valido' => true];
    }

    /**
     * Preparar datos del usuario para crear
     */
    private function prepararDatosUsuario($fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);
        
        // Generar código único (puede ser el CI o un número aleatorio)
        $codigo = isset($fila['codigo']) && !empty($fila['codigo']) 
            ? $fila['codigo'] 
            : $fila['ci'];

        // Contraseña: usar la proporcionada o el CI por defecto
        $password = isset($fila['password']) && !empty($fila['password'])
            ? $fila['password']
            : $fila['ci'];

        return [
            'ci' => (int) $fila['ci'],
            'nombre' => strtoupper(trim($fila['nombre'])),
            'correo' => strtolower(trim($fila['correo'])),
            'telefono' => (int) $fila['telefono'],
            'codigo' => (int) $codigo,
            'password' => Hash::make($password),
        ];
    }

    /**
     * Asignar roles al usuario según las columnas del archivo
     */
    private function asignarRoles($usuario, $fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);
        $rolesAsignados = [];

        // Mapeo de columnas a roles
        $mapaRoles = [
            'docente' => 'Docente',
            'coordinador' => 'Coordinador',
            'autoridad' => 'Autoridad',
            'administrador' => 'Administrador',
        ];

        foreach ($mapaRoles as $columna => $nombreRol) {
            // Verificar si la columna existe y tiene algún valor (X, 1, true, etc.)
            if (isset($fila[$columna]) && !empty($fila[$columna])) {
                $valor = strtoupper(trim($fila[$columna]));
                
                // Considerar como "marcado" si tiene X, 1, SI, TRUE, etc.
                if (in_array($valor, ['X', '1', 'SI', 'TRUE', 'YES', 'S'])) {
                    $rol = Rol::where('descripcion', $nombreRol)->first();
                    
                    if ($rol) {
                        // Asignar rol al usuario
                        $usuario->roles()->attach($rol->id, [
                            'detalle' => "Asignado mediante carga masiva"
                        ]);
                        $rolesAsignados[] = $nombreRol;
                    }
                }
            }
        }

        return $rolesAsignados;
    }

    /**
     * Descargar plantilla de ejemplo
     */
    public function descargarPlantilla()
    {
        $datos = [
            [
                'nombre' => 'Juan Pérez García',
                'ci' => '12345678',
                'correo' => 'juan.perez@universidad.edu',
                'telefono' => '70123456',
                'coordinador' => '',
                'autoridad' => 'X',
                'docente' => '',
                'administrador' => '',
            ],
            [
                'nombre' => 'María López',
                'ci' => '87654321',
                'correo' => 'maria.lopez@universidad.edu',
                'telefono' => '71234567',
                'coordinador' => 'X',
                'autoridad' => 'X',
                'docente' => '',
                'administrador' => '',
            ],
            [
                'nombre' => 'Carlos Gómez',
                'ci' => '11223344',
                'correo' => 'carlos.gomez@universidad.edu',
                'telefono' => '72345678',
                'coordinador' => '',
                'autoridad' => 'X',
                'docente' => '',
                'administrador' => '',
            ],
        ];

        Bitacora::registrar(
            'Descarga de plantilla',
            true,
            'Usuario descargó plantilla de carga masiva',
            auth()->id()
        );

        try {
            // Crear directorio temporal si no existe
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Generar archivo temporal
            $tempFileName = 'plantilla_' . time() . '.xlsx';
            $tempFilePath = $tempPath . '/' . $tempFileName;
            
            // Exportar a archivo temporal
            (new FastExcel(collect($datos)))->export($tempFilePath);
            
            // Descargar y eliminar archivo temporal
            return response()->download($tempFilePath, 'plantilla_carga_usuarios.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar la plantilla: ' . $e->getMessage()]);
        }
    }
}

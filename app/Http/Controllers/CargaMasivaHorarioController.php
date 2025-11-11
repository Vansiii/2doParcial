<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Usuario;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\Modulo;
use App\Models\Dia;
use App\Models\GrupoMateria;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CargaMasivaHorarioController extends Controller
{
    /**
     * Mostrar formulario de carga masiva de horarios
     */
    public function index()
    {
        $grupos = Grupo::with('periodo')->orderBy('sigla')->get();
        
        return view('carga-masiva.horarios', compact('grupos'));
    }

    /**
     * Procesar archivo de carga masiva de horarios
     */
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240',
        ]);

        try {
            $archivo = $request->file('archivo');
            $extension = $archivo->getClientOriginalExtension();
            
            // Crear directorio temporal si no existe
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $tempFileName = 'import_horarios_' . time() . '_' . uniqid() . '.' . $extension;
            $fullPath = $tempPath . '/' . $tempFileName;
            
            // Mover el archivo subido al directorio temporal
            move_uploaded_file($archivo->getPathname(), $fullPath);

            // Leer el archivo
            $registros = (new FastExcel)->import($fullPath);
            
            $errores = [];
            $advertencias = [];
            $procesados = 0;
            $horariosCreados = 0;
            $asignacionesCreadas = 0;

            DB::beginTransaction();

            foreach ($registros as $index => $fila) {
                $numeroFila = $index + 2; // +2 porque Excel empieza en 1 y tiene encabezado
                
                // Validar fila
                $validacion = $this->validarFila($fila, $numeroFila);
                if (!$validacion['valido']) {
                    $errores = array_merge($errores, $validacion['errores']);
                    continue;
                }

                // Preparar datos
                $datosHorario = $this->prepararDatosHorario($fila, $numeroFila);
                if (isset($datosHorario['errores'])) {
                    $errores = array_merge($errores, $datosHorario['errores']);
                    continue;
                }

                // Verificar o crear grupo_materia
                $grupoMateria = GrupoMateria::where('id_grupo', $datosHorario['grupo']->id)
                    ->where('sigla_materia', $datosHorario['materia']->sigla)
                    ->where('id_docente', $datosHorario['docente']->id)
                    ->first();

                if (!$grupoMateria) {
                    // Crear la asignación automáticamente
                    $grupoMateria = GrupoMateria::create([
                        'id_grupo' => $datosHorario['grupo']->id,
                        'sigla_materia' => $datosHorario['materia']->sigla,
                        'id_docente' => $datosHorario['docente']->id,
                    ]);
                    
                    $asignacionesCreadas++;
                    $advertencias[] = "Fila {$numeroFila}: Se creó automáticamente la asignación {$datosHorario['grupo']->sigla}-{$datosHorario['materia']->sigla}-Docente({$datosHorario['docente']->codigo}).";
                }

                // Crear horarios para cada día especificado
                for ($diaNum = 1; $diaNum <= 4; $diaNum++) {
                    $diaKey = "dia{$diaNum}";
                    
                    if (!empty($datosHorario[$diaKey])) {
                        // Crear horario
                        $horario = Horario::create([
                            'horaini' => $datosHorario["hora_inicio{$diaNum}"],
                            'horafin' => $datosHorario["hora_fin{$diaNum}"],
                            'tiempoh' => $datosHorario["tiempoh{$diaNum}"],
                            'nroaula' => $datosHorario["aula{$diaNum}"]->nroaula,
                            'id_grupo' => $datosHorario['grupo']->id,
                        ]);

                        // Relacionar con día
                        $horario->dias()->attach($datosHorario[$diaKey]->id);

                        // Relacionar con materia
                        $horario->materias()->attach($datosHorario['materia']->sigla);

                        $horariosCreados++;
                    }
                }

                $procesados++;
            }

            // Limpiar archivo temporal
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if (count($errores) > 0) {
                DB::rollBack();
                return back()->with('error', 'Se encontraron errores en el archivo:')
                    ->with('errores', $errores)
                    ->with('advertencias', $advertencias);
            }

            DB::commit();

            // Registrar en bitácora
            Bitacora::create([
                'fecha' => now(),
                'ip' => request()->ip(),
                'accion' => 'Carga masiva de horarios',
                'estado' => true,
                'detalle' => "Procesados: {$procesados} registros, Asignaciones creadas: {$asignacionesCreadas}, Horarios creados: {$horariosCreados}",
                'id_usuario' => auth()->id(),
            ]);

            $mensaje = "✅ Carga masiva completada exitosamente. ";
            $mensaje .= "Procesados: {$procesados} registros. ";
            if ($asignacionesCreadas > 0) {
                $mensaje .= "Asignaciones Grupo-Materia-Docente creadas: {$asignacionesCreadas}. ";
            }
            $mensaje .= "Horarios creados: {$horariosCreados}.";

            if (count($advertencias) > 0) {
                return back()->with('success', $mensaje)
                    ->with('advertencias', $advertencias);
            }

            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            Bitacora::create([
                'fecha' => now(),
                'ip' => request()->ip(),
                'accion' => 'Error en carga masiva de horarios',
                'estado' => false,
                'detalle' => $e->getMessage(),
                'id_usuario' => auth()->id(),
            ]);

            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Validar una fila del archivo
     */
    private function validarFila($fila, $numeroFila)
    {
        $errores = [];

        // Campos obligatorios
        if (empty($fila['sigla_grupo'])) {
            $errores[] = "Fila {$numeroFila}: El campo 'sigla_grupo' es obligatorio.";
        }
        if (empty($fila['sigla_materia'])) {
            $errores[] = "Fila {$numeroFila}: El campo 'sigla_materia' es obligatorio.";
        }
        if (empty($fila['cod_docente'])) {
            $errores[] = "Fila {$numeroFila}: El campo 'cod_docente' es obligatorio.";
        }

        // Validar que al menos un día tenga horario
        $tieneDia = false;
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($fila["dia{$i}"])) {
                $tieneDia = true;
                
                // Si tiene día, debe tener horario y local
                if (empty($fila["horario{$i}"])) {
                    $errores[] = "Fila {$numeroFila}: Si especifica 'dia{$i}', debe especificar 'horario{$i}'.";
                }
                if (empty($fila["local{$i}"])) {
                    $errores[] = "Fila {$numeroFila}: Si especifica 'dia{$i}', debe especificar 'local{$i}'.";
                }

                // Validar formato de horario (HH:MM-HH:MM)
                if (!empty($fila["horario{$i}"])) {
                    if (!preg_match('/^\d{1,2}:\d{2}-\d{1,2}:\d{2}$/', $fila["horario{$i}"])) {
                        $errores[] = "Fila {$numeroFila}: El formato de 'horario{$i}' debe ser HH:MM-HH:MM (ejemplo: 7:00-8:30).";
                    }
                }

                // Validar formato de local (NNN-NN)
                if (!empty($fila["local{$i}"])) {
                    if (!preg_match('/^\d{3}-\d{1,2}$/', $fila["local{$i}"])) {
                        $errores[] = "Fila {$numeroFila}: El formato de 'local{$i}' debe ser CódigoMódulo-NroAula (ejemplo: 236-11).";
                    }
                }
            }
        }

        if (!$tieneDia) {
            $errores[] = "Fila {$numeroFila}: Debe especificar al menos un día con su horario y local.";
        }

        return [
            'valido' => count($errores) === 0,
            'errores' => $errores
        ];
    }

    /**
     * Preparar datos de la fila para inserción
     */
    private function prepararDatosHorario($fila, $numeroFila)
    {
        $errores = [];
        $datos = [];

        // Normalizar y buscar grupo
        $siglaGrupo = strtoupper(trim($fila['sigla_grupo']));
        $grupo = Grupo::where('sigla', $siglaGrupo)->first();
        if (!$grupo) {
            $errores[] = "Fila {$numeroFila}: No existe el grupo con sigla '{$siglaGrupo}'.";
        }
        $datos['grupo'] = $grupo;

        // Normalizar y buscar materia
        $siglaMateria = strtoupper(trim($fila['sigla_materia']));
        $materia = Materia::where('sigla', $siglaMateria)->first();
        if (!$materia) {
            $errores[] = "Fila {$numeroFila}: No existe la materia con sigla '{$siglaMateria}'.";
        }
        $datos['materia'] = $materia;

        // Buscar docente por código
        $codigoDocente = trim($fila['cod_docente']);
        $docente = Usuario::where('codigo', $codigoDocente)
            ->whereHas('roles', function($q) {
                $q->where('descripcion', 'Docente');
            })
            ->first();
        if (!$docente) {
            $errores[] = "Fila {$numeroFila}: No existe el docente con código '{$codigoDocente}'.";
        }
        $datos['docente'] = $docente;

        // Procesar cada día posible (hasta 4)
        for ($diaNum = 1; $diaNum <= 4; $diaNum++) {
            $diaKey = "dia{$diaNum}";
            $horarioKey = "horario{$diaNum}";
            $localKey = "local{$diaNum}";

            if (!empty($fila[$diaKey])) {
                // Buscar día
                $nombreDia = trim($fila[$diaKey]);
                $dia = $this->buscarDia($nombreDia);
                if (!$dia) {
                    $errores[] = "Fila {$numeroFila}: Día '{$nombreDia}' no válido. Use: Lun, Mar, Mie, Jue, Vie, Sab, Dom.";
                } else {
                    $datos[$diaKey] = $dia;
                }

                // Procesar horario
                $horario = trim($fila[$horarioKey]);
                if (preg_match('/^(\d{1,2}):(\d{2})-(\d{1,2}):(\d{2})$/', $horario, $matches)) {
                    $horaIni = str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2] . ':00';
                    $horaFin = str_pad($matches[3], 2, '0', STR_PAD_LEFT) . ':' . $matches[4] . ':00';
                    
                    // Calcular duración en minutos
                    $inicio = Carbon::createFromFormat('H:i:s', $horaIni);
                    $fin = Carbon::createFromFormat('H:i:s', $horaFin);
                    $tiempoh = $inicio->diffInMinutes($fin);

                    $datos["hora_inicio{$diaNum}"] = $horaIni;
                    $datos["hora_fin{$diaNum}"] = $horaFin;
                    $datos["tiempoh{$diaNum}"] = $tiempoh;
                }

                // Procesar local (Módulo-Aula)
                $local = trim($fila[$localKey]);
                if (preg_match('/^(\d{3})-(\d{1,2})$/', $local, $matches)) {
                    $codigoModulo = (int)$matches[1];
                    $nroAula = (int)$matches[2];

                    // Buscar aula
                    $aula = Aula::where('nroaula', $nroAula)
                        ->where('id_modulo', $codigoModulo)
                        ->first();
                    
                    if (!$aula) {
                        // Verificar si existe el módulo
                        $modulo = Modulo::find($codigoModulo);
                        if (!$modulo) {
                            $errores[] = "Fila {$numeroFila}: No existe el módulo con código '{$codigoModulo}'.";
                        } else {
                            $errores[] = "Fila {$numeroFila}: No existe el aula '{$nroAula}' en el módulo '{$codigoModulo}'.";
                        }
                    } else {
                        $datos["aula{$diaNum}"] = $aula;
                    }
                }
            }
        }

        if (count($errores) > 0) {
            return ['errores' => $errores];
        }

        return $datos;
    }

    /**
     * Buscar día por abreviatura
     */
    private function buscarDia($nombre)
    {
        $mapaDias = [
            'LUN' => 1,
            'LUNES' => 1,
            'MAR' => 2,
            'MARTES' => 2,
            'MIE' => 3,
            'MIERCOLES' => 3,
            'MIÉRCOLES' => 3,
            'JUE' => 4,
            'JUEVES' => 4,
            'VIE' => 5,
            'VIERNES' => 5,
            'SAB' => 6,
            'SABADO' => 6,
            'SÁBADO' => 6,
            'DOM' => 7,
            'DOMINGO' => 7,
        ];

        $nombreNormalizado = strtoupper(trim($nombre));
        
        if (isset($mapaDias[$nombreNormalizado])) {
            return Dia::find($mapaDias[$nombreNormalizado]);
        }

        return null;
    }

    /**
     * Descargar plantilla Excel de ejemplo
     */
    public function descargarPlantilla()
    {
        $ejemplos = [
            [
                'sigla_grupo' => 'Z1',
                'sigla_materia' => 'INF220',
                'cod_docente' => '107',
                'dia1' => 'Lun',
                'horario1' => '7:00-8:30',
                'local1' => '236-11',
                'dia2' => 'Mie',
                'horario2' => '7:00-8:30',
                'local2' => '236-11',
                'dia3' => 'Vie',
                'horario3' => '7:00-8:30',
                'local3' => '236-11',
                'dia4' => '',
                'horario4' => '',
                'local4' => '',
            ],
            [
                'sigla_grupo' => 'Z1',
                'sigla_materia' => 'MAT101',
                'cod_docente' => '112',
                'dia1' => 'Lun',
                'horario1' => '8:30-10:00',
                'local1' => '236-12',
                'dia2' => 'Mie',
                'horario2' => '8:30-10:00',
                'local2' => '236-12',
                'dia3' => 'Vie',
                'horario3' => '8:30-10:00',
                'local3' => '236-12',
                'dia4' => '',
                'horario4' => '',
                'local4' => '',
            ],
            [
                'sigla_grupo' => 'Z2',
                'sigla_materia' => 'INF220',
                'cod_docente' => '115',
                'dia1' => 'Mar',
                'horario1' => '9:15-11:30',
                'local1' => '236-13',
                'dia2' => 'Jue',
                'horario2' => '9:15-11:30',
                'local2' => '236-13',
                'dia3' => '',
                'horario3' => '',
                'local3' => '',
                'dia4' => '',
                'horario4' => '',
                'local4' => '',
            ],
        ];

        $filename = 'plantilla_horarios_' . date('Y-m-d') . '.xlsx';
        return (new FastExcel(collect($ejemplos)))->download($filename);
    }
}

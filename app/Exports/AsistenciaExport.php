<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciaExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $asistencias;
    protected $docente;
    protected $grupo;
    protected $fechaInicio;
    protected $fechaFin;
    protected $totalAsistencias;
    protected $presentes;
    protected $ausentes;
    protected $licencias;
    protected $retrasos;
    protected $porcentajeAsistencia;

    public function __construct($asistencias, $docente, $grupo, $fechaInicio, $fechaFin, $totalAsistencias, $presentes, $ausentes, $licencias, $retrasos, $porcentajeAsistencia)
    {
        $this->asistencias = $asistencias;
        $this->docente = $docente;
        $this->grupo = $grupo;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->totalAsistencias = $totalAsistencias;
        $this->presentes = $presentes;
        $this->ausentes = $ausentes;
        $this->licencias = $licencias;
        $this->retrasos = $retrasos;
        $this->porcentajeAsistencia = $porcentajeAsistencia;
    }

    public function collection()
    {
        $data = collect();

        // Encabezado de filtros
        if ($this->docente) {
            $data->push([
                'Docente:',
                $this->docente->nombre,
            ]);
        }

        if ($this->grupo) {
            $data->push([
                'Grupo:',
                $this->grupo->sigla,
            ]);
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $data->push([
                'Período:',
                $this->fechaInicio . ' al ' . $this->fechaFin,
            ]);
        }

        $data->push(['']); // Línea vacía

        // Estadísticas
        $data->push(['ESTADÍSTICAS']);
        $data->push(['Total Asistencias:', $this->totalAsistencias]);
        $data->push(['Presentes:', $this->presentes]);
        $data->push(['Ausentes:', $this->ausentes]);
        $data->push(['Con Licencia:', $this->licencias]);
        $data->push(['Retrasos:', $this->retrasos]);
        $data->push(['% Asistencia:', $this->porcentajeAsistencia . '%']);

        $data->push(['']); // Línea vacía

        // Encabezados de tabla
        $data->push([
            'Fecha',
            'Hora',
            'Docente',
            'Grupo',
            'Materia',
            'Aula',
            'Presente',
            'Retraso',
            'Licencia',
        ]);

        // Datos de asistencias
        foreach ($this->asistencias as $asistencia) {
            $docente = $asistencia->usuario->nombre ?? 'N/A';
            $materia = ($asistencia->horario->grupo && $asistencia->horario->grupo->materias->isNotEmpty()) 
                ? $asistencia->horario->grupo->materias->first()->nombre 
                : 'N/A';
            
            $data->push([
                $asistencia->fecha,
                $asistencia->hora,
                $docente,
                $asistencia->horario->grupo->sigla ?? 'N/A',
                $materia,
                $asistencia->horario->aula->nroaula ?? 'N/A',
                $asistencia->tipo == 'Presente' ? 'Sí' : 'No',
                $asistencia->tipo == 'Retraso' ? 'Sí' : 'No',
                $asistencia->tipo == 'Licencia' ? 'Sí' : 'No',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Reporte de Asistencia',
            'Generado: ' . now()->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }

    public function title(): string
    {
        return 'Asistencia';
    }
}

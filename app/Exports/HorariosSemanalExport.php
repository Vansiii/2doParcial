<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HorariosSemanalExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $horariosPorDia;
    protected $docente;
    protected $grupo;
    protected $dias;

    public function __construct($horariosPorDia, $docente, $grupo, $dias)
    {
        $this->horariosPorDia = $horariosPorDia;
        $this->docente = $docente;
        $this->grupo = $grupo;
        $this->dias = $dias;
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

        $data->push(['']); // Línea vacía

        // Datos de horarios por día
        foreach ($this->horariosPorDia as $nombreDia => $horarios) {
            $data->push(['Día:', $nombreDia]);
            
            foreach ($horarios as $horario) {
                $docente = ($horario->grupo && $horario->grupo->docentes->isNotEmpty()) 
                    ? $horario->grupo->docentes->first()->nombre 
                    : 'N/A';
                
                $materia = ($horario->grupo && $horario->grupo->materias->isNotEmpty()) 
                    ? $horario->grupo->materias->first()->nombre 
                    : 'N/A';
                
                $data->push([
                    $horario->horaini . ' - ' . $horario->horafin,
                    $docente,
                    $horario->grupo->sigla ?? 'N/A',
                    $materia,
                    $horario->aula->nroaula ?? 'N/A',
                    'N/A',
                ]);
            }

            $data->push(['']); // Línea vacía entre días
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Reporte de Horarios Semanales',
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
        return 'Horarios Semanales';
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CargaHorariaExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $cargaHoraria;

    public function __construct($cargaHoraria)
    {
        $this->cargaHoraria = $cargaHoraria;
    }

    public function collection()
    {
        $data = collect();

        $data->push([
            'Docente',
            'Total Períodos',
            'Materias',
            'Períodos por Materia',
        ]);

        foreach ($this->cargaHoraria as $carga) {
            $docente = $carga['docente'];
            $nombreCompleto = $docente->nombre;

            $materias = implode(', ', array_keys($carga['materias']));
            $periodos = implode(', ', array_map(function ($materia, $periodos) {
                return $materia . ': ' . $periodos;
            }, array_keys($carga['materias']), $carga['materias']));

            $data->push([
                $nombreCompleto,
                $carga['total_periodos'],
                $materias,
                $periodos,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Reporte de Carga Horaria',
            'Generado: ' . now()->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Carga Horaria';
    }
}

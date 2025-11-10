<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AulasDisponiblesExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $disponibilidadAulas;
    protected $dia;

    public function __construct($disponibilidadAulas, $dia)
    {
        $this->disponibilidadAulas = $disponibilidadAulas;
        $this->dia = $dia;
    }

    public function collection()
    {
        $data = collect();

        if ($this->dia) {
            $data->push([
                'Día:',
                $this->dia->descripcion,
            ]);
            $data->push(['']); // Línea vacía
        }

        // Encabezados de tabla
        $data->push([
            'Aula',
            'Capacidad',
            'Períodos Ocupados',
            'Períodos Disponibles',
            '% Ocupación',
        ]);

        // Datos de disponibilidad
        foreach ($this->disponibilidadAulas as $disponibilidad) {
            $data->push([
                $disponibilidad['aula']->nroaula,
                $disponibilidad['aula']->capacidad,
                $disponibilidad['periodos_ocupados'],
                $disponibilidad['periodos_disponibles'],
                $disponibilidad['porcentaje_ocupacion'] . '%',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Reporte de Aulas Disponibles',
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
        return 'Aulas Disponibles';
    }
}

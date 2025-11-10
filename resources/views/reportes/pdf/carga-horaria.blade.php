<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Carga Horaria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .docente-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .docente-header {
            background-color: #4a90e2;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .summary {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #4a90e2;
        }
        .summary p {
            margin: 5px 0;
        }
        .materias-list {
            margin-top: 10px;
        }
        .materia-item {
            padding: 5px 10px;
            margin: 5px 0;
            background-color: #f9f9f9;
            border-left: 3px solid #4a90e2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #e0e0e0;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ccc;
        }
        table td {
            padding: 6px;
            border: 1px solid #ccc;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-general {
            background-color: #4a90e2;
            color: white;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE CARGA HORARIA</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $totalPeriodosGeneral = 0;
    @endphp

    @foreach($cargaHoraria as $carga)
        @php
            $docente = $carga['docente'];
            $totalPeriodosGeneral += $carga['total_periodos'];
        @endphp
        
        <div class="docente-section">
            <div class="docente-header">
                {{ $docente->nombre }}
            </div>

            <div class="summary">
                <p><strong>Total de Períodos:</strong> {{ $carga['total_periodos'] }}</p>
                <p><strong>Número de Materias:</strong> {{ count($carga['materias']) }}</p>
            </div>

            <div class="materias-list">
                <strong>Distribución por Materia:</strong>
                @foreach($carga['materias'] as $nombreMateria => $periodos)
                    <div class="materia-item">
                        {{ $nombreMateria }}: <strong>{{ $periodos }} período(s)</strong>
                    </div>
                @endforeach
            </div>

            @if($carga['horarios']->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Día</th>
                            <th style="width: 18%;">Horario</th>
                            <th style="width: 12%;">Grupo</th>
                            <th style="width: 35%;">Materia</th>
                            <th style="width: 10%;">Aula</th>
                            <th style="width: 10%;">Módulo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carga['horarios'] as $horario)
                            @foreach($horario->dias as $dia)
                            <tr>
                                <td>{{ $dia->descripcion }}</td>
                                <td>{{ $horario->horaini }} - {{ $horario->horafin }}</td>
                                <td>{{ $horario->grupo->sigla ?? 'N/A' }}</td>
                                <td>
                                    @if($horario->grupo && $horario->grupo->materias->isNotEmpty())
                                        {{ $horario->grupo->materias->first()->nombre }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $horario->aula->nroaula ?? 'N/A' }}</td>
                                <td>{{ $horario->aula->modulo->codigo ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <div class="total-general">
        TOTAL GENERAL DE PERÍODOS: {{ $totalPeriodosGeneral }}
    </div>

    <div class="footer">
        Sistema de Gestión Académica - Página <span class="pagenum"></span>
    </div>
</body>
</html>

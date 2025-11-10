<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia</title>
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
        .filters {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .filters p {
            margin: 5px 0;
        }
        .estadisticas {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-card {
            display: table-cell;
            width: 16.66%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stat-card .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .stat-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-card.total { background-color: #e3f2fd; }
        .stat-card.presente { background-color: #e8f5e9; }
        .stat-card.ausente { background-color: #ffebee; }
        .stat-card.licencia { background-color: #fff3e0; }
        .stat-card.retraso { background-color: #fce4ec; }
        .stat-card.porcentaje { background-color: #f3e5f5; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th {
            background-color: #4a90e2;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #4a90e2;
            font-size: 10px;
        }
        table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge-si {
            background-color: #4caf50;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        .badge-no {
            background-color: #f44336;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        .badge-justificado {
            background-color: #ff9800;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            display: inline-block;
            margin-top: 2px;
        }
        .nota-justificacion {
            font-size: 8px;
            color: #ff9800;
            font-style: italic;
            margin-top: 2px;
        }
        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
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
        <h1>REPORTE DE ASISTENCIA</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if($docente || $grupo || $fechaInicio || $fechaFin)
    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @if($docente)
            <p><strong>Docente:</strong> {{ $docente->nombre }}</p>
        @endif
        @if($grupo)
            <p><strong>Grupo:</strong> {{ $grupo->sigla }}</p>
        @endif
        @if($fechaInicio && $fechaFin)
            <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        @endif
        <p style="font-size: 9px; color: #ff9800; margin-top: 8px;">
            <strong>Nota:</strong> Las licencias marcadas como "JUSTIFICADA" corresponden a ausencias o tardanzas que fueron respaldadas con una justificación aprobada.
        </p>
    </div>
    @endif

    <div class="estadisticas">
        <div class="stat-card total">
            <div class="label">Total</div>
            <div class="value">{{ $totalAsistencias }}</div>
        </div>
        <div class="stat-card presente">
            <div class="label">Puntuales</div>
            <div class="value">{{ $presentes }}</div>
        </div>
        <div class="stat-card ausente">
            <div class="label">Ausentes</div>
            <div class="value">{{ $ausentes }}</div>
        </div>
        <div class="stat-card licencia">
            <div class="label">Licencias</div>
            <div class="value">{{ $licencias }}</div>
        </div>
        <div class="stat-card retraso">
            <div class="label">Tardanzas</div>
            <div class="value">{{ $retrasos }}</div>
        </div>
        <div class="stat-card porcentaje">
            <div class="label">% Asistencia</div>
            <div class="value">{{ $porcentajeAsistencia }}%</div>
        </div>
    </div>

    @if($asistencias->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 12%;">Horario</th>
                    <th style="width: 20%;">Docente</th>
                    <th style="width: 10%;">Grupo</th>
                    <th style="width: 18%;">Materia</th>
                    <th style="width: 8%;">Aula</th>
                    <th style="width: 7%;">Puntual</th>
                    <th style="width: 7%;">Tardanza</th>
                    <th style="width: 8%;">Licencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asistencias as $asistencia)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($asistencia->hora)->format('H:i') }}</td>
                    <td>{{ $asistencia->usuario->nombre ?? 'N/A' }}</td>
                    <td>{{ $asistencia->horario->grupo->sigla ?? 'N/A' }}</td>
                    <td>
                        @if($asistencia->horario->grupo && $asistencia->horario->grupo->materias->isNotEmpty())
                            {{ $asistencia->horario->grupo->materias->first()->nombre }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $asistencia->horario->aula->nroaula ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        @if(trim(strtolower($asistencia->tipo)) == 'puntual')
                            <span class="badge-si">SÍ</span>
                        @else
                            <span class="badge-no">NO</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(trim(strtolower($asistencia->tipo)) == 'tardanza')
                            <span class="badge-si">SÍ</span>
                        @else
                            <span class="badge-no">NO</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(trim(strtolower($asistencia->tipo)) == 'licencia')
                            <span class="badge-si">SÍ</span>
                            @if($asistencia->tiene_justificacion ?? false)
                                <br><span class="badge-justificado">JUSTIFICADA</span>
                            @endif
                        @else
                            <span class="badge-no">NO</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No se encontraron registros de asistencia con los filtros aplicados</div>
    @endif

    <div class="footer">
        Sistema de Gestión Académica - Página <span class="pagenum"></span>
    </div>
</body>
</html>

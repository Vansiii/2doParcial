<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Aulas Disponibles</title>
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
        .aula-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        .aula-header {
            background-color: #4a90e2;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .aula-stats {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stat-item .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-top: 3px;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background-color: #4a90e2;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 10px;
            font-weight: bold;
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
            font-size: 10px;
        }
        table td {
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-horarios {
            text-align: center;
            color: #4caf50;
            font-style: italic;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 3px;
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
        <h1>REPORTE DE AULAS DISPONIBLES</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if($dia)
    <div class="filters">
        <strong>Filtro aplicado:</strong>
        <p><strong>Día:</strong> {{ $dia->descripcion }}</p>
    </div>
    @endif

    @foreach($disponibilidadAulas as $disponibilidad)
        @php
            $aula = $disponibilidad['aula'];
            $porcentaje = $disponibilidad['porcentaje_ocupacion'];
        @endphp
        
        <div class="aula-card">
            <div class="aula-header">
                AULA {{ $aula->nroaula }} - Capacidad: {{ $aula->capacidad }} personas
            </div>

            <div class="aula-stats">
                <div class="stat-item">
                    <div class="label">Períodos Ocupados</div>
                    <div class="value">{{ $disponibilidad['periodos_ocupados'] }}</div>
                </div>
                <div class="stat-item">
                    <div class="label">Períodos Disponibles</div>
                    <div class="value">{{ $disponibilidad['periodos_disponibles'] }}</div>
                </div>
                <div class="stat-item">
                    <div class="label">% Ocupación</div>
                    <div class="value">{{ $porcentaje }}%</div>
                </div>
                <div class="stat-item">
                    <div class="label">Estado</div>
                    <div class="value" style="color: {{ $porcentaje > 75 ? '#f44336' : ($porcentaje > 50 ? '#ff9800' : '#4caf50') }};">
                        @if($porcentaje > 75)
                            ALTA
                        @elseif($porcentaje > 50)
                            MEDIA
                        @else
                            BAJA
                        @endif
                    </div>
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $porcentaje }}%; background-color: {{ $porcentaje > 75 ? '#f44336' : ($porcentaje > 50 ? '#ff9800' : '#4caf50') }};">
                    {{ $porcentaje }}%
                </div>
            </div>

            @if($disponibilidad['horarios']->count() > 0)
                <strong style="display: block; margin-top: 10px; margin-bottom: 5px;">Horarios Ocupados:</strong>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Día</th>
                            <th style="width: 15%;">Horario</th>
                            <th style="width: 30%;">Docente</th>
                            <th style="width: 15%;">Grupo</th>
                            <th style="width: 25%;">Materia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disponibilidad['horarios'] as $horario)
                            @foreach($horario->dias as $diaHorario)
                            <tr>
                                <td>{{ $diaHorario->descripcion }}</td>
                                <td>{{ $horario->horaini }} - {{ $horario->horafin }}</td>
                                <td>
                                    @if($horario->grupo && $horario->grupo->docentes->isNotEmpty())
                                        {{ $horario->grupo->docentes->first()->nombre }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $horario->grupo->sigla ?? 'N/A' }}</td>
                                <td>
                                    @if($horario->grupo && $horario->grupo->materias->isNotEmpty())
                                        {{ $horario->grupo->materias->first()->nombre }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-horarios">✓ Aula completamente disponible (sin horarios asignados)</div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        Sistema de Gestión Académica - Página <span class="pagenum"></span>
    </div>
</body>
</html>

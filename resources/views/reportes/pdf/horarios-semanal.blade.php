<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Horarios Semanales</title>
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
        .day-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .day-header {
            background-color: #4a90e2;
            color: white;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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
        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 10px;
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
        <h1>REPORTE DE HORARIOS SEMANALES</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if($docente || $grupo)
    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @if($docente)
            <p><strong>Docente:</strong> {{ $docente->nombre }}</p>
        @endif
        @if($grupo)
            <p><strong>Grupo:</strong> {{ $grupo->sigla }}</p>
        @endif
    </div>
    @endif

    @foreach($dias as $dia)
        @php
            $horariosDelDia = $horariosPorDia[$dia->nombre] ?? collect();
        @endphp
        
        <div class="day-section">
            <div class="day-header">{{ strtoupper($dia->nombre) }}</div>
            
            @if($horariosDelDia->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Horario</th>
                            <th style="width: 25%;">Docente</th>
                            <th style="width: 10%;">Grupo</th>
                            <th style="width: 25%;">Materia</th>
                            <th style="width: 10%;">Aula</th>
                            <th style="width: 15%;">Semestre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($horariosDelDia as $horario)
                        <tr>
                            <td>{{ $horario->horaini }} - {{ $horario->horafin }}</td>
                            <td>
                                @php
                                    // Obtener el docente correcto desde grupo_materia
                                    $materia = $horario->materias->first();
                                    $docenteNombre = 'N/A';
                                    if ($materia && $horario->grupo) {
                                        $gm = DB::table('grupo_materia')
                                            ->where('id_grupo', $horario->grupo->id)
                                            ->where('sigla_materia', $materia->sigla)
                                            ->first();
                                        if ($gm) {
                                            $docenteObj = \App\Models\Usuario::find($gm->id_docente);
                                            $docenteNombre = $docenteObj ? $docenteObj->nombre : 'N/A';
                                        }
                                    }
                                @endphp
                                {{ $docenteNombre }}
                            </td>
                            <td>{{ $horario->grupo->sigla ?? 'N/A' }}</td>
                            <td>
                                @if($horario->materias->isNotEmpty())
                                    {{ $horario->materias->first()->nombre }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $horario->aula->nroaula ?? 'N/A' }}</td>
                            <td>
                                @if($horario->grupo && $horario->grupo->periodo)
                                    {{ $horario->grupo->periodo->gestion }}/{{ $horario->grupo->periodo->periodo }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">No hay horarios programados para este día</div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        Sistema de Gestión Académica - Página <span class="pagenum"></span>
    </div>
</body>
</html>

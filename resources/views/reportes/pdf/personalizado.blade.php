<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Personalizado - {{ ucfirst($tipoReporte) }}</title>
    <style>
        @page {
            margin: 15mm;
            size: landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 9px;
        }
        .info-box {
            background-color: #f5f5f5;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 3px;
            font-size: 9px;
        }
        .info-box strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table thead {
            background-color: #4a90e2;
            color: white;
        }
        table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ccc;
            font-size: 10px;
        }
        table td {
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 9px;
            vertical-align: top;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tbody tr:hover {
            background-color: #f0f0f0;
        }
        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
        }
        .footer {
            position: fixed;
            bottom: 10px;
            right: 15px;
            font-size: 8px;
            color: #666;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        .small-text {
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Reporte Personalizado: {{ ucfirst($tipoReporte) }}</h1>
        <p>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Información de filtros aplicados -->
    @if(isset($filtroInfo) && $filtroInfo)
        <div class="info-box">
            <strong>Filtros aplicados:</strong> {{ $filtroInfo }}
        </div>
    @endif

    <!-- Información de columnas seleccionadas -->
    <div class="info-box">
        <strong>Columnas incluidas:</strong> {{ implode(', ', array_map('ucfirst', $columnas)) }}
    </div>

    <!-- Tabla de datos -->
    @if(count($datos) > 0)
        <table>
            <thead>
                <tr>
                    @foreach($columnas as $columna)
                        <th>{{ ucfirst(str_replace('_', ' ', $columna)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($datos as $fila)
                    <tr>
                        @foreach($columnas as $columna)
                            <td>{{ $fila[$columna] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Resumen -->
        <div class="info-box">
            <strong>Total de registros:</strong> {{ count($datos) }}
        </div>
    @else
        <div class="no-data">
            No se encontraron datos para mostrar con los filtros seleccionados.
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Sistema de Gestión Académica - Página {PAGE_NUM} de {PAGE_COUNT}
    </div>
</body>
</html>

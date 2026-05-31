<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estadisticas del dashboard</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Estadisticas del dashboard</h1>
    <p class="muted">Resumen generado automaticamente desde el sistema.</p>

    <h2>Metricas principales</h2>
    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Valor</th>
                <th>Descripcion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $stat)
                <tr>
                    <td>{{ $stat['title'] }}</td>
                    <td>{{ $stat['value'] }}</td>
                    <td>{{ $stat['subtitle'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Actividad reciente</h2>
    <table>
        <thead>
            <tr>
                <th>Archivo</th>
                <th>Tiempo</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentActivity as $item)
                <tr>
                    <td>{{ $item['file'] }}</td>
                    <td>{{ $item['time'] }}</td>
                    <td>{{ $item['type'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay actividad reciente disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>ArrhythmiaAI - Reporte Clínico</title>
    <style>
        @page {
            margin: 80px 50px;
        }

        header {
            position: fixed;
            top: -60px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 3px solid #003366;
        }

        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 8px;
            color: #aaa;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        /* Fondo de rejilla ECG sutil */
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
            background-image: radial-gradient(#e5e5e5 0.5px, transparent 0.5px);
            background-size: 20px 20px;
            /* Simula papel milimetrado de ECG muy suave */
        }

        .main-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 10px;
        }

        .logo-text {
            font-size: 26px;
            font-weight: bold;
            color: #003366;
            letter-spacing: -1px;
        }

        .logo-text span {
            color: #00aaff;
        }

        /* El "AI" en otro color */

        .report-title {
            text-align: right;
            text-transform: uppercase;
            margin-top: -40px;
        }

        .report-title h1 {
            margin: 0;
            font-size: 18px;
            color: #444;
        }

        .report-title p {
            margin: 0;
            font-size: 10px;
            color: #00aaff;
            font-weight: bold;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #ccc;
        }

        .info-grid td {
            padding: 10px;
            border: 1px solid #eee;
        }

        .bg-navy {
            background-color: #003366;
            color: white;
            font-weight: bold;
            width: 25%;
        }

        .section-header {
            margin-top: 25px;
            padding: 5px 0;
            border-bottom: 2px solid #00aaff;
            font-weight: bold;
            color: #003366;
            font-size: 12px;
        }

        .result-card {
            margin-top: 10px;
            padding: 15px;
            border-radius: 5px;
            background: white;
            border: 1px solid #e0e0e0;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.05);
        }

        .ai-border {
            border-left: 6px solid #00aaff;
        }

        .md-border {
            border-left: 6px solid #003366;
        }

        .prob-bar-container {
            width: 100%;
            background-color: #eee;
            height: 10px;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .prob-bar-fill {
            background-color: #00aaff;
            height: 100%;
            border-radius: 5px;
        }

        .status-pill {
            float: right;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .pill-active {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #1976d2;
        }

        .disclaimer {
            margin-top: 50px;
            font-size: 8px;
            color: #888;
            text-align: justify;
            line-height: 1.2;
            padding: 10px;
            background: #fdfdfd;
            border: 1px solid #eee;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo-text">Arrhythmia<span>AI</span></div>
        <div style="font-size: 8px; color: #666; margin-top: -5px;">INTELLIGENT CARDIAC ANALYSIS SYSTEM</div>
        <div class="report-title">
            <h1>INFORME CLÍNICO</h1>
            <p>EXPEDIENTE: {{ $paciente['codigo_generado'] }}</p>
        </div>
    </header>

    <footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">ArrhythmiaAI v1.0 - Análisis Confidencial</td>
                <td style="text-align: right;">Generado electrónicamente el {{ date('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </footer>

    <main class="main-container">

        <table class="info-grid">
            <tr>
                <td class="bg-navy">ID PACIENTE</td>
                <td>{{ $paciente['codigo_generado'] }}</td>
                <td class="bg-navy">SISTEMA</td>
                <td>ArrhythmiaAI Cloud</td>
            </tr>
            <tr>
                <td class="bg-navy">EDAD / GÉNERO</td>
                <td>{{ $paciente['edad'] }} AÑOS / {{ $paciente['genero'] }}</td>
                <td class="bg-navy">FECHA ESTUDIO</td>
                <td>{{ $estudio['fecha'] }}</td>
            </tr>
        </table>

        <div class="section-header">ANÁLISIS DE REDES NEURONALES (AI ENGINE)</div>
        <div class="result-card ai-border">
            <span class="status-pill pill-active">Procesado</span>
            <div style="font-size: 10px; color: #666;">HALLAZGO PREDICHO:</div>
            <div style="font-size: 16px; font-weight: bold; color: #003366; margin: 5px 0;">{{ $ia['resultado'] }}</div>
            <div style="margin-top: 10px;">
                <span>Nivel de Confianza Predictiva: <strong>{{ $ia['probabilidad'] }}</strong></span>
                <div class="prob-bar-container">
                    <div class="prob-bar-fill" style="width: {{ $ia['probabilidad'] }};"></div>
                </div>
            </div>
        </div>

        <div class="section-header">CONFIRMACIÓN MÉDICA ESPECIALIZADA</div>
        <div class="result-card md-border">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <div style="font-size: 10px; color: #666;">DIAGNÓSTICO FINAL:</div>
                        <div style="font-size: 14px; font-weight: bold; margin: 5px 0;">{{ $diagnostico['ritmo'] }}
                        </div>
                    </td>
                    <td style="text-align: right; vertical-align: top;">
                        <span class="status-pill pill-active"
                            style="background-color: #e8f5e9; color: #2e7d32; border-color: #2e7d32;">
                            {{ $diagnostico['concordancia'] ? 'CONCORDANTE' : 'DIVERGENTE' }}
                        </span>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 10px; border-top: 1px dashed #eee; padding-top: 10px;">
                <div style="font-weight: bold; margin-bottom: 5px;">Observación Facultativa:</div>
                <div style="font-style: italic; color: #555;">"{{ $diagnostico['observacion'] }}"</div>
            </div>
        </div>

        <div class="disclaimer">
            <strong>ADVERTENCIA TÉCNICA Y LEGAL:</strong> Este documento ha sido generado por el motor de análisis
            <strong>ArrhythmiaAI</strong>.
            Este informe es una herramienta de apoyo a la decisión clínica y no debe utilizarse como único criterio para
            el diagnóstico o tratamiento.
            La clasificación algorítmica es una asistencia estadística para el médico. La responsabilidad de la
            interpretación clínica final recae exclusivamente en el personal de salud autorizado.
            Este documento está anonimizado bajo estándares de protección de datos en salud.
        </div>
    </main>

</body>

</html>
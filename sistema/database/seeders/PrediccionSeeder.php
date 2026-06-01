<?php

namespace Database\Seeders;

use App\Models\Prediccion;
use Illuminate\Database\Seeder;

class PrediccionSeeder extends Seeder
{
    /**
     * Seeder para cargar predicciones de ritmos cardíacos
     * 
     * DESCRIPCIÓN:
     * - Carga 63 registros de predicciones de ECG
     * - Utiliza updateOrCreate() para evitar duplicados
     * - Si prediccion_id existe: ACTUALIZA todos los campos
     * - Si no existe: CREA un nuevo registro
     * 
     * CAMPOS QUE SE MODIFICAN:
     * ✅ imagen_id       → ID de imagen asociada (1-63)
     * ✅ ritmo_id        → Tipo de ritmo cardíaco (1,4,6,7,9)
     * ✅ probabilidad    → Confianza de predicción (0.8497-0.9658)
     * ✅ tiempo_ms       → FIJADO A NULL (tiempo no registrado)
     * ✅ top_predicciones → JSON con 2 predicciones alternativas
     * ✅ label_detectado → Nombre legible del ritmo
     * ✅ label_code      → Código corto (NORM, AFIB, PVC, etc)
     * ✅ tipo            → Clasificación (1=Normal, 2=Arritmia)
     * ✅ estado          → FIJADO A 1 (Activo)
     * ✅ created_at      → Fecha de creación/evento
     * ✅ updated_at      → Fecha de actualización
     */
    public function run(): void
    {
        // Array con 63 registros de predicciones de ritmos cardíacos
        // Período: 2026-04-06 a 2026-05-05
        // Distribución: 51% Normal, 35% Fibrilación, 8% Bradicardia, 5% PVC, 1% Taquicardia
        $predicciones = [
            ['prediccion_id' => 1, 'imagen_id' => 1, 'ritmo_id' => 7, 'probabilidad' => 0.9486, 'top_predicciones' => '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 94.86}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.14}]', 'label_detectado' => 'Taquicardia Sinusal', 'label_code' => 'STACH', 'tipo' => 2, 'created_at' => '2026-04-06 08:10:00'],
            ['prediccion_id' => 2, 'imagen_id' => 2, 'ritmo_id' => 6, 'probabilidad' => 0.9434, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.34}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.66}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-06 09:25:00'],
            ['prediccion_id' => 3, 'imagen_id' => 3, 'ritmo_id' => 1, 'probabilidad' => 0.9324, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.24}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.76}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-06 10:40:00'],
            ['prediccion_id' => 4, 'imagen_id' => 4, 'ritmo_id' => 6, 'probabilidad' => 0.9077, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 90.77}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.23}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-07 08:05:00'],
            ['prediccion_id' => 5, 'imagen_id' => 5, 'ritmo_id' => 1, 'probabilidad' => 0.8708, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 87.08}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 12.92}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-07 09:20:00'],
            ['prediccion_id' => 6, 'imagen_id' => 6, 'ritmo_id' => 1, 'probabilidad' => 0.8801, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.01}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.99}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-07 10:35:00'],
            ['prediccion_id' => 7, 'imagen_id' => 7, 'ritmo_id' => 1, 'probabilidad' => 0.8996, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.96}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.04}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-07 11:50:00'],
            ['prediccion_id' => 8, 'imagen_id' => 8, 'ritmo_id' => 4, 'probabilidad' => 0.8808, 'top_predicciones' => '[{"code": "PVC", "label": "Complejo ventricular prematuro", "probability": 88.08}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.92}]', 'label_detectado' => 'Complejo ventricular prematuro', 'label_code' => 'PVC', 'tipo' => 2, 'created_at' => '2026-04-08 08:15:00'],
            ['prediccion_id' => 9, 'imagen_id' => 9, 'ritmo_id' => 1, 'probabilidad' => 0.8725, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 87.25}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 12.75}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-08 09:30:00'],
            ['prediccion_id' => 10, 'imagen_id' => 10, 'ritmo_id' => 9, 'probabilidad' => 0.8670, 'top_predicciones' => '[{"code": "SBRAD", "label": "Bradicardia Sinusal", "probability": 86.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 13.30}]', 'label_detectado' => 'Bradicardia Sinusal', 'label_code' => 'SBRAD', 'tipo' => 2, 'created_at' => '2026-04-09 08:00:00'],
            ['prediccion_id' => 11, 'imagen_id' => 11, 'ritmo_id' => 6, 'probabilidad' => 0.9250, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 92.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.50}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-09 09:15:00'],
            ['prediccion_id' => 12, 'imagen_id' => 12, 'ritmo_id' => 1, 'probabilidad' => 0.8631, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 86.31}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 13.69}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-09 10:30:00'],
            ['prediccion_id' => 13, 'imagen_id' => 13, 'ritmo_id' => 6, 'probabilidad' => 0.8727, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 87.27}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 12.73}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-10 08:20:00'],
            ['prediccion_id' => 14, 'imagen_id' => 14, 'ritmo_id' => 1, 'probabilidad' => 0.8524, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 85.24}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 14.76}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-10 09:35:00'],
            ['prediccion_id' => 15, 'imagen_id' => 15, 'ritmo_id' => 1, 'probabilidad' => 0.8838, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.38}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.62}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-10 10:50:00'],
            ['prediccion_id' => 16, 'imagen_id' => 16, 'ritmo_id' => 6, 'probabilidad' => 0.9105, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 91.05}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.95}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-13 08:10:00'],
            ['prediccion_id' => 17, 'imagen_id' => 17, 'ritmo_id' => 1, 'probabilidad' => 0.9658, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 96.58}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 3.42}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-13 09:25:00'],
            ['prediccion_id' => 18, 'imagen_id' => 18, 'ritmo_id' => 6, 'probabilidad' => 0.9281, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 92.81}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.19}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-14 08:05:00'],
            ['prediccion_id' => 19, 'imagen_id' => 19, 'ritmo_id' => 1, 'probabilidad' => 0.9087, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.87}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.13}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-14 09:20:00'],
            ['prediccion_id' => 20, 'imagen_id' => 20, 'ritmo_id' => 1, 'probabilidad' => 0.8970, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.30}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-14 10:35:00'],
            ['prediccion_id' => 21, 'imagen_id' => 21, 'ritmo_id' => 6, 'probabilidad' => 0.9269, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 92.69}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.31}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-14 11:50:00'],
            ['prediccion_id' => 22, 'imagen_id' => 22, 'ritmo_id' => 9, 'probabilidad' => 0.8768, 'top_predicciones' => '[{"code": "SBRAD", "label": "Bradicardia Sinusal", "probability": 87.68}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 12.32}]', 'label_detectado' => 'Bradicardia Sinusal', 'label_code' => 'SBRAD', 'tipo' => 2, 'created_at' => '2026-04-15 08:15:00'],
            ['prediccion_id' => 23, 'imagen_id' => 23, 'ritmo_id' => 1, 'probabilidad' => 0.8497, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 84.97}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 15.03}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-15 09:30:00'],
            ['prediccion_id' => 24, 'imagen_id' => 24, 'ritmo_id' => 1, 'probabilidad' => 0.8583, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 85.83}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 14.17}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-15 10:45:00'],
            ['prediccion_id' => 25, 'imagen_id' => 25, 'ritmo_id' => 6, 'probabilidad' => 0.9339, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 93.39}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 6.61}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-16 08:00:00'],
            ['prediccion_id' => 26, 'imagen_id' => 26, 'ritmo_id' => 1, 'probabilidad' => 0.8618, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 86.18}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 13.82}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-16 09:15:00'],
            ['prediccion_id' => 27, 'imagen_id' => 27, 'ritmo_id' => 1, 'probabilidad' => 0.8661, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 86.61}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 13.39}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-16 10:30:00'],
            ['prediccion_id' => 28, 'imagen_id' => 28, 'ritmo_id' => 6, 'probabilidad' => 0.8632, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 86.32}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 13.68}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-17 08:20:00'],
            ['prediccion_id' => 29, 'imagen_id' => 29, 'ritmo_id' => 6, 'probabilidad' => 0.8655, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 86.55}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 13.45}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-17 09:35:00'],
            ['prediccion_id' => 30, 'imagen_id' => 30, 'ritmo_id' => 1, 'probabilidad' => 0.8514, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 85.14}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 14.86}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-17 10:50:00'],
            ['prediccion_id' => 31, 'imagen_id' => 31, 'ritmo_id' => 6, 'probabilidad' => 0.8856, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 88.56}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.44}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-20 08:05:00'],
            ['prediccion_id' => 32, 'imagen_id' => 32, 'ritmo_id' => 1, 'probabilidad' => 0.8878, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.78}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.22}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-20 09:20:00'],
            ['prediccion_id' => 33, 'imagen_id' => 33, 'ritmo_id' => 1, 'probabilidad' => 0.8615, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 86.15}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 13.85}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-20 10:35:00'],
            ['prediccion_id' => 34, 'imagen_id' => 34, 'ritmo_id' => 6, 'probabilidad' => 0.9021, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 90.21}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.79}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-20 11:50:00'],
            ['prediccion_id' => 35, 'imagen_id' => 35, 'ritmo_id' => 4, 'probabilidad' => 0.8586, 'top_predicciones' => '[{"code": "PVC", "label": "Complejo ventricular prematuro", "probability": 85.86}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 14.14}]', 'label_detectado' => 'Complejo ventricular prematuro', 'label_code' => 'PVC', 'tipo' => 2, 'created_at' => '2026-04-21 08:15:00'],
            ['prediccion_id' => 36, 'imagen_id' => 36, 'ritmo_id' => 1, 'probabilidad' => 0.8748, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 87.48}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 12.52}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-21 09:30:00'],
            ['prediccion_id' => 37, 'imagen_id' => 37, 'ritmo_id' => 6, 'probabilidad' => 0.9359, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 93.59}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 6.41}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-22 08:00:00'],
            ['prediccion_id' => 38, 'imagen_id' => 38, 'ritmo_id' => 1, 'probabilidad' => 0.8648, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 86.48}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 13.52}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-22 09:15:00'],
            ['prediccion_id' => 39, 'imagen_id' => 39, 'ritmo_id' => 1, 'probabilidad' => 0.9359, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.59}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.41}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-22 10:30:00'],
            ['prediccion_id' => 40, 'imagen_id' => 40, 'ritmo_id' => 4, 'probabilidad' => 0.9004, 'top_predicciones' => '[{"code": "PVC", "label": "Complejo ventricular prematuro", "probability": 90.04}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.96}]', 'label_detectado' => 'Complejo ventricular prematuro', 'label_code' => 'PVC', 'tipo' => 2, 'created_at' => '2026-04-23 08:20:00'],
            ['prediccion_id' => 41, 'imagen_id' => 41, 'ritmo_id' => 1, 'probabilidad' => 0.8516, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 85.16}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 14.84}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-23 09:35:00'],
            ['prediccion_id' => 42, 'imagen_id' => 42, 'ritmo_id' => 1, 'probabilidad' => 0.8746, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 87.46}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 12.54}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-23 10:50:00'],
            ['prediccion_id' => 43, 'imagen_id' => 43, 'ritmo_id' => 9, 'probabilidad' => 0.9034, 'top_predicciones' => '[{"code": "SBRAD", "label": "Bradicardia Sinusal", "probability": 90.34}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.66}]', 'label_detectado' => 'Bradicardia Sinusal', 'label_code' => 'SBRAD', 'tipo' => 2, 'created_at' => '2026-04-24 08:05:00'],
            ['prediccion_id' => 44, 'imagen_id' => 44, 'ritmo_id' => 1, 'probabilidad' => 0.8876, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.76}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.24}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-24 09:20:00'],
            ['prediccion_id' => 45, 'imagen_id' => 45, 'ritmo_id' => 1, 'probabilidad' => 0.8947, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.47}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.53}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-24 10:35:00'],
            ['prediccion_id' => 46, 'imagen_id' => 46, 'ritmo_id' => 6, 'probabilidad' => 0.8652, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 86.52}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 13.48}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-27 08:15:00'],
            ['prediccion_id' => 47, 'imagen_id' => 47, 'ritmo_id' => 1, 'probabilidad' => 0.9305, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.05}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.95}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-27 09:30:00'],
            ['prediccion_id' => 48, 'imagen_id' => 48, 'ritmo_id' => 1, 'probabilidad' => 0.8595, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 85.95}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 14.05}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-27 10:45:00'],
            ['prediccion_id' => 49, 'imagen_id' => 49, 'ritmo_id' => 1, 'probabilidad' => 0.8723, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 87.23}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 12.77}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-27 12:00:00'],
            ['prediccion_id' => 50, 'imagen_id' => 50, 'ritmo_id' => 9, 'probabilidad' => 0.8968, 'top_predicciones' => '[{"code": "SBRAD", "label": "Bradicardia Sinusal", "probability": 89.68}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.32}]', 'label_detectado' => 'Bradicardia Sinusal', 'label_code' => 'SBRAD', 'tipo' => 2, 'created_at' => '2026-04-28 08:00:00'],
            ['prediccion_id' => 51, 'imagen_id' => 51, 'ritmo_id' => 1, 'probabilidad' => 0.8748, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 87.48}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 12.52}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-28 09:15:00'],
            ['prediccion_id' => 52, 'imagen_id' => 52, 'ritmo_id' => 1, 'probabilidad' => 0.8532, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 85.32}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 14.68}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-28 10:30:00'],
            ['prediccion_id' => 53, 'imagen_id' => 53, 'ritmo_id' => 6, 'probabilidad' => 0.9224, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 92.24}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.76}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-29 08:20:00'],
            ['prediccion_id' => 54, 'imagen_id' => 54, 'ritmo_id' => 6, 'probabilidad' => 0.8793, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 87.93}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 12.07}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-29 09:35:00'],
            ['prediccion_id' => 55, 'imagen_id' => 55, 'ritmo_id' => 1, 'probabilidad' => 0.8828, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.28}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.72}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-29 10:50:00'],
            ['prediccion_id' => 56, 'imagen_id' => 56, 'ritmo_id' => 6, 'probabilidad' => 0.9649, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 96.49}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 3.51}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-04-30 08:05:00'],
            ['prediccion_id' => 57, 'imagen_id' => 57, 'ritmo_id' => 1, 'probabilidad' => 0.9045, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.45}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.55}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-04-30 09:20:00'],
            ['prediccion_id' => 58, 'imagen_id' => 58, 'ritmo_id' => 9, 'probabilidad' => 0.8764, 'top_predicciones' => '[{"code": "SBRAD", "label": "Bradicardia Sinusal", "probability": 87.64}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 12.36}]', 'label_detectado' => 'Bradicardia Sinusal', 'label_code' => 'SBRAD', 'tipo' => 2, 'created_at' => '2026-05-04 08:15:00'],
            ['prediccion_id' => 59, 'imagen_id' => 59, 'ritmo_id' => 6, 'probabilidad' => 0.9062, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 90.62}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.38}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-05-04 09:30:00'],
            ['prediccion_id' => 60, 'imagen_id' => 60, 'ritmo_id' => 1, 'probabilidad' => 0.9044, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.44}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.56}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-05-04 10:45:00'],
            ['prediccion_id' => 61, 'imagen_id' => 61, 'ritmo_id' => 1, 'probabilidad' => 0.9037, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.37}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.63}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-05-04 12:00:00'],
            ['prediccion_id' => 62, 'imagen_id' => 62, 'ritmo_id' => 6, 'probabilidad' => 0.8758, 'top_predicciones' => '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 87.58}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 12.42}]', 'label_detectado' => 'Fibrilacion Auricular', 'label_code' => 'AFIB', 'tipo' => 2, 'created_at' => '2026-05-05 08:00:00'],
            ['prediccion_id' => 63, 'imagen_id' => 63, 'ritmo_id' => 1, 'probabilidad' => 0.9046, 'top_predicciones' => '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.46}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.54}]', 'label_detectado' => 'Ritmo Sinusal Normal', 'label_code' => 'NORM', 'tipo' => 1, 'created_at' => '2026-05-05 09:15:00'],
        ];

        // ====== PROCESAMIENTO DE DATOS ======
        // Para cada predicción en el array:
        // 1. Busca si prediccion_id ya existe
        // 2. Si existe → Actualiza TODOS los campos (UPDATE)
        // 3. Si no existe → Crea nuevo registro (INSERT)
        
        foreach ($predicciones as $p) {
            // ✅ CONDICIÓN DE BÚSQUEDA: solo prediccion_id
            // Este campo identifica unívocamente si el registro ya existe
            Prediccion::updateOrCreate(
                ['prediccion_id' => $p['prediccion_id']],
                
                // ✅ CAMPOS QUE SE ACTUALIZAN/CREAN:
                [
                    // Campo: imagen_id
                    // Descripción: Referencia a la imagen ECG
                    // Cambio: ✅ ASIGNADO (valores 1-63)
                    'imagen_id' => $p['imagen_id'],
                    
                    // Campo: ritmo_id
                    // Descripción: Tipo de ritmo cardíaco detectado
                    // Cambio: ✅ ASIGNADO (valores: 1=Normal, 4=PVC, 6=AFIB, 7=STACH, 9=SBRAD)
                    'ritmo_id' => $p['ritmo_id'],
                    
                    // Campo: probabilidad
                    // Descripción: Confianza de la predicción (0 a 1)
                    // Cambio: ✅ ASIGNADO (rango: 0.8497-0.9658, ~89% promedio)
                    'probabilidad' => $p['probabilidad'],
                    
                    // Campo: tiempo_ms
                    // Descripción: Tiempo de procesamiento en milisegundos
                    // Cambio: ✅ ESTABLECIDO A NULL (no se registra tiempo)
                    'tiempo_ms' => null,
                    
                    // Campo: top_predicciones
                    // Descripción: JSON con las 2 predicciones principales
                    // Cambio: ✅ ASIGNADO (formato JSON válido)
                    // Ejemplo: [{"code":"NORM","label":"Ritmo Sinusal Normal","probability":93.24},...]
                    'top_predicciones' => $p['top_predicciones'],
                    
                    // Campo: label_detectado
                    // Descripción: Nombre legible del ritmo detectado
                    // Cambio: ✅ ASIGNADO
                    // Valores: "Ritmo Sinusal Normal", "Fibrilacion Auricular", 
                    //          "Taquicardia Sinusal", "Complejo ventricular prematuro", "Bradicardia Sinusal"
                    'label_detectado' => $p['label_detectado'],
                    
                    // Campo: label_code
                    // Descripción: Código abreviado del ritmo (para identificación rápida)
                    // Cambio: ✅ ASIGNADO
                    // Valores: NORM (Normal), AFIB (Fibrilación), STACH (Taquicardia), 
                    //          PVC (Complejo Ventricular), SBRAD (Bradicardia)
                    'label_code' => $p['label_code'],
                    
                    // Campo: tipo
                    // Descripción: Clasificación del ritmo
                    // Cambio: ✅ ASIGNADO
                    // Valores: 1 = Normal/Sano, 2 = Arritmia/Anormal
                    'tipo' => $p['tipo'],
                    
                    // Campo: estado
                    // Descripción: Estado del registro en el sistema
                    // Cambio: ✅ ESTABLECIDO A 1 (Activo/Visible)
                    // Nota: Todos los registros comienzan activos
                    'estado' => 1,
                    
                    // Campo: created_at
                    // Descripción: Timestamp de creación/evento
                    // Cambio: ✅ ASIGNADO (preserva fecha original del evento)
                    // Rango: 2026-04-06 08:10:00 a 2026-05-05 09:15:00
                    'created_at' => $p['created_at'],
                    
                    // Campo: updated_at
                    // Descripción: Timestamp de última actualización
                    // Cambio: ✅ ASIGNADO (igualado a created_at inicialmente)
                    // Nota: Se diferencia de created_at solo si se actualiza después
                    'updated_at' => $p['created_at'],
                ]
            );
        }
    }
}

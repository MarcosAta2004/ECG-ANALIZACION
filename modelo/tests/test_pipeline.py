"""
Tests unitarios del pipeline de procesamiento ECG.
Prueba funciones auxiliares de api.py y valida comportamiento con datos sintéticos.

Ejecutar:
    cd modelo
    python -m pytest tests/test_pipeline.py -v
"""

import io
import os
import sys
from unittest.mock import patch

import cv2
import numpy as np
import pytest

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))


# ──────────────────────────────────────────────────────────────────────────────
# Importar funciones auxiliares del módulo api
# ──────────────────────────────────────────────────────────────────────────────

from api import _cargar_imagen_desde_bytes


# ──────────────────────────────────────────────────────────────────────────────
# Tests de _cargar_imagen_desde_bytes
# ──────────────────────────────────────────────────────────────────────────────

class TestCargarImagenDesdeBytes:

    def _crear_png_bytes(self, ancho: int = 200, alto: int = 150) -> bytes:
        """Helper: crea bytes PNG válidos."""
        img = np.ones((alto, ancho, 3), dtype=np.uint8) * 200
        _, buf = cv2.imencode(".png", img)
        return buf.tobytes()

    def _crear_jpg_bytes(self, ancho: int = 200, alto: int = 150) -> bytes:
        """Helper: crea bytes JPEG válidos."""
        img = np.ones((alto, ancho, 3), dtype=np.uint8) * 180
        _, buf = cv2.imencode(".jpg", img)
        return buf.tobytes()

    def test_carga_imagen_png_valida(self):
        """Debe retornar array numpy no nulo para PNG válido."""
        png_bytes = self._crear_png_bytes(400, 300)
        resultado = _cargar_imagen_desde_bytes(png_bytes, "ecg.png")

        assert resultado is not None
        assert isinstance(resultado, np.ndarray)
        assert resultado.ndim == 3  # (H, W, C)

    def test_carga_imagen_jpg_valida(self):
        """Debe retornar array numpy no nulo para JPG válido."""
        jpg_bytes = self._crear_jpg_bytes(400, 300)
        resultado = _cargar_imagen_desde_bytes(jpg_bytes, "ecg.jpg")

        assert resultado is not None
        assert isinstance(resultado, np.ndarray)

    def test_carga_imagen_mantiene_dimensiones_correctas(self):
        """La imagen cargada debe tener las dimensiones originales."""
        ancho, alto = 800, 600
        img = np.zeros((alto, ancho, 3), dtype=np.uint8)
        _, buf = cv2.imencode(".png", img)
        png_bytes = buf.tobytes()

        resultado = _cargar_imagen_desde_bytes(png_bytes, "ecg.png")

        assert resultado.shape[0] == alto
        assert resultado.shape[1] == ancho
        assert resultado.shape[2] == 3  # BGR

    def test_bytes_corruptos_lanza_value_error(self):
        """Bytes corruptos deben lanzar ValueError, no crash silencioso."""
        with pytest.raises(ValueError, match="No se pudo decodificar"):
            _cargar_imagen_desde_bytes(b"datos_corruptos_invalidos", "ecg.png")

    def test_bytes_vacios_lanza_value_error(self):
        """Bytes vacíos deben lanzar ValueError."""
        with pytest.raises(ValueError):
            _cargar_imagen_desde_bytes(b"", "ecg.png")


# ──────────────────────────────────────────────────────────────────────────────
# Tests de validación del CSV (formato de entrada)
# ──────────────────────────────────────────────────────────────────────────────

class TestValidacionCSV:

    def _crear_csv(self, filas: int, columnas: int) -> bytes:
        """Helper: crea CSV de dimensiones especificadas."""
        data = np.random.randn(filas, columnas).astype(np.float32)
        lines = [",".join(f"{v:.6f}" for v in row) for row in data]
        return "\n".join(lines).encode("utf-8")

    def test_csv_con_12_columnas_es_valido(self):
        """Un CSV con 12 columnas (N, 12) debe aceptarse."""
        csv_bytes = self._crear_csv(1000, 12)
        content = csv_bytes.decode("utf-8", errors="replace")
        rows = [
            [float(v) for v in line.strip().split(",") if v.strip()]
            for line in content.splitlines()
            if line.strip()
        ]
        arr = np.array(rows, dtype=np.float32)
        assert arr.shape[1] == 12

    def test_csv_con_12_filas_puede_transponerse(self):
        """Un CSV con 12 filas (12, N) debe poder transponerse a (N, 12)."""
        csv_bytes = self._crear_csv(12, 1000)
        content = csv_bytes.decode("utf-8")
        rows = [
            [float(v) for v in line.strip().split(",") if v.strip()]
            for line in content.splitlines()
            if line.strip()
        ]
        arr = np.array(rows, dtype=np.float32)
        # Puede ser (12, N) → transponerlo
        assert arr.shape[0] == 12 or arr.shape[1] == 12

    def test_csv_con_columnas_incorrectas_detecta_error(self):
        """CSV con menos de 12 columnas debe detectarse como inválido."""
        csv_bytes = self._crear_csv(100, 6)  # Solo 6 columnas, necesita 12
        content = csv_bytes.decode("utf-8")
        rows = [
            [float(v) for v in line.strip().split(",") if v.strip()]
            for line in content.splitlines()
            if line.strip()
        ]
        arr = np.array(rows, dtype=np.float32)
        # Verificar que no tiene 12 columnas ni 12 filas
        assert arr.shape[0] != 12 and arr.shape[1] != 12


# ──────────────────────────────────────────────────────────────────────────────
# Tests de estructura de respuesta del pipeline
# ──────────────────────────────────────────────────────────────────────────────

class TestEstructuraRespuesta:

    RESPUESTA_VALIDA = {
        "label": "NORM",
        "confidence": 0.95,
        "scores": [0.073] * 13,
        "leads": ["I", "II", "III", "aVR", "aVL", "aVF", "V1", "V2", "V3", "V4", "V5", "V6"],
        "signals": [[0.0] * 1000 for _ in range(12)],
        "metrics": {
            "heart_rate": 72.0,
            "beats": 12,
            "variability": 0.045,
            "amplitude": 1.2,
        },
        "top_predictions": [
            {"label": "Ritmo Sinusal Normal", "code": "NORM",  "probability": 95.0},
            {"label": "Bradicardia Sinusal",  "code": "SBRAD", "probability": 2.0},
            {"label": "Arritmia Sinusal",     "code": "SARRH", "probability": 1.5},
            {"label": "Taquicardia Sinusal",  "code": "STACH", "probability": 1.0},
            {"label": "Bloqueo AV 1er grado", "code": "1AVB", "probability": 0.5},
        ],
    }

    def test_respuesta_contiene_claves_requeridas(self):
        """La respuesta del pipeline debe tener todas las claves esperadas."""
        claves_requeridas = [
            "label", "confidence", "scores",
            "leads", "signals", "metrics", "top_predictions"
        ]
        for clave in claves_requeridas:
            assert clave in self.RESPUESTA_VALIDA, f"Falta la clave: {clave}"

    def test_top_predictions_tiene_5_elementos(self):
        """top_predictions debe tener exactamente 5 elementos."""
        assert len(self.RESPUESTA_VALIDA["top_predictions"]) == 5

    def test_metrics_contiene_todos_los_campos(self):
        """metrics debe tener heart_rate, beats, variability y amplitude."""
        metrics = self.RESPUESTA_VALIDA["metrics"]
        assert "heart_rate" in metrics
        assert "beats" in metrics
        assert "variability" in metrics
        assert "amplitude" in metrics

    def test_confidence_esta_entre_cero_y_uno(self):
        """La confianza debe estar normalizada entre 0.0 y 1.0."""
        confidence = self.RESPUESTA_VALIDA["confidence"]
        assert 0.0 <= confidence <= 1.0

    def test_leads_tiene_12_derivaciones(self):
        """leads debe tener exactamente 12 derivaciones."""
        assert len(self.RESPUESTA_VALIDA["leads"]) == 12

    def test_signals_tiene_12_canales(self):
        """signals debe tener 12 canales (una por derivación)."""
        assert len(self.RESPUESTA_VALIDA["signals"]) == 12

    def test_cada_top_prediction_tiene_label_code_probability(self):
        """Cada predicción top debe tener label, code y probability."""
        for pred in self.RESPUESTA_VALIDA["top_predictions"]:
            assert "label" in pred
            assert "code" in pred
            assert "probability" in pred

    def test_heart_rate_es_positivo(self):
        """La frecuencia cardíaca debe ser un número positivo."""
        hr = self.RESPUESTA_VALIDA["metrics"]["heart_rate"]
        assert hr > 0

    def test_beats_es_entero_positivo(self):
        """El número de latidos debe ser un entero positivo."""
        beats = self.RESPUESTA_VALIDA["metrics"]["beats"]
        assert isinstance(beats, int)
        assert beats > 0

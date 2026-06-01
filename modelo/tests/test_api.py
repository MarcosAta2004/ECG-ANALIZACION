"""
Tests unitarios de la API FastAPI del modelo ECG.
Usa TestClient de FastAPI para probar los endpoints sin levantar uvicorn.

Ejecutar:
    cd modelo
    python -m pytest tests/test_api.py -v
"""

import io
import json
import os
import sys
from unittest.mock import MagicMock, patch

import numpy as np
import pytest
from fastapi.testclient import TestClient
from PIL import Image

# Asegurar que el directorio raíz del módulo esté en el path
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))


# ──────────────────────────────────────────────────────────────────────────────
# Fixtures auxiliares
# ──────────────────────────────────────────────────────────────────────────────

def crear_imagen_png_bytes(ancho: int = 800, alto: int = 600) -> bytes:
    """Crea una imagen PNG sintética en memoria."""
    img = Image.new("RGB", (ancho, alto), color=(255, 255, 255))
    buffer = io.BytesIO()
    img.save(buffer, format="PNG")
    return buffer.getvalue()


def crear_csv_12_derivaciones(n_muestras: int = 1000) -> bytes:
    """Crea un CSV válido con 12 columnas (derivaciones) y n_muestras filas."""
    data = np.random.randn(n_muestras, 12).astype(np.float32)
    lines = [",".join(f"{v:.6f}" for v in row) for row in data]
    return "\n".join(lines).encode("utf-8")


# ──────────────────────────────────────────────────────────────────────────────
# Respuesta simulada del pipeline completo
# ──────────────────────────────────────────────────────────────────────────────

MOCK_PIPELINE_RESULT = {
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
        {"label": "Ritmo Sinusal Normal",  "code": "NORM",  "probability": 95.0},
        {"label": "Bradicardia Sinusal",   "code": "SBRAD", "probability": 2.0},
        {"label": "Arritmia Sinusal",      "code": "SARRH", "probability": 1.5},
        {"label": "Taquicardia Sinusal",   "code": "STACH", "probability": 1.0},
        {"label": "Bloqueo AV 1er grado",  "code": "1AVB",  "probability": 0.5},
    ],
}


# ──────────────────────────────────────────────────────────────────────────────
# Fixture del cliente de test con pipeline mockeado
# ──────────────────────────────────────────────────────────────────────────────

@pytest.fixture
def client():
    """
    Cliente de test con todas las funciones del pipeline mockeadas.
    Los tests unitarios no deben depender del modelo TensorFlow ni de imágenes reales.
    """
    with patch("api._ejecutar_pipeline_imagen", return_value=MOCK_PIPELINE_RESULT), \
         patch("api.extraer_ecg_de_pdf", return_value=np.zeros((600, 800, 3), dtype=np.uint8)), \
         patch("api.detectar_region_ecg", side_effect=lambda img: img), \
         patch("api.preparar_para_modelo", return_value=(
             np.zeros((1, 12, 1000), dtype=np.float32),
             [np.zeros(1000) for _ in range(12)]
         )), \
         patch("api.predecir_con_modelo", return_value=(
             "NORM", 0.95,
             np.array([0.95, 0.01, 0.01, 0.01, 0.01, 0.01]),
             [np.zeros(1000) for _ in range(12)]
         )), \
         patch("api.detect_metrics", return_value=(12, 72.0, 0.045, 1.2)):
        from api import app
        yield TestClient(app, raise_server_exceptions=False)


# ──────────────────────────────────────────────────────────────────────────────
# Tests de Health Check
# ──────────────────────────────────────────────────────────────────────────────

class TestHealthCheck:

    def test_health_retorna_status_ok(self, client):
        """GET /health debe retornar status ok."""
        response = client.get("/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "ok"
        assert "model" in data

    def test_health_retorna_nombre_del_modelo(self, client):
        """GET /health debe incluir el nombre del modelo."""
        response = client.get("/health")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data["model"], str)
        assert len(data["model"]) > 0


# ──────────────────────────────────────────────────────────────────────────────
# Tests del endpoint /preview
# ──────────────────────────────────────────────────────────────────────────────

class TestPreview:

    def test_preview_con_imagen_png_valida_retorna_base64(self, client):
        """POST /preview con PNG válido debe retornar imagen en base64."""
        imagen_bytes = crear_imagen_png_bytes(800, 600)
        response = client.post(
            "/preview",
            files={"file": ("ecg_test.png", imagen_bytes, "image/png")},
        )
        assert response.status_code == 200
        data = response.json()
        assert "image" in data
        assert data["image"].startswith("data:image/jpeg;base64,")

    def test_preview_con_formato_invalido_retorna_400(self, client):
        """POST /preview con formato no soportado debe retornar 400."""
        response = client.post(
            "/preview",
            files={"file": ("ecg.txt", b"datos invalidos", "text/plain")},
        )
        assert response.status_code == 400

    def test_preview_con_imagen_jpg_valida(self, client):
        """POST /preview con JPG válido debe funcionar correctamente."""
        img = Image.new("RGB", (600, 400), color=(200, 100, 50))
        buffer = io.BytesIO()
        img.save(buffer, format="JPEG")
        jpg_bytes = buffer.getvalue()

        response = client.post(
            "/preview",
            files={"file": ("ecg_test.jpg", jpg_bytes, "image/jpeg")},
        )
        assert response.status_code == 200
        assert "image" in response.json()


# ──────────────────────────────────────────────────────────────────────────────
# Tests del endpoint /predict
# ──────────────────────────────────────────────────────────────────────────────

class TestPredict:

    def test_predict_con_csv_valido_retorna_estructura_correcta(self, client):
        """POST /predict con CSV de 12 derivaciones debe retornar estructura completa."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = client.post(
            "/predict",
            files={"file": ("ecg.csv", csv_bytes, "text/csv")},
            data={"age": "45", "sex": "1", "weight": "70.0"},
        )
        assert response.status_code == 200
        data = response.json()

        # Verificar estructura de la respuesta
        assert "label" in data
        assert "confidence" in data
        assert "scores" in data
        assert "leads" in data
        assert "signals" in data
        assert "metrics" in data
        assert "top_predictions" in data

    def test_predict_top_predictions_tiene_exactamente_5_elementos(self, client):
        """La respuesta debe contener exactamente 5 predicciones top."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = client.post(
            "/predict",
            files={"file": ("ecg.csv", csv_bytes, "text/csv")},
            data={"age": "50", "sex": "0", "weight": "65.0"},
        )
        assert response.status_code == 200
        top = response.json()["top_predictions"]
        assert len(top) == 5

    def test_predict_metrics_contiene_campos_requeridos(self, client):
        """El campo metrics debe contener heart_rate, beats, variability y amplitude."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = client.post(
            "/predict",
            files={"file": ("ecg.csv", csv_bytes, "text/csv")},
            data={"age": "30", "sex": "1", "weight": "80.0"},
        )
        assert response.status_code == 200
        metrics = response.json()["metrics"]

        assert "heart_rate" in metrics
        assert "beats" in metrics
        assert "variability" in metrics
        assert "amplitude" in metrics

    def test_predict_con_formato_no_soportado_retorna_400(self, client):
        """POST /predict con formato no soportado debe retornar error."""
        response = client.post(
            "/predict",
            files={"file": ("ecg.docx", b"datos", "application/docx")},
            data={"age": "30", "sex": "1", "weight": "70.0"},
        )
        assert response.status_code == 400

    def test_predict_imagen_png_con_pipeline_mockeado(self, client):
        """POST /predict con imagen PNG debe procesar con pipeline y retornar resultado."""
        imagen_bytes = crear_imagen_png_bytes(1200, 800)

        response = client.post(
            "/predict",
            files={"file": ("ecg_real.png", imagen_bytes, "image/png")},
            data={"age": "55", "sex": "1", "weight": "85.0"},
        )
        assert response.status_code == 200
        data = response.json()
        assert data["label"] == "NORM"
        assert 0.0 <= data["confidence"] <= 1.0

    def test_predict_confidence_entre_cero_y_uno(self, client):
        """La confianza del modelo debe estar entre 0.0 y 1.0."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = client.post(
            "/predict",
            files={"file": ("ecg.csv", csv_bytes, "text/csv")},
            data={"age": "40", "sex": "0", "weight": "60.0"},
        )
        assert response.status_code == 200
        confidence = response.json()["confidence"]
        assert 0.0 <= confidence <= 1.0

    def test_predict_leads_tiene_12_derivaciones(self, client):
        """La respuesta debe contener exactamente 12 derivaciones."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = client.post(
            "/predict",
            files={"file": ("ecg.csv", csv_bytes, "text/csv")},
            data={"age": "40", "sex": "0", "weight": "60.0"},
        )
        assert response.status_code == 200
        leads = response.json()["leads"]
        assert len(leads) == 12

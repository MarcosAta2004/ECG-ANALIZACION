"""
Tests de integración REAL del servidor FastAPI.
Requieren que uvicorn esté corriendo: uvicorn api:app --port 8001 --reload

Ejecutar solo estos tests:
    cd modelo
    python -m pytest tests/test_integration.py -v

NOTA: Estos tests se marcan con @pytest.mark.integration
      y se saltan automáticamente si el servidor no está disponible.
"""

import io
import os
import sys

import numpy as np
import pytest
import requests
from PIL import Image

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

# URL del servidor de inferencia (se puede sobreescribir con variable de entorno)
API_URL = os.environ.get("ECG_API_URL", "http://localhost:8001")

# Etiquetas válidas del modelo (nombres descriptivos)
LABELS_VALIDOS = {
    "normal ECG", "first degree AV block", "ventricular premature complex", 
    "atrial fibrillation", "sinus tachycardia", "sinus bradycardia"
}


def servidor_disponible() -> bool:
    """Verifica si el servidor FastAPI está corriendo."""
    try:
        r = requests.get(f"{API_URL}/health", timeout=3)
        return r.status_code == 200
    except Exception:
        return False


# Saltar todos los tests de este módulo si el servidor no está disponible
pytestmark = pytest.mark.skipif(
    not servidor_disponible(),
    reason=f"Servidor FastAPI no disponible en {API_URL}. Inicia: uvicorn api:app --port 8001",
)


def crear_csv_12_derivaciones(n_muestras: int = 1000) -> bytes:
    """CSV sintético de 12 derivaciones con señales senoidales."""
    t = np.linspace(0, 4 * np.pi, n_muestras)
    cols = [np.sin(t + i * 0.5) * 0.5 for i in range(12)]
    data = np.column_stack(cols).astype(np.float32)
    lines = [",".join(f"{v:.6f}" for v in row) for row in data]
    return "\n".join(lines).encode("utf-8")


def crear_imagen_png_bytes(ancho: int = 1200, alto: int = 800) -> bytes:
    """Imagen PNG sintética de ECG (fondo blanco)."""
    img = Image.new("RGB", (ancho, alto), color=(255, 255, 255))
    buffer = io.BytesIO()
    img.save(buffer, format="PNG")
    return buffer.getvalue()


# ──────────────────────────────────────────────────────────────────────────────
# Tests de integración real
# ──────────────────────────────────────────────────────────────────────────────

class TestHealthIntegracion:

    def test_health_retorna_ok(self):
        """El servidor real debe responder con status ok."""
        response = requests.get(f"{API_URL}/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "ok"
        assert "model" in data


class TestPredictIntegracion:

    def test_predict_csv_valido_retorna_label_conocido(self):
        """La predicción con CSV real debe devolver un label del catálogo conocido."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = requests.post(
            f"{API_URL}/predict",
            files={"file": ("ecg_sintetico.csv", csv_bytes, "text/csv")},
            data={"age": "45", "sex": "1", "weight": "70.0"},
            timeout=60,
        )
        assert response.status_code == 200
        data = response.json()

        assert data["label"] in LABELS_VALIDOS, \
            f"Label '{data['label']}' no está en el catálogo de labels válidos."

    def test_predict_scores_suma_aproximadamente_uno(self):
        """La suma de scores del modelo debe ser ≈ 1.0 (distribución de probabilidad)."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = requests.post(
            f"{API_URL}/predict",
            files={"file": ("ecg_scores.csv", csv_bytes, "text/csv")},
            data={"age": "50", "sex": "0", "weight": "65.0"},
            timeout=60,
        )
        assert response.status_code == 200
        scores = response.json()["scores"]
        suma = sum(scores)
        assert abs(suma - 1.0) < 0.05, \
            f"La suma de scores ({suma:.4f}) debería ser ≈ 1.0"

    def test_predict_top_predictions_en_orden_descendente(self):
        """Las predicciones top deben estar ordenadas de mayor a menor probabilidad."""
        csv_bytes = crear_csv_12_derivaciones(1000)

        response = requests.post(
            f"{API_URL}/predict",
            files={"file": ("ecg_orden.csv", csv_bytes, "text/csv")},
            data={"age": "40", "sex": "1", "weight": "75.0"},
            timeout=60,
        )
        assert response.status_code == 200
        top = response.json()["top_predictions"]
        probabilidades = [p["probability"] for p in top]

        for i in range(len(probabilidades) - 1):
            assert probabilidades[i] >= probabilidades[i + 1], \
                f"top_predictions no está ordenado: {probabilidades}"

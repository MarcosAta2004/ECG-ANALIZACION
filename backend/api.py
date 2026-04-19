"""
API REST para el pipeline ECG de detección de arritmias.
Expone los endpoints /predict y /preview para el frontend Laravel.

Uso:
    uvicorn api:app --host 0.0.0.0 --port 8001 --reload
"""

import os
import sys
import tempfile
import base64
import traceback

import cv2
import numpy as np
from fastapi import FastAPI, File, UploadFile, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

# Fijar el directorio de trabajo al directorio del script para que todas las
# rutas relativas del pipeline (modelo, norm_stats, rois_derivaciones) funcionen
# sin importar desde dónde se lance uvicorn.
_BACKEND_DIR = os.path.dirname(os.path.abspath(__file__))
os.chdir(_BACKEND_DIR)
sys.path.insert(0, _BACKEND_DIR)

from pipeline_unificado import (
    extraer_ecg_de_pdf,
    detectar_region_ecg,
    eliminar_cuadricula,
    preprocesar_para_digitalizacion,
    seleccionar_rois,
    digitalizar_todas_derivaciones,
    preparar_para_modelo,
    predecir_con_modelo,
    detect_metrics,
    LEADS_ORDER,
)
import config
from arr_constantes import LABEL_NAMES

app = FastAPI(title="ECG Arritmia API", version="1.0.0")

# Permitir peticiones desde el frontend Laravel (cualquier origen en desarrollo)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ─── Funciones auxiliares ────────────────────────────────────────────────────

def _cargar_imagen_desde_bytes(data: bytes, filename: str) -> np.ndarray:
    """Carga una imagen PNG/JPG desde bytes y la devuelve como array BGR."""
    arr = np.frombuffer(data, dtype=np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    if img is None:
        raise ValueError(f"No se pudo decodificar la imagen '{filename}'")
    return img


def _ejecutar_pipeline_imagen(img: np.ndarray, age: float, sex: int, weight: float) -> dict:
    """
    Ejecuta el pipeline completo a partir de una imagen BGR de OpenCV.
    Devuelve un dict con label, confidence, scores y señales.
    """
    # Preprocesar
    img_sin_grid = eliminar_cuadricula(img)
    img_procesada = preprocesar_para_digitalizacion(img_sin_grid)

    # ROIs (guardados → auto-detección)
    rois = seleccionar_rois(img_procesada, interactivo=False)

    if len(rois) < 12:
        raise RuntimeError(
            f"Solo se detectaron {len(rois)}/12 derivaciones. "
            "Comprueba que el ECG sea de 12 derivaciones y sea legible."
        )

    # Digitalizar
    derivaciones_mv = digitalizar_todas_derivaciones(img_procesada, rois)

    # Preparar tensor para el modelo
    tensor, signals = preparar_para_modelo(derivaciones_mv)

    # Predecir
    label, confidence, probs, signals = predecir_con_modelo(
        tensor, signals, age, sex, weight
    )

    # Métricas clínicas básicas (Lead II si está disponible)
    lead_ii = signals[1] if len(signals) > 1 else signals[0]
    beats, hr, variability, amplitude = detect_metrics(lead_ii)

    # Construir señales para el gráfico (12 canales, 1000 muestras completas)
    chart_signals = [
        [float(v) for v in s[:1000].tolist()] for s in signals
    ]

    # Top-5 predicciones
    top_idx = np.argsort(probs)[::-1][:5]
    top_predictions = [
        {
            "label": LABEL_NAMES.get(config.LABEL_CODES[i], f"Clase {i}"),
            "code": config.LABEL_CODES[i],
            "probability": round(float(probs[i]) * 100, 2),
        }
        for i in top_idx
    ]

    return {
        "label": label,
        "confidence": round(float(confidence), 2),
        "scores": [round(float(p), 6) for p in probs.tolist()],
        "leads": LEADS_ORDER,
        "signals": chart_signals,
        "metrics": {
            "heart_rate": round(float(hr), 1),
            "beats": int(beats),
            "variability": round(float(variability), 4),
            "amplitude": round(float(amplitude), 4),
        },
        "top_predictions": top_predictions,
    }


# ─── Endpoints ───────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok", "model": config.MODEL_NAME}


@app.post("/preview")
async def preview(file: UploadFile = File(...)):
    """
    Devuelve una vista previa en base64 de la primera página del PDF.
    Útil para que el frontend muestre el ECG antes de analizarlo.
    """
    data = await file.read()
    filename = file.filename or "upload"
    ext = os.path.splitext(filename)[1].lower()

    try:
        if ext == ".pdf":
            with tempfile.NamedTemporaryFile(suffix=".pdf", delete=False) as tmp:
                tmp.write(data)
                tmp_path = tmp.name
            try:
                img = extraer_ecg_de_pdf(tmp_path, dpi=150)
            finally:
                os.unlink(tmp_path)
        elif ext in (".png", ".jpg", ".jpeg"):
            img = _cargar_imagen_desde_bytes(data, filename)
        else:
            raise HTTPException(status_code=400, detail="Formato no soportado para preview.")

        # Redimensionar para preview (máx 1200px ancho)
        h, w = img.shape[:2]
        if w > 1200:
            scale = 1200 / w
            img = cv2.resize(img, (1200, int(h * scale)))

        _, buf = cv2.imencode(".jpg", img, [cv2.IMWRITE_JPEG_QUALITY, 80])
        b64 = base64.b64encode(buf).decode("utf-8")
        return {"image": f"data:image/jpeg;base64,{b64}"}

    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/predict")
async def predict(
    file: UploadFile = File(...),
    age: float = Form(...),
    sex: int = Form(...),
    weight: float = Form(...),
):
    """
    Pipeline completo: imagen/PDF → digitalización → predicción.

    Parámetros de formulario:
        file   : archivo ECG (PNG, JPG, JPEG o PDF)
        age    : edad del paciente en años
        sex    : 0 = Femenino, 1 = Masculino
        weight : peso en kg
    """
    data = await file.read()
    filename = file.filename or "upload"
    ext = os.path.splitext(filename)[1].lower()

    try:
        # ── Cargar imagen ────────────────────────────────────────────────────
        if ext == ".pdf":
            with tempfile.NamedTemporaryFile(suffix=".pdf", delete=False) as tmp:
                tmp.write(data)
                tmp_path = tmp.name
            try:
                img = extraer_ecg_de_pdf(tmp_path, dpi=300)
                img = detectar_region_ecg(img)
            finally:
                os.unlink(tmp_path)

        elif ext in (".png", ".jpg", ".jpeg"):
            img = _cargar_imagen_desde_bytes(data, filename)
            img = detectar_region_ecg(img)

        elif ext in (".csv", ".txt"):
            # CSV: columnas = derivaciones (12), filas = muestras (>=1000)
            # El frontend ya envía el CSV en el formato correcto
            import io
            content = data.decode("utf-8", errors="replace")
            rows = [
                [float(v) for v in line.strip().split(",") if v.strip()]
                for line in content.splitlines()
                if line.strip()
            ]
            if not rows:
                raise ValueError("El archivo CSV está vacío.")

            arr = np.array(rows, dtype=np.float32)
            # Aceptar (N, 12) o (12, N)
            if arr.shape[1] == 12:
                signals_raw = [arr[:, i] for i in range(12)]
            elif arr.shape[0] == 12:
                signals_raw = [arr[i, :] for i in range(12)]
            else:
                raise ValueError(
                    f"El CSV debe tener 12 columnas (derivaciones). "
                    f"Shape detectado: {arr.shape}"
                )

            derivaciones_mv = {LEADS_ORDER[i]: signals_raw[i] for i in range(12)}
            tensor, signals = preparar_para_modelo(derivaciones_mv)
            label, confidence, probs, signals = predecir_con_modelo(
                tensor, signals, age, sex, weight
            )

            lead_ii = signals[1] if len(signals) > 1 else signals[0]
            beats, hr, variability, amplitude = detect_metrics(lead_ii)
            chart_signals = [[float(v) for v in s[:500].tolist()] for s in signals]
            top_idx = np.argsort(probs)[::-1][:5]
            top_predictions = [
                {
                    "label": LABEL_NAMES.get(config.LABEL_CODES[i], f"Clase {i}"),
                    "code": config.LABEL_CODES[i],
                    "probability": round(float(probs[i]) * 100, 2),
                }
                for i in top_idx
            ]

            return {
                "label": label,
                "confidence": round(float(confidence), 2),
                "scores": [round(float(p), 6) for p in probs.tolist()],
                "leads": LEADS_ORDER,
                "signals": chart_signals,
                "metrics": {
                    "heart_rate": round(float(hr), 1),
                    "beats": int(beats),
                    "variability": round(float(variability), 4),
                    "amplitude": round(float(amplitude), 4),
                },
                "top_predictions": top_predictions,
            }

        else:
            raise HTTPException(
                status_code=400,
                detail=f"Formato '{ext}' no soportado. Usa PNG, JPG, JPEG, PDF, CSV o TXT.",
            )

        # ── Pipeline para imagen/PDF ─────────────────────────────────────────
        result = _ejecutar_pipeline_imagen(img, age, sex, weight)
        return result

    except HTTPException:
        raise
    except RuntimeError as e:
        raise HTTPException(status_code=422, detail=str(e))
    except Exception as e:
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=f"Error interno: {str(e)}")


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api:app", host="0.0.0.0", port=8001, reload=True)

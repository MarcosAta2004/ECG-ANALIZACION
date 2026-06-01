# 02_config.py - parametros de inferencia para modelo ECG-only.
from pathlib import Path

from arr_constantes import LABEL_CODES as ARR_LABEL_CODES, LABEL_NAMES as ARR_LABEL_NAMES

# Parametros de la red. Deben coincidir exactamente con el entrenamiento.
SAMPLING_RATE = 100
DURATION = 5
INPUT_SHAPE = (500, 12)
NUM_CLASSES = len(ARR_LABEL_CODES)
MODELS_DIR = Path(__file__).resolve().parent
MODEL_CANDIDATES = (
    "modelo_arritmias_Fina_v4.keras",
    "modelo_arritmias_5seg_V2.keras",
)


def resolver_modelo():
    for nombre in MODEL_CANDIDATES:
        candidato = MODELS_DIR / nombre
        if candidato.exists():
            return candidato
    return MODELS_DIR / MODEL_CANDIDATES[0]


MODEL_NAME = str(resolver_modelo())

LABEL_CODE_TO_INDEX = {code: idx for idx, code in enumerate(ARR_LABEL_CODES)}
LABEL_NAMES = ARR_LABEL_NAMES
LABEL_CODES = ARR_LABEL_CODES
LABEL_CODE_TO_NAME = ARR_LABEL_NAMES

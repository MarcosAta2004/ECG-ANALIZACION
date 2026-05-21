# 02_config.py - parametros de inferencia para modelo ECG-only.
from arr_constantes import LABEL_CODES as ARR_LABEL_CODES, LABEL_NAMES as ARR_LABEL_NAMES

# Parametros de la red. Deben coincidir exactamente con el entrenamiento.
SAMPLING_RATE = 100
DURATION = 5
INPUT_SHAPE = (1000, 12)
NUM_CLASSES = len(ARR_LABEL_CODES)
MODEL_NAME = "modelo_arritmias_5seg.keras"

LABEL_CODE_TO_INDEX = {code: idx for idx, code in enumerate(ARR_LABEL_CODES)}
LABEL_NAMES = ARR_LABEL_NAMES
LABEL_CODES = ARR_LABEL_CODES
LABEL_CODE_TO_NAME = ARR_LABEL_NAMES

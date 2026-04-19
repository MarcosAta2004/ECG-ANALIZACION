# config.py — versión inferencia (sin rutas al dataset PTB-XL)
from arr_constantes import LABEL_CODES as ARR_LABEL_CODES, LABEL_NAMES as ARR_LABEL_NAMES

# Parámetros de la red (deben coincidir exactamente con el entrenamiento)
SAMPLING_RATE  = 100        # Hz
DURATION       = 10         # segundos
INPUT_SHAPE    = (1000, 12) # 1000 muestras, 12 derivaciones
META_SHAPE     = (3,)       # [age_norm, sex, weight_norm]
NUM_CLASSES    = len(ARR_LABEL_CODES)
MODEL_NAME     = 'modelo_arritmias_modular.keras'

LABEL_CODE_TO_INDEX = {code: idx for idx, code in enumerate(ARR_LABEL_CODES)}
LABEL_NAMES         = ARR_LABEL_NAMES
LABEL_CODES         = ARR_LABEL_CODES
LABEL_CODE_TO_NAME  = ARR_LABEL_NAMES

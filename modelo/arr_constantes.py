# 01_arr_constantes.py - clases activas del modelo de inferencia ECG-only.
# El orden debe coincidir exactamente con las salidas de modelo_arritmias_5seg.keras.

TRAINABLE_RHYTHM_CODES = [
    "AFIB",
    "PVC",
    "STACH",
    "SBRAD",
    "1AVB",
]

LABEL_CODES = ["NORM", *TRAINABLE_RHYTHM_CODES]

LABEL_NAMES = {
    "NORM": "normal ECG",
    "AFIB": "atrial fibrillation",
    "PVC": "ventricular premature complex",
    "STACH": "sinus tachycardia",
    "SBRAD": "sinus bradycardia",
    "1AVB": "first degree AV block",
}

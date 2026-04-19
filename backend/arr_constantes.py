# arr_constantes.py — versión inferencia (sin dependencia al dataset PTB-XL)
# Las descripciones están tomadas directamente de scp_statements.csv del entrenamiento.

TRAINABLE_RHYTHM_CODES = [
    "1AVB",
    "WPW",
    "PVC",
    "PAC",
    "AFIB",
    "STACH",
    "SARRH",
    "SBRAD",
    "SVARR",
    "BIGU",
    "AFLT",
    "PSVT",
]

LABEL_CODES = ["NORM", *TRAINABLE_RHYTHM_CODES]

LABEL_NAMES = {
    "NORM":  "normal ECG",
    "1AVB":  "first degree AV block",
    "WPW":   "Wolf-Parkinson-White syndrome",
    "PVC":   "ventricular premature complex",
    "PAC":   "atrial premature complex",
    "AFIB":  "atrial fibrillation",
    "STACH": "sinus tachycardia",
    "SARRH": "sinus arrhythmia",
    "SBRAD": "sinus bradycardia",
    "SVARR": "supraventricular arrhythmia",
    "BIGU":  "bigeminal pattern (unknown origin, SV or Ventricular)",
    "AFLT":  "atrial flutter",
    "PSVT":  "paroxysmal supraventricular tachycardia",
}

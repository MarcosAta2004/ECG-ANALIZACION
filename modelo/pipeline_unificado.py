"""
Pipeline Unificado para Extracción, Digitalización y Predicción de ECG
Integra los 3 scripts en un flujo coherente y optimizado
"""

import os
import json
import cv2
import numpy as np
import matplotlib.pyplot as plt
from pdf2image import convert_from_path
from scipy.ndimage import gaussian_filter1d, median_filter
from scipy.signal import find_peaks
import tensorflow as tf
from tensorflow.keras.models import load_model
from modelo import focal_loss

# ==================== CONFIGURACIÓN ====================
import config
from arr_constantes import LABEL_NAMES

# Configuración de archivos
PDF_PATH = '20250522-030159-2205250301.pdf'
OUTPUT_IMAGE = 'ecg_procesado.png'
OUTPUT_CSV = 'ecg_digitalizado.csv'
ROI_CONFIG_FILE = 'rois_derivaciones.json'
MODELO_PATH = config.MODEL_NAME

# Parámetros de procesamiento
DPI = 300
ECG_MM_PER_S = 25
ECG_MM_PER_MV = 10
NORMALIZE_SIGNALS = True  # Importante: normalizar para el modelo

# Orden estándar de derivaciones
LEADS_ORDER = ['I', 'II', 'III', 'aVR', 'aVL', 'aVF', 'V1', 'V2', 'V3', 'V4', 'V5', 'V6']

# ==================== PASO 1: EXTRACCIÓN DEL PDF ====================

def extraer_ecg_de_pdf(pdf_path, dpi=300):
    """Extrae imagen del PDF con alta resolución"""
    print(f"[1/7] Extrayendo ECG del PDF (DPI={dpi})...")
    imagenes = convert_from_path(pdf_path, dpi=dpi)
    if len(imagenes) == 0:
        raise Exception("No se pudo extraer ninguna imagen del PDF")
    img_pil = imagenes[0]
    img = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
    print(f"   ✓ Imagen extraída: {img.shape[1]}x{img.shape[0]} píxeles")
    return img


def detectar_region_ecg(img):
    """Detecta y recorta la región que contiene el ECG"""
    print("[2/7] Detectando región del ECG...")
    gris = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    _, binaria = cv2.threshold(gris, 200, 255, cv2.THRESH_BINARY_INV)
    
    proyeccion_h = np.sum(binaria, axis=1)
    proyeccion_v = np.sum(binaria, axis=0)
    
    umbral_h = np.max(proyeccion_h) * 0.05
    umbral_v = np.max(proyeccion_v) * 0.05
    
    filas = np.where(proyeccion_h > umbral_h)[0]
    cols = np.where(proyeccion_v > umbral_v)[0]
    
    if len(filas) == 0 or len(cols) == 0:
        return img
    
    y_ini, y_fin = filas[0], filas[-1]
    x_ini, x_fin = cols[0], cols[-1]
    
    margen = 20
    y_ini = max(0, y_ini - margen)
    y_fin = min(img.shape[0], y_fin + margen)
    x_ini = max(0, x_ini - margen)
    x_fin = min(img.shape[1], x_fin + margen)
    
    ecg_recortado = img[y_ini:y_fin, x_ini:x_fin]
    print(f"   ✓ Región ECG: {ecg_recortado.shape[1]}x{ecg_recortado.shape[0]} píxeles")
    return ecg_recortado


# ==================== PASO 2: PREPROCESAMIENTO ====================

def eliminar_cuadricula(img):
    """Elimina la cuadrícula preservando el trazo"""
    print("[3/7] Eliminando cuadrícula...")
    gris = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY) if len(img.shape) == 3 else img.copy()
    
    # Detectar líneas de cuadrícula
    kernel_h = cv2.getStructuringElement(cv2.MORPH_RECT, (15, 1))
    lineas_h = cv2.morphologyEx(gris, cv2.MORPH_OPEN, kernel_h, iterations=1)
    
    kernel_v = cv2.getStructuringElement(cv2.MORPH_RECT, (1, 15))
    lineas_v = cv2.morphologyEx(gris, cv2.MORPH_OPEN, kernel_v, iterations=1)
    
    cuadricula = cv2.add(lineas_h, lineas_v)
    _, mask_cuadricula = cv2.threshold(cuadricula, 180, 255, cv2.THRESH_BINARY)
    
    # Restar cuadrícula
    gris_float = gris.astype(np.float32)
    cuadricula_float = (mask_cuadricula.astype(np.float32) / 255.0) * 30
    gris_sin_grid = np.clip(gris_float - cuadricula_float, 0, 255).astype(np.uint8)
    
    print(f"   ✓ Cuadrícula eliminada")
    return gris_sin_grid


def preprocesar_para_digitalizacion(img):
    """Preprocesa la imagen para digitalización óptima"""
    clahe = cv2.createCLAHE(clipLimit=1.5, tileGridSize=(16, 16))
    img_contraste = clahe.apply(img)
    
    img_suave = cv2.GaussianBlur(img_contraste, (3, 3), 0.5)
    
    _, img_binaria = cv2.threshold(img_suave, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    
    if np.mean(img_binaria) < 127:
        img_binaria = cv2.bitwise_not(img_binaria)
    
    return img_binaria


# ==================== PASO 3: DETECCIÓN / SELECCIÓN DE ROIs ====================

# Layouts estándar de 12 derivaciones.
# Clave: n_cols detectadas
# 'leads_por_columna': lista de listas, una por columna, de arriba a abajo
_LAYOUTS_CONOCIDOS = {
    2: {
        'n_filas': 6,
        'leads_por_columna': [
            ['I', 'II', 'III', 'aVR', 'aVL', 'aVF'],   # columna izquierda
            ['V1', 'V2', 'V3', 'V4', 'V5', 'V6'],       # columna derecha
        ],
    },
    3: {
        'n_filas': 4,
        'leads_por_columna': [
            ['I',   'II',  'III', 'aVR'],
            ['aVL', 'aVF', 'V1', 'V2'],
            ['V3',  'V4',  'V5', 'V6'],
        ],
    },
    4: {
        'n_filas': 3,
        'leads_por_columna': [
            ['I',   'II',  'III'],
            ['aVR', 'aVL', 'aVF'],
            ['V1',  'V2',  'V3'],
            ['V4',  'V5',  'V6'],
        ],
    },
}


def cargar_rois_guardados():
    """Carga ROIs previamente guardados"""
    if not os.path.exists(ROI_CONFIG_FILE):
        return {}
    try:
        with open(ROI_CONFIG_FILE, 'r', encoding='utf-8') as f:
            data = json.load(f)
        return data.get('rois', {})
    except Exception:
        return {}


def guardar_rois(rois):
    """Guarda ROIs para uso futuro"""
    try:
        with open(ROI_CONFIG_FILE, 'w', encoding='utf-8') as f:
            json.dump({'rois': rois}, f, indent=2)
        print(f"   ✓ ROIs guardados en {ROI_CONFIG_FILE}")
    except Exception:
        print("    No se pudieron guardar los ROIs")


def _detectar_columnas(binaria, ancho, min_fraccion=0.10):
    """
    Detecta columnas de señal. Filtra columnas estrechas (calibración, etiquetas).

    Analiza solo el 70 % superior de la imagen para evitar que un rhythm-strip
    de ancho completo en la parte inferior fusione las dos columnas de leads.
    """
    alto = binaria.shape[0]
    analisis = binaria[:int(alto * 0.70), :]   # excluir franja inferior (rhythm strip)
    proj_v = gaussian_filter1d(np.sum(analisis, axis=0).astype(float), sigma=25)
    umbral = np.max(proj_v) * 0.15
    activo = proj_v > umbral
    cambios = np.diff(activo.astype(int))
    inicios = list(np.where(cambios == 1)[0])
    fines   = list(np.where(cambios == -1)[0])
    if activo[0]:  inicios.insert(0, 0)
    if activo[-1]: fines.append(ancho - 1)
    min_px = ancho * min_fraccion
    return [(ini, fin) for ini, fin in zip(inicios, fines) if (fin - ini) > min_px]


def _encontrar_limites_ecg(binaria, bandas_v):
    """
    Detecta filas de inicio y fin del área ECG (excluye encabezado y pie).

    Para el INICIO busca la línea separadora horizontal gruesa (>35% de píxeles
    de trazo en toda la fila). Para el FIN busca la primera caída de señal en
    el tercio inferior de la imagen.
    """
    alto, ancho = binaria.shape

    # ── INICIO: línea separadora del encabezado ──────────────────────────────
    zona_header = int(alto * 0.45)
    proj_full   = np.sum(binaria[:zona_header, :], axis=1) / (ancho * 255.0)

    seps = np.where(proj_full > 0.35)[0]           # línea gruesa continua
    if len(seps) > 0:
        ecg_start = int(seps[-1]) + 5
    else:
        texto = np.where(proj_full > 0.06)[0]      # fin del texto del header
        ecg_start = int(texto[-1]) + 20 if len(texto) > 0 else int(alto * 0.22)

    # ── FIN: pie de página ───────────────────────────────────────────────────
    proj_col = np.zeros(alto, dtype=float)
    for x0, x1 in bandas_v:
        proj_col += np.sum(binaria[:, x0:x1], axis=1)
    proj_col /= len(bandas_v)
    suave = gaussian_filter1d(proj_col, sigma=15)
    umbral = np.max(suave) * 0.08
    activo = suave > umbral

    zona_inf = int(alto * 0.65)
    caidas   = np.where(np.diff(activo[zona_inf:].astype(int)) == -1)[0]
    ecg_end  = min(zona_inf + int(caidas[0]) + 5, alto - 1) if len(caidas) > 0 else int(alto * 0.95)

    return ecg_start, ecg_end


def _gaps_entre_derivaciones(col_binaria, n_leads, min_gap_px=3):
    """
    Encuentra los n_leads-1 separadores entre derivaciones buscando filas
    con CERO píxeles de trazo (gaps reales en la imagen binarizada).

    No usa suavizado — trabaja directamente con la imagen binaria, lo que
    permite detectar gaps de tan sólo 3-5 píxeles sin riesgo de borrarlos.

    Retorna lista de posiciones (centro de cada gap), ordenada.
    """
    alto = col_binaria.shape[0]

    # Fila con al menos 1 píxel de trazo = tiene señal
    tiene_señal = np.any(col_binaria > 0, axis=1)

    # Encontrar regiones consecutivas sin señal
    gaps = []
    i = 0
    while i < alto:
        if not tiene_señal[i]:
            j = i
            while j < alto and not tiene_señal[j]:
                j += 1
            largo = j - i
            if largo >= min_gap_px:
                gaps.append({'pos': (i + j) // 2, 'largo': largo, 'ini': i, 'fin': j})
            i = j
        else:
            i += 1

    if len(gaps) == 0:
        # Sin gaps visibles: división equitativa
        return [int(k * alto / n_leads) for k in range(1, n_leads)]

    if len(gaps) >= n_leads - 1:
        # Elegir los n_leads-1 gaps más largos (más significativos)
        gaps_ord = sorted(gaps, key=lambda g: -g['largo'])[:n_leads - 1]
        return sorted(g['pos'] for g in gaps_ord)

    # Menos gaps que separadores necesarios: usar los que hay + división local
    seps_hallados = sorted(g['pos'] for g in gaps)
    # Completar con división equitativa de los segmentos sin gap
    puntos = [0] + seps_hallados + [alto]
    seps_extra = []
    for k in range(len(puntos) - 1):
        segmento = puntos[k + 1] - puntos[k]
        faltantes = (n_leads // (len(puntos) - 1)) - 1
        for f in range(1, faltantes + 1):
            seps_extra.append(puntos[k] + int(f * segmento / (faltantes + 1)))
    todos = sorted(set(seps_hallados + seps_extra))
    return todos[:n_leads - 1]


def _extent_senal(region, trazo_oscuro=True):
    """
    Devuelve (sig_top, baseline, sig_bot) en coordenadas relativas a `region`.

    sig_top / sig_bot: primera y última fila que contiene al menos un píxel de
    trazo. Esto captura el rango COMPLETO de la señal, incluyendo los picos QRS
    más altos (que son breves pero deben estar dentro del ROI para digitalizarse
    correctamente).

    baseline: mediana de la posición Y del trazo columna a columna — representa
    la línea isoeléctrica estable, independiente de los picos.

    Por qué NO usar percentiles aquí:
    - Un pico QRS dura ~10 de 200 columnas (~5 % del tiempo).
    - Con P5/P95 ese pico queda fuera del rango → el ROI lo recorta.
    - Necesitamos el rango absoluto (min/max de filas con señal), no estadístico.
    """
    alto, ancho = region.shape[:2]

    if trazo_oscuro:
        tiene_trazo = np.any(region < 128, axis=1)
    else:
        tiene_trazo = np.any(region > 0, axis=1)

    filas = np.where(tiene_trazo)[0]
    if len(filas) == 0:
        return 0, alto // 2, alto

    sig_top = int(filas[0])
    sig_bot = int(filas[-1])

    # Baseline: mediana de la posición del trazo por columna (robusto a picos)
    pos_y = []
    for c in range(ancho):
        col = region[:, c]
        px = np.where(col < 128)[0] if trazo_oscuro else np.where(col > 0)[0]
        if len(px) > 0:
            pos_y.append(float(np.median(px)))
    baseline = int(np.median(pos_y)) if pos_y else (sig_top + sig_bot) // 2

    return sig_top, baseline, sig_bot


def _bandas_senal_aware(col_img, separadores, alto, margen_pct=0.05, margen_min_px=5):
    """
    Calcula las bandas ROI ajustadas al rango REAL (completo) de la señal.

    Por cada derivación:
      1. Extrae la zona nominal (entre separadores vecinos).
      2. Detecta el rango absoluto de señal: primera y última fila con píxel de
         trazo → captura todos los picos QRS sin excluirlos.
      3. Añade margen = max(margen_min_px, 5 % × altura_señal).
      4. Limita estrictamente a [separador_arriba, separador_abajo]:
         como los separadores son centros de filas vacías, esta frontera
         garantiza que no hay solapamiento por construcción.
    """
    n = len(separadores) + 1
    puntos = [0] + list(separadores) + [alto]

    bandas = []
    for i in range(n):
        zona_ini = puntos[i]
        zona_fin = puntos[i + 1]

        if zona_fin <= zona_ini:
            bandas.append((max(0, zona_ini), min(alto, zona_fin)))
            continue

        zona_img = col_img[zona_ini:zona_fin, :]

        # Rango completo (trazo=255 en col_img invertida)
        sig_top_rel, _, sig_bot_rel = _extent_senal(zona_img, trazo_oscuro=False)
        senal_top = zona_ini + sig_top_rel
        senal_bot = zona_ini + sig_bot_rel
        altura    = max(1, senal_bot - senal_top + 1)

        margen = max(margen_min_px, int(altura * margen_pct))

        # Límites duros: centros de gaps vacíos → sin solapamiento
        y0 = max(puntos[i],     senal_top - margen)
        y1 = min(puntos[i + 1], senal_bot + margen)

        # Garantía: el ROI siempre cubre la señal real completa
        y0 = min(y0, senal_top)
        y1 = max(y1, senal_bot)

        bandas.append((max(0, y0), min(alto, y1)))

    return bandas


def _detectar_inicio_footer(img_gray):
    """
    Busca el inicio del pie de página (footer) del ECG desde abajo hacia arriba.

    Sube desde la última fila con píxeles hasta encontrar la primera fila vacía
    (el espacio entre el footer y la última derivación). Devuelve esa fila como
    límite inferior seguro para aVF / V6.

    Garantía: nunca devuelve un valor por encima del 70 % de la imagen.
    """
    alto, ancho = img_gray.shape[:2]
    # Umbral relativo al ancho: 0.3 % de los píxeles de la fila → vacío
    # Adapta a cualquier resolución (300 DPI ~7 px, 150 DPI ~4 px, 72 DPI ~2 px)
    umbral_vacio = max(3, ancho * 0.003)

    # Invertir para que el trazo sea brillante
    inv = cv2.bitwise_not(img_gray)
    densidad = np.sum(inv > 30, axis=1).astype(float)
    suave = gaussian_filter1d(densidad, sigma=3)

    # 1. Bajar hasta encontrar la última fila con contenido (saltar margen en blanco)
    limite_inf = alto - 1
    while limite_inf > 0 and suave[limite_inf] < umbral_vacio:
        limite_inf -= 1

    if limite_inf < int(alto * 0.70):
        return int(alto * 0.92)   # fallback conservador

    # 2. Subir desde ahí hasta encontrar la primera fila vacía → tope del footer
    footer_top = limite_inf
    while footer_top > 0 and suave[footer_top] > umbral_vacio:
        footer_top -= 1

    # Añadir un pequeño buffer y garantizar que no corta demasiado arriba
    return max(int(alto * 0.70), footer_top - 5)


def _encontrar_mejor_frontera(img_gray, x, w, y_candidato, margen_busqueda=80):
    """
    Dado un y candidato a frontera entre dos derivaciones adyacentes, busca
    la fila con MÍNIMA densidad de trazo en la ventana
    [y_candidato - margen_busqueda, y_candidato + margen_busqueda].

    Esto encuentra el gap real entre las dos señales aunque los picos de la
    derivación inferior se hayan "fugado" por encima del límite nominal:
    el mínimo estará en el espacio vacío que siempre existe entre la señal
    de arriba (I) y los picos de abajo (II), incluso si ese espacio vacío
    está por encima de la frontera originalmente calculada.

    Devuelve la coordenada y absoluta del mejor límite.
    """
    alto_img = img_gray.shape[0]
    y_ini = max(0, y_candidato - margen_busqueda)
    y_fin = min(alto_img, y_candidato + margen_busqueda)

    if y_fin <= y_ini:
        return y_candidato

    region = img_gray[y_ini:y_fin, x:x + w]
    densidad = np.sum(region < 128, axis=1).astype(float)

    # Suavizado para evitar elegir un mínimo ruidoso de 1 px
    suave = gaussian_filter1d(densidad, sigma=5)
    min_row = int(np.argmin(suave))

    return y_ini + min_row


def auto_detectar_rois(img):
    """
    Detecta automáticamente las ROIs de las 12 derivaciones.

    Algoritmo:
      1. Detectar columnas de señal (proyección vertical suavizada).
      2. Detectar límites del área ECG (encabezado + pie de página).
      3. Para cada columna buscar gaps REALES (filas con cero píxeles de trazo).
      4. Ajustar cada banda a la extensión real de la señal + margen dinámico.

    Layouts soportados: 2×6 | 3×4 | 4×3
    """
    gris = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY) if len(img.shape) == 3 else img.copy()
    alto, ancho = gris.shape

    # Trazo = 255, fondo = 0
    inv = cv2.bitwise_not(gris)
    _, binaria = cv2.threshold(inv, 30, 255, cv2.THRESH_BINARY)

    # 1. Columnas
    bandas_v = _detectar_columnas(binaria, ancho, min_fraccion=0.10)
    n_cols = len(bandas_v)
    if n_cols not in _LAYOUTS_CONOCIDOS:
        print(f"   {n_cols} columnas — layout no soportado {list(_LAYOUTS_CONOCIDOS.keys())}")
        return None

    layout = _LAYOUTS_CONOCIDOS[n_cols]
    leads_por_columna = layout['leads_por_columna']
    print(f"   Layout detectado: {layout['n_filas']}×{n_cols}")

    # 2. Límites del área ECG
    ecg_start, ecg_end = _encontrar_limites_ecg(binaria, bandas_v)
    print(f"   Área ECG: y=[{ecg_start}, {ecg_end}]  ({ecg_end - ecg_start}px útiles)")

    # 3. Analizar cada columna independientemente
    rois = {}
    for col_idx, (x_ini, x_fin) in enumerate(bandas_v):
        col_leads = leads_por_columna[col_idx]
        n = len(col_leads)

        col_img   = binaria[ecg_start:ecg_end, x_ini:x_fin]
        alto_col  = col_img.shape[0]

        # Gaps reales entre derivaciones
        seps = _gaps_entre_derivaciones(col_img, n)

        # Bandas ajustadas a la extensión real de señal + margen dinámico
        bandas_h = _bandas_senal_aware(col_img, seps, alto_col)

        for i, (y0r, y1r) in enumerate(bandas_h):
            y_ini = ecg_start + y0r
            y_fin = ecg_start + y1r
            rois[col_leads[i]] = [x_ini, y_ini, x_fin - x_ini, y_fin - y_ini]
            print(f"   {col_leads[i]:>4}: y=[{y_ini},{y_fin}]  h={y_fin - y_ini}")

    return rois


def visualizar_rois_detectados(img, rois, path='rois_detectados.png'):
    """Guarda una imagen con las ROIs dibujadas para verificación visual."""
    vis = cv2.cvtColor(img, cv2.COLOR_GRAY2BGR) if len(img.shape) == 2 else img.copy()
    colores = plt.cm.tab20.colors  # 20 colores distintos

    for i, (nombre, roi) in enumerate(rois.items()):
        x, y, w, h = roi
        color = tuple(int(c * 255) for c in colores[i % 20][:3])[::-1]  # BGR
        cv2.rectangle(vis, (x, y), (x + w, y + h), color, 3)
        cv2.putText(vis, nombre, (x + 5, y + 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 2)

    # Escalar para visualización (máx 1200px de ancho)
    escala = min(1.0, 1200 / vis.shape[1])
    if escala < 1.0:
        vis = cv2.resize(vis, None, fx=escala, fy=escala)

    cv2.imwrite(path, vis)
    print(f"   ✓ Vista de ROIs guardada en: {path}")


def _ajustar_rois_sin_solapamiento(img_bin, rois_base, leads_order,
                                    margen_pct=0.05, margen_min_px=5):
    """
    Ajusta todos los ROIs de forma conjunta para que:

      1. Cubran la señal real completa de cada derivación, incluyendo picos
         que físicamente se "fugan" por encima del límite nominal del ROI base.
      2. NO se superpongan entre derivaciones adyacentes.
      3. Usen padding conservador: max(margen_min_px, margen_pct × rango_señal).

    Algoritmo (por columna de leads):
      a) Calcula la baseline de cada lead dentro de su ROI base (estable aunque
         los picos desborden, porque la isoelectrica sí está dentro del ROI).
      b) Entre cada par adyacente busca la frontera óptima con
         _encontrar_mejor_frontera: fila con MÍNIMA densidad de trazo cerca del
         midpoint entre las dos baselines. Esto coloca la frontera en el espacio
         vacío real entre las señales, aunque los picos de la inferior hayan
         cruzado el límite nominal.
      c) Usa esas fronteras "reales" para recortar regiones por lead y calcular
         la extensión final de la señal con margen.

    Parámetros:
        img_bin      : imagen del ECG (se normaliza internamente)
        rois_base    : dict nombre → [x, y, w, h]  (ROIs de referencia)
        leads_order  : lista ordenada de nombres de derivaciones
        margen_pct   : fracción de la altura de señal para el margen (5 % por defecto)
        margen_min_px: margen mínimo en píxeles cuando la señal es casi plana
    """
    # Normalizar imagen: fondo claro, trazo oscuro (<128)
    if len(img_bin.shape) == 3:
        img_gray = cv2.cvtColor(img_bin, cv2.COLOR_BGR2GRAY)
    else:
        img_gray = img_bin.copy()
    if np.mean(img_gray) < 127:
        img_gray = cv2.bitwise_not(img_gray)

    img_alto = img_gray.shape[0]

    # ── Detectar inicio del footer (límite inferior para aVF y V6) ───────────
    #   Sube desde el fondo de la imagen hasta encontrar el espacio vacío
    #   entre la última derivación y la línea de parámetros técnicos del ECG.
    _ecg_end = _detectar_inicio_footer(img_gray)

    # ── Paso 1: extent y baseline por lead dentro del ROI base ──────────────
    #   La isoeléctrica SIEMPRE está en el ROI base aunque los picos se fuguen,
    #   por eso baseline y extent se calculan únicamente sobre el ROI base.
    extent_base = {}   # nombre → (abs_top, abs_bot) dentro del ROI base
    baseline    = {}   # nombre → y absoluto de línea isoeléctrica
    for nombre in leads_order:
        if nombre not in rois_base:
            continue
        x, y, w, h = [int(v) for v in rois_base[nombre]]
        y_clip = min(img_alto, y + h)
        if y_clip <= y:
            mid = y + h // 2
            extent_base[nombre] = (mid, mid)
            baseline[nombre]    = mid
            continue
        region = img_gray[y:y_clip, x:x + w]
        sig_top_rel, base_rel, sig_bot_rel = _extent_senal(region, trazo_oscuro=True)
        extent_base[nombre] = (y + sig_top_rel, y + sig_bot_rel)
        baseline[nombre]    = y + base_rel

    # ── Paso 2: agrupar leads por columna (misma franja horizontal X) ────────
    def solapa_x(n1, n2):
        x1, _, w1, _ = [int(v) for v in rois_base[n1]]
        x2, _, w2, _ = [int(v) for v in rois_base[n2]]
        return not (x1 + w1 <= x2 or x2 + w2 <= x1)

    presentes = [n for n in leads_order if n in baseline]
    columnas, asignado = [], set()
    for n1 in presentes:
        if n1 in asignado:
            continue
        col = [n1]
        asignado.add(n1)
        for n2 in presentes:
            if n2 not in asignado and solapa_x(n1, n2):
                col.append(n2)
                asignado.add(n2)
        col.sort(key=lambda n: baseline[n])   # orden top → bottom por baseline
        columnas.append(col)

    # ── Paso 3: fronteras reales entre pares INTERNOS de la columna ──────────
    #
    #   Para cada par adyacente (Narr, Nabj) → arriba y abajo respectivamente:
    #     1. Midpoint entre sus baselines → candidato inicial de frontera.
    #     2. _encontrar_mejor_frontera busca la fila de MÍNIMA densidad
    #        cerca de ese midpoint → localiza el gap físico real.
    #     3. Sanity-clamp estricto: la frontera DEBE estar entre las dos
    #        baselines (baseline_Narr ≤ frontera ≤ baseline_Nabj). Esto
    #        garantiza que la frontera nunca invade el cuerpo de ninguna señal.
    #
    #   Los extremos de la columna (primer lead arriba / último lead abajo)
    #   no usan este mecanismo: se limitan a la extensión del ROI base + margen
    #   para no incluir encabezados ni pies de página del ECG.
    rois_final = {}
    for col in columnas:
        n = len(col)

        # Fronteras internas
        fronteras = []
        for i in range(n - 1):
            n_arr = col[i]        # lead superior
            n_abj = col[i + 1]   # lead inferior
            mid   = (baseline[n_arr] + baseline[n_abj]) // 2
            x_col = int(rois_base[n_arr][0])
            w_col = int(rois_base[n_arr][2])

            frontera = _encontrar_mejor_frontera(img_gray, x_col, w_col, mid,
                                                 margen_busqueda=100)

            # Sanity-clamp: la frontera debe estar entre las dos baselines
            clamp_lo = baseline[n_arr]   # nunca por encima de la baseline superior
            clamp_hi = baseline[n_abj]   # nunca por debajo de la baseline inferior
            frontera = max(clamp_lo, min(clamp_hi, frontera))

            fronteras.append(int(frontera))

        # ROIs finales
        for i, nombre in enumerate(col):
            x, y_b, w, h_b = [int(v) for v in rois_base[nombre]]
            sig_top, sig_bot = extent_base[nombre]
            altura_senal = max(1, sig_bot - sig_top + 1)
            margen = max(margen_min_px, int(altura_senal * margen_pct))

            # ── Borde superior ──────────────────────────────────────────────
            if i == 0:
                # Primer lead: usa extensión del ROI base con margen pequeño
                y0 = max(0, sig_top - margen)
                y0 = min(y0, sig_top)   # garantía: cubre la señal real
            else:
                # Lead interno: su borde superior ES la frontera con el lead de arriba
                # NO se aplica garantía de sig_top para evitar incluir overflow de vecino
                y0 = fronteras[i - 1]

            # ── Borde inferior ──────────────────────────────────────────────
            if i == n - 1:
                # Último lead: usa extensión del ROI base con margen pequeño
                y1 = min(img_alto, sig_bot + margen)
                y1 = max(y1, sig_bot)   # garantía: cubre la señal real
                # Doble capa de protección contra footer / tira de ritmo:
                #  1. No superar el fondo del ROI base calibrado manualmente.
                #  2. No superar el fin del área ECG detectado desde la imagen.
                y1 = min(y1, y_b + h_b)
                y1 = min(y1, _ecg_end)
            else:
                # Lead interno: su borde inferior ES la frontera con el lead de abajo
                # NO se aplica garantía de sig_bot para evitar incluir overflow de vecino
                y1 = fronteras[i]

            rois_final[nombre] = [x, max(0, y0), w, max(1, y1 - y0)]

    return rois_final


def _validar_sin_solapamiento(rois, leads_order):
    """
    Comprueba que ningún par de ROIs de la misma columna se solapa.
    Imprime advertencias (no lanza excepción) para no detener el pipeline.
    """
    presentes = [n for n in leads_order if n in rois]
    sin_solapamiento = True
    for i in range(len(presentes)):
        for j in range(i + 1, len(presentes)):
            n1, n2 = presentes[i], presentes[j]
            x1, y1, w1, h1 = rois[n1]
            x2, y2, w2, h2 = rois[n2]
            # Solo verificar pares con solapamiento horizontal (misma columna)
            if x1 + w1 <= x2 or x2 + w2 <= x1:
                continue
            # ¿Se solapan verticalmente?
            y1_bot = y1 + h1
            y2_bot = y2 + h2
            if y1_bot > y2 and y2_bot > y1:
                overlap = min(y1_bot, y2_bot) - max(y1, y2)
                print(f"  ⚠  Solapamiento {n1}/{n2}: {overlap} px")
                sin_solapamiento = False
    if sin_solapamiento:
        print("  ✓ Validación: ningún ROI se solapa")


def seleccionar_rois(img, interactivo=False):
    """
    Obtiene las ROIs de las 12 derivaciones en este orden de preferencia:
      1. ROIs guardados en rois_derivaciones.json  (calibrados manualmente)
         → Se ajustan dinámicamente a la señal real del ECG actual.
      2. Detección automática (gap-based + señal-aware)
      3. Selección manual interactiva (sólo si interactivo=True)

    En modo NO interactivo (pipeline automático) nunca pide input al usuario.
    """
    print("\n" + "="*70)
    print("[4/7] DETECCIÓN DE DERIVACIONES")
    print("="*70)

    # ── 1. ROIs guardados ajustados a la señal real (sin solapamiento) ──────
    rois_guardados = cargar_rois_guardados()
    if all(n in rois_guardados for n in LEADS_ORDER):
        print("  ✓ ROIs base cargados. Ajustando a señal real (sin solapamiento)...")
        rois_ajustados = _ajustar_rois_sin_solapamiento(img, rois_guardados, LEADS_ORDER)

        for nombre in LEADS_ORDER:
            if nombre not in rois_ajustados:
                continue
            x0, y0, w0, h0 = [int(v) for v in rois_guardados[nombre]]
            xa, ya, wa, ha  = rois_ajustados[nombre]
            if ha != h0 or ya != y0:
                print(f"    {nombre:>4}: y {y0}→{ya}  h {h0}→{ha}")

        _validar_sin_solapamiento(rois_ajustados, LEADS_ORDER)
        visualizar_rois_detectados(img, rois_ajustados)
        return rois_ajustados

    # ── 2. Detección automática ──────────────────────────────────────────────
    print("  Sin ROIs guardados. Intentando detección automática...")
    rois_auto = auto_detectar_rois(img)

    if rois_auto and all(n in rois_auto for n in LEADS_ORDER):
        print("  ✓ Detección automática exitosa.")
        visualizar_rois_detectados(img, rois_auto)

        if interactivo:
            print("  Revisa 'rois_detectados.png' para verificar que sean correctas.")
            respuesta = input("  ¿Son correctas? (s=guardar y continuar / n=selección manual): ").strip().lower()
            if respuesta == 'n':
                # caer a selección manual abajo
                rois_auto = None
            else:
                guardar_rois(rois_auto)
                return rois_auto
        else:
            # Pipeline automático: guardar y continuar
            guardar_rois(rois_auto)
            return rois_auto

    # ── 3. Selección manual (sólo en modo interactivo) ───────────────────────
    if not interactivo:
        raise RuntimeError(
            "No hay ROIs guardados y la detección automática falló.\n"
            "Ejecuta en modo interactivo o selecciona los ROIs manualmente:\n"
            "  python pipeline_unificado.py --manual"
        )

    print("\nSelección manual:")
    print("  - Arrastra para marcar cada derivación, luego ENTER/ESPACIO")
    print("  - Sin dibujar + ENTER → usa el ROI guardado (si existe)")
    print("="*70 + "\n")

    rois = {}
    for nombre in LEADS_ORDER:
        print(f"→ Selecciona derivación: {nombre}")
        try:
            cv2.namedWindow(f"Selecciona {nombre}", cv2.WINDOW_NORMAL)
            roi = cv2.selectROI(f"Selecciona {nombre}", img,
                                showCrosshair=True, fromCenter=False)
            cv2.destroyWindow(f"Selecciona {nombre}")

            if roi[2] == 0 or roi[3] == 0:
                if nombre in rois_guardados:
                    rois[nombre] = rois_guardados[nombre]
                    print(f"  ✓ Usando ROI guardado para {nombre}")
                else:
                    print(f"  ⚠ Sin ROI para {nombre}, se omitirá.")
            else:
                rois[nombre] = [int(roi[0]), int(roi[1]), int(roi[2]), int(roi[3])]
                print(f"  ✓ {nombre} seleccionado")
        except Exception:
            if nombre in rois_guardados:
                rois[nombre] = rois_guardados[nombre]

    guardar_rois(rois)
    return rois


# Alias para compatibilidad con test_digitalizacion.py
def seleccionar_rois_interactivo(img):
    return seleccionar_rois(img, interactivo=True)


# ==================== PASO 4: DIGITALIZACIÓN ====================

def _segmentar_columna_pip(px_oscuros, gap_min=3):
    """Divide indices de pixeles en segmentos conectados (gap >= gap_min)."""
    if len(px_oscuros) == 0:
        return []
    segs, ini = [], 0
    for k in range(1, len(px_oscuros)):
        if px_oscuros[k] - px_oscuros[k - 1] >= gap_min:
            segs.append(px_oscuros[ini:k])
            ini = k
    segs.append(px_oscuros[ini:])
    return segs


def _digitalizar_region_robusta_pip(region, umbral=128, margen_borde=12):
    """
    Extrae la posicion vertical del trazo ECG columna a columna,
    robusta ante pixeles invasores de derivaciones adyacentes.

    SOLUCION - PROXIMIDAD A BASELINE + EXCLUSION DE ZONA FRONTERA:
      1a pasada: para cada columna elige el grupo mas cercano al centro
                 geometrico del ROI => estimacion inicial del trazo propio.
      Baseline:  mediana robusta (percentil 10-90) de la 1a pasada.
      2a pasada: para cada columna:
         a) Descarta segmentos dentro de 'margen_borde' px del borde del ROI
            cuyo centro este lejos de la baseline (>= alto/4). Esos pixeles
            son casi siempre de la derivacion vecina que se "fuga".
         b) De los segmentos validos elige el mas cercano a la baseline.
         c) Si NO hay segmentos validos (solo hay invasores en el borde),
            usa la ultima posicion valida en lugar de adoptar el invasor.
    """
    alto, ancho = region.shape
    centro_roi = alto / 2.0
    umbral_lejos = alto / 4.0   # distancia maxima a baseline para aceptar borde

    # Primera pasada — estimacion inicial con centro geometrico
    pos_primera = []
    for col in range(ancho):
        px = np.where(region[:, col] < umbral)[0]
        segs = _segmentar_columna_pip(px)
        if not segs:
            pos_primera.append(None)
            continue
        centros = [float(np.median(s)) for s in segs]
        pos_primera.append(min(centros, key=lambda c: abs(c - centro_roi)))

    vals = [v for v in pos_primera if v is not None]
    if not vals:
        return np.full(ancho, centro_roi)

    # Baseline robusta (percentil 10-90)
    arr = np.array(vals)
    p10, p90 = np.percentile(arr, [10, 90])
    mascara = (arr >= p10) & (arr <= p90)
    baseline = float(np.median(arr[mascara])) if mascara.any() else float(np.median(arr))

    # Segunda pasada — con exclusion de zona frontera
    senal  = np.zeros(ancho)
    ultimo = baseline
    for col in range(ancho):
        px = np.where(region[:, col] < umbral)[0]
        segs = _segmentar_columna_pip(px)
        if not segs:
            senal[col] = ultimo
            continue

        # Filtrar segmentos sospechosos: en zona de borde Y lejos de la baseline
        segs_validos = []
        for s in segs:
            c = float(np.median(s))
            en_borde = (c < margen_borde) or (c > alto - margen_borde)
            dist_bl  = abs(c - baseline)
            # Aceptar si NO está en borde, o si está cerca de la baseline
            if not en_borde or dist_bl < umbral_lejos:
                segs_validos.append(s)

        if not segs_validos:
            # Todos los segmentos son invasores de borde → mantener posicion anterior
            senal[col] = ultimo
            continue

        centros = [float(np.median(s)) for s in segs_validos]
        mejor   = min(centros, key=lambda c: abs(c - baseline))
        senal[col] = mejor
        ultimo = mejor

    return senal


def digitalizar_roi(img, roi):
    """
    Digitaliza una region ROI extrayendo la posicion vertical del trazo.
    Usa _digitalizar_region_robusta_pip para descartar pixeles invasores
    de derivaciones adyacentes mediante seleccion por proximidad a baseline.
    """
    x, y, w, h = roi
    region = img[y:y+h, x:x+w]

    if np.mean(region) < 127:
        region = cv2.bitwise_not(region)

    señal = _digitalizar_region_robusta_pip(region)
    señal = h - señal                          # invertir Y
    señal = median_filter(señal, size=3)       # suavizar ruido
    return señal


def convertir_a_milivoltios(señal, dpi, mm_per_mv=10):
    """Convierte píxeles a mV"""
    mm_por_pixel = 25.4 / dpi
    pixeles_por_mv = mm_per_mv / mm_por_pixel
    
    linea_base = np.median(señal)
    señal_centrada = señal - linea_base
    señal_mv = señal_centrada / pixeles_por_mv
    
    return señal_mv


def digitalizar_todas_derivaciones(img, rois):
    """Digitaliza todas las derivaciones"""
    print("\n[5/7] Digitalizando derivaciones...")
    
    derivaciones = {}
    derivaciones_mv = {}
    
    for nombre in LEADS_ORDER:
        if nombre not in rois:
            print(f"   {nombre}: No disponible")
            continue
        
        print(f"   → {nombre}...", end=" ")
        señal = digitalizar_roi(img, rois[nombre])
        derivaciones[nombre] = señal
        
        # Convertir a mV
        señal_mv = convertir_a_milivoltios(señal, DPI, ECG_MM_PER_MV)
        derivaciones_mv[nombre] = señal_mv
        
        print(f"✓ {len(señal)} muestras, std={np.std(señal_mv):.3f} mV")
    
    return derivaciones_mv


# ==================== PASO 5: PREPARAR PARA EL MODELO ====================

def preparar_para_modelo(derivaciones_mv):
    """Prepara las señales para el modelo (1000 muestras, 12 leads)"""
    print("\n[6/7] Preparando datos para el modelo...")
    
    signals = []
    
    for lead in LEADS_ORDER:
        if lead not in derivaciones_mv:
            raise ValueError(f"Falta la derivación {lead}")
        
        values = derivaciones_mv[lead]
        
        if len(values) < 10:
            raise ValueError(f"Derivación {lead} tiene muy pocos datos")
        
        # Resamplear a 1000 puntos
        x_old = np.linspace(0, 1, len(values))
        x_new = np.linspace(0, 1, 1000)
        resampled = np.interp(x_new, x_old, values)
        
        # Normalizar si está activado
        if NORMALIZE_SIGNALS:
            normed = (resampled - np.mean(resampled)) / (np.std(resampled) + 1e-8)
        else:
            normed = resampled
        
        signals.append(normed)
        print(f"   ✓ {lead}: {len(values)} → 1000 muestras")
    
    # Construir tensor (1, 1000, 12)
    arr = np.stack(signals, axis=1)
    tensor = arr.reshape(1, 1000, arr.shape[1])
    
    print(f"   ✓ Tensor creado: {tensor.shape}")
    return tensor, signals


# ==================== PASO 6: PREDICCIÓN ====================

def detect_metrics(signal_curve):
    """Detecta métricas clínicas básicas"""
    diff = np.diff(signal_curve)
    peaks = np.where((diff[:-1] > 0) & (diff[1:] < 0))[0]
    beats = len(peaks)
    hr = beats * 6  # aprox para ventana de 10 segundos
    variability = np.std(np.diff(peaks)) if beats > 1 else 0.0
    amplitude = signal_curve.max() - signal_curve.min()
    return beats, hr, variability, amplitude


def pedir_metadata_paciente():
    """Solicita los datos clínicos del paciente por consola."""
    print("\n" + "="*70)
    print("DATOS DEL PACIENTE (necesarios para el modelo)")
    print("="*70)
    while True:
        try:
            age = float(input("  Edad (años): "))
            break
        except ValueError:
            print("  Ingresa un número válido.")
    while True:
        try:
            sex_str = input("  Sexo (0=Femenino, 1=Masculino): ").strip()
            sex = int(sex_str)
            if sex not in (0, 1):
                raise ValueError
            break
        except ValueError:
            print("  Ingresa 0 o 1.")
    while True:
        try:
            weight = float(input("  Peso (kg): "))
            break
        except ValueError:
            print("  Ingresa un número válido.")
    return age, sex, weight


def normalizar_metadata_paciente(age, sex, weight):
    """Normaliza metadata con las stats del entrenamiento."""
    with open('norm_stats.json') as f:
        stats = json.load(f)
    age_norm    = (age    - stats['age_mean']) / stats['age_std']
    weight_norm = (weight - stats['w_mean'])   / stats['w_std']
    return np.array([[age_norm, sex, weight_norm]], dtype='float32')


def _leer_pesos_keras3(h5_path):
    """
    Lee los pesos guardados en formato Keras 3 (layers/<name>/vars/N)
    y devuelve un dict: layer_name → lista de arrays numpy.
    Maneja el caso especial del Bidirectional LSTM.
    """
    import h5py
    pesos = {}
    with h5py.File(h5_path, 'r') as f:
        if 'layers' not in f:
            return pesos
        for layer_name in f['layers']:
            grp = f['layers'][layer_name]
            # Caso estándar: layers/<name>/vars/0, 1, ...
            if 'vars' in grp:
                vars_grp = grp['vars']
                idx = 0
                arr_list = []
                while str(idx) in vars_grp:
                    arr_list.append(vars_grp[str(idx)][:])
                    idx += 1
                if arr_list:
                    pesos[layer_name] = arr_list
            # Caso Bidirectional: sublayers forward_layer / backward_layer
            sub_keys = [k for k in grp.keys() if k in ('forward_layer', 'backward_layer')]
            if sub_keys:
                combined = []
                for sub in ('forward_layer', 'backward_layer'):
                    if sub not in grp:
                        continue
                    cell_grp = grp[sub].get('cell', grp[sub])
                    if 'vars' in cell_grp:
                        vars_grp = cell_grp['vars']
                        idx = 0
                        while str(idx) in vars_grp:
                            combined.append(vars_grp[str(idx)][:])
                            idx += 1
                if combined:
                    pesos[layer_name] = combined
    return pesos


def _cargar_modelo():
    """
    Carga el modelo compatible con Keras 2 (TF 2.15) aunque los pesos
    hayan sido guardados con Keras 3.

    Reconstruye la arquitectura desde modelo.py y asigna los pesos
    leyéndolos directamente del H5 interno del .keras zip.

    El mapeo usa primero el nombre de la capa; si hay discrepancia de forma
    (Keras 2 y Keras 3 a veces numeran las Dense de forma distinta),
    busca en el pool restante de pesos una entrada con formas compatibles.
    """
    import zipfile, tempfile, os as _os
    from modelo import construir_modelo

    if not _os.path.exists(MODELO_PATH):
        raise FileNotFoundError(
            f"No existe el modelo '{MODELO_PATH}'. "
            "Copia modelo_arritmias_Fina_v4.keras dentro de la carpeta modelo."
        )

    model = construir_modelo()

    with zipfile.ZipFile(MODELO_PATH, 'r') as zf:
        with tempfile.NamedTemporaryFile(suffix='.h5', delete=False) as tmp:
            tmp.write(zf.read('model.weights.h5'))
            tmp_path = tmp.name

    try:
        pesos_k3 = _leer_pesos_keras3(tmp_path)
    finally:
        _os.unlink(tmp_path)

    capas_con_pesos = [l for l in model.layers if l.weights]

    # Pool de pesos aún no asignados (para fallback por forma)
    pool = dict(pesos_k3)
    asignadas, omitidas = 0, 0

    for layer in capas_con_pesos:
        nombre = layer.name
        esperados = len(layer.weights)
        formas_modelo = [tuple(w.shape) for w in layer.weights]

        # 1. Intento por nombre exacto con forma correcta
        if nombre in pool:
            arr_list = pool[nombre]
            formas_k3 = [tuple(a.shape) for a in arr_list]
            if formas_k3 == formas_modelo:
                layer.set_weights(arr_list)
                del pool[nombre]
                asignadas += 1
                continue

        # 2. Fallback: buscar en el pool una entrada con formas idénticas
        candidato = None
        for clave, arr_list in pool.items():
            if [tuple(a.shape) for a in arr_list] == formas_modelo:
                candidato = clave
                break

        if candidato is not None:
            print(f"   >> {nombre} <- '{candidato}' (reasignado por forma)")
            layer.set_weights(pool[candidato])
            del pool[candidato]
            asignadas += 1
        else:
            print(f"   ⚠  Sin pesos compatibles para: {nombre} {formas_modelo}")
            omitidas += 1

    print(f"   OK Pesos cargados (Keras 3->2): {asignadas} capas OK, {omitidas} omitidas")
    if omitidas > 0:
        raise RuntimeError(
            f"{omitidas} capas sin pesos. "
            "Verifica que modelo.py coincide con la arquitectura guardada."
        )
    return model


def predecir_con_modelo(tensor, signals, age=None, sex=None, weight=None):
    """Realiza la prediccion con el modelo ECG-only."""
    print("\n[7/7] Ejecutando predicción con el modelo...")

    model = _cargar_modelo()

    probs = model.predict(tensor, verbose=0)[0]

    idx = int(np.argmax(probs))
    label = LABEL_NAMES.get(config.LABEL_CODES[idx], "Desconocido")
    confidence = probs[idx] * 100

    print(f"\n{'='*70}")
    print(f"RESULTADO DEL DIAGNÓSTICO")
    print(f"{'='*70}")
    print(f"Diagnóstico: {label}")
    print(f"Confianza:   {confidence:.2f}%")
    print(f"{'='*70}\n")

    return label, confidence, probs, signals


# ==================== PASO 7: VISUALIZACIÓN ====================

def guardar_reporte_completo(signals, label, confidence, probs, derivaciones_mv):
    """Genera y guarda el reporte visual completo"""
    print("Generando reporte visual...")
    
    fig = plt.figure(figsize=(18, 12))
    gs = fig.add_gridspec(4, 4, width_ratios=[1, 1, 1, 1.2], height_ratios=[1, 1, 1, 1])
    
    # Panel de derivaciones (3x4 grid)
    for idx, nombre in enumerate(LEADS_ORDER):
        fila = idx // 4
        col = idx % 4
        
        if col == 3 and fila < 3:  # Última columna reservada para otros paneles
            continue
        
        ax = fig.add_subplot(gs[fila, col])
        
        if nombre in derivaciones_mv:
            señal = signals[idx]
            ax.plot(señal, color='black', linewidth=0.8)
            ax.set_title(f'{nombre}', fontsize=10, fontweight='bold')
            ax.set_xticks([])
            ax.set_yticks([])
            ax.grid(True, alpha=0.3)
        else:
            ax.text(0.5, 0.5, 'N/A', ha='center', va='center')
            ax.set_title(f'{nombre}', fontsize=10)
    
    # Panel de probabilidades (top 5)
    ax_bar = fig.add_subplot(gs[0:2, 3])
    top_idx = np.argsort(probs)[::-1][:5]
    labels = [LABEL_NAMES.get(config.LABEL_CODES[i], f"Clase {i}") for i in top_idx]
    values = probs[top_idx] * 100
    colors = ['green' if 'normal' in lbl.lower() else 'red' for lbl in labels]
    ax_bar.barh(labels[::-1], values[::-1], color=colors[::-1])
    ax_bar.set_xlabel("Probabilidad (%)", fontsize=10)
    ax_bar.set_title("Top 5 Predicciones", fontsize=12, fontweight='bold')
    ax_bar.set_xlim(0, 100)
    ax_bar.grid(axis='x', linestyle='--', alpha=0.3)
    
    # Panel de métricas
    ax_metrics = fig.add_subplot(gs[2, 3])
    ax_metrics.axis('off')
    beats, hr, variability, amplitude = detect_metrics(signals[1])  # Usar lead II
    texto_metricas = (
        "MÉTRICAS CLÍNICAS (Lead II)\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"Frecuencia Cardiaca: {hr:.0f} lpm\n"
        f"Variabilidad: {variability:.2f}\n"
        f"Latidos detectados: {beats}\n"
        f"Amplitud QRS: {amplitude:.2f}"
    )
    ax_metrics.text(0.1, 0.5, texto_metricas, fontsize=9, family='monospace',
                    bbox=dict(facecolor='lightcyan', alpha=0.8, boxstyle='round'))
    
    # Panel de diagnóstico
    ax_diag = fig.add_subplot(gs[3, 3])
    ax_diag.axis('off')
    texto_diag = (
        f"DIAGNÓSTICO AUTOMATIZADO\n"
        f"━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"Resultado: {label}\n"
        f"Confianza: {confidence:.1f}%\n"
        f"Modelo: {os.path.basename(MODELO_PATH)}\n\n"
        " IMPORTANTE:\n"
        "Este análisis es orientativo.\n"
        "Requiere validación médica."
    )
    color_fondo = 'lightgreen' if 'normal' in label.lower() else 'lightyellow'
    ax_diag.text(0.1, 0.5, texto_diag, fontsize=9, family='monospace',
                 bbox=dict(facecolor=color_fondo, alpha=0.8, boxstyle='round'))
    
    fig.suptitle(f'REPORTE ECG - {label}', fontsize=16, fontweight='bold')
    plt.tight_layout()
    plt.savefig('reporte_ecg_completo.png', dpi=150, bbox_inches='tight')
    plt.close()
    
    print("✓ Reporte guardado: reporte_ecg_completo.png")


def exportar_csv(derivaciones_mv):
    """Exporta las señales a CSV"""
    print("Exportando a CSV...")
    
    max_len = max(len(derivaciones_mv[n]) for n in LEADS_ORDER if n in derivaciones_mv)
    
    with open(OUTPUT_CSV, 'w', encoding='utf-8') as f:
        f.write('Muestra,' + ','.join(LEADS_ORDER) + '\n')
        
        for i in range(max_len):
            fila = f"{i}"
            for nombre in LEADS_ORDER:
                if nombre in derivaciones_mv and i < len(derivaciones_mv[nombre]):
                    fila += f",{derivaciones_mv[nombre][i]:.6f}"
                else:
                    fila += ","
            f.write(fila + '\n')
    
    print(f"✓ CSV guardado: {OUTPUT_CSV}")


# ==================== PIPELINE PRINCIPAL ====================

def ejecutar_pipeline_completo(args=None):
    """Ejecuta el pipeline completo de extracción a predicción"""
    import argparse
    if args is None:
        parser = argparse.ArgumentParser(description='Pipeline ECG: PDF → Arritmia')
        parser.add_argument('--pdf',    type=str,  default=PDF_PATH,
                            help='Ruta al archivo PDF del ECG')
        parser.add_argument('--manual', action='store_true',
                            help='Fuerza selección manual de ROIs (útil para nuevo formato de ECG)')
        args = parser.parse_args()

    pdf_path = args.pdf

    print("\n" + "="*70)
    print("PIPELINE UNIFICADO: EXTRACCIÓN → DIGITALIZACIÓN → PREDICCIÓN")
    print("="*70)
    print(f"  PDF: {pdf_path}")
    print(f"  Modo ROI: {'manual' if args.manual else 'automático (usa guardados si existen)'}\n")

    try:
        # 1. Extraer imagen del PDF
        img_original = extraer_ecg_de_pdf(pdf_path, dpi=DPI)
        
        # 2. Detectar y recortar región del ECG
        ecg_recortado = detectar_region_ecg(img_original)
        
        # 3. Preprocesar
        img_sin_grid = eliminar_cuadricula(ecg_recortado)
        img_procesada = preprocesar_para_digitalizacion(img_sin_grid)
        
        # Guardar imagen procesada
        cv2.imwrite(OUTPUT_IMAGE, img_procesada)
        print(f"✓ Imagen procesada guardada: {OUTPUT_IMAGE}\n")
        
        # 4. Seleccionar ROIs (guardados → auto-detección → error)
        rois = seleccionar_rois(img_procesada, interactivo=args.manual)

        if len(rois) < 12:
            print(f"\n  ADVERTENCIA: Solo {len(rois)}/12 derivaciones seleccionadas")
            if not args.manual:
                raise RuntimeError("Faltan derivaciones. Ejecuta con --manual para seleccionarlas.")
            respuesta = input("¿Continuar de todos modos? (s/n): ")
            if respuesta.lower() != 's':
                return
        
        # 5. Digitalizar
        derivaciones_mv = digitalizar_todas_derivaciones(img_procesada, rois)
        
        # 6. Exportar CSV
        exportar_csv(derivaciones_mv)
        
        # 7. Preparar para modelo
        tensor, signals = preparar_para_modelo(derivaciones_mv)

        # 7.5 Pedir datos del paciente
        age, sex, weight = pedir_metadata_paciente()

        # 8. Predecir
        label, confidence, probs, signals = predecir_con_modelo(tensor, signals, age, sex, weight)
        
        # 9. Generar reporte
        guardar_reporte_completo(signals, label, confidence, probs, derivaciones_mv)
        
        # 10. Resumen final
        print("\n" + "="*70)
        print("PIPELINE COMPLETADO EXITOSAMENTE")
        print("="*70)
        print(f"✓ Derivaciones procesadas: {len(derivaciones_mv)}/12")
        print(f"✓ Diagnóstico: {label} ({confidence:.1f}%)")
        print(f"✓ Archivos generados:")
        print(f"  - {OUTPUT_IMAGE}")
        print(f"  - {OUTPUT_CSV}")
        print(f"  - reporte_ecg_completo.png")
        print("="*70 + "\n")
        
    except Exception as e:
        print(f"\n❌ Error en el pipeline: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    ejecutar_pipeline_completo()

# Análisis del Módulo PrediccionSeeder

## 📋 Descripción General
El `PrediccionSeeder` es un seeder que carga **63 registros de predicciones de ritmos cardíacos** en la base de datos usando el patrón `updateOrCreate`. Esto significa que si el registro ya existe, lo actualiza; si no, lo crea.

---

## 🎯 Flujo de Operación

```
Para cada predicción en el array $predicciones:
  1. Busca si existe un registro con prediccion_id = X
  2. Si existe → ACTUALIZA todos los campos
  3. Si no existe → CREA un nuevo registro con todos los campos
```

---

## 📊 Campos y su Propósito

### Campos de Búsqueda (Condición WHERE)
| Campo | Propósito | Valor |
|-------|----------|-------|
| `prediccion_id` | **Identificador único** - Se usa para encontrar si el registro ya existe | 1 a 63 |

### Campos de Actualización/Creación
| Campo | Tipo | Propósito | Valor | ✅ Cambio |
|-------|------|----------|-------|----------|
| `imagen_id` | INT | ID de la imagen ECG asociada | 1 a 63 | ✅ ASIGNADO |
| `ritmo_id` | INT | ID del tipo de ritmo cardíaco detectado | 1, 4, 6, 7, 9 | ✅ ASIGNADO |
| `probabilidad` | DECIMAL | Confianza de la predicción (0-1) | 0.8497 a 0.9658 | ✅ ASIGNADO |
| `tiempo_ms` | INT/NULL | Tiempo de procesamiento en milisegundos | `null` | ✅ ESTABLECIDO A NULL |
| `top_predicciones` | JSON | Array con top 2 predicciones alternativas | `[{code, label, probability}]` | ✅ ASIGNADO |
| `label_detectado` | STRING | Nombre legible del ritmo detectado | "Taquicardia Sinusal", "Fibrilacion Auricular", etc | ✅ ASIGNADO |
| `label_code` | STRING | Código corto del ritmo | STACH, AFIB, NORM, PVC, SBRAD | ✅ ASIGNADO |
| `tipo` | INT | Clasificación: 1=Normal, 2=Arritmia | 1 o 2 | ✅ ASIGNADO |
| `estado` | INT | Estado del registro (1=Activo) | `1` | ✅ FIJADO A 1 |
| `created_at` | TIMESTAMP | Fecha de creación | Fecha de la predicción | ✅ ASIGNADO |
| `updated_at` | TIMESTAMP | Fecha de última actualización | Misma que created_at | ✅ ASIGNADO |

---

## 🔍 Análisis de Datos

### Distribución de Ritmos Detectados:
- **NORM (Ritmo Sinusal Normal)**: ~51% - Función: `ritmo_id = 1`, `tipo = 1`
- **AFIB (Fibrilación Auricular)**: ~35% - Función: `ritmo_id = 6`, `tipo = 2`
- **SBRAD (Bradicardia Sinusal)**: ~8% - Función: `ritmo_id = 9`, `tipo = 2`
- **PVC (Complejo Ventricular Prematuro)**: ~5% - Función: `ritmo_id = 4`, `tipo = 2`
- **STACH (Taquicardia Sinusal)**: ~1% - Función: `ritmo_id = 7`, `tipo = 2`

### Rango de Probabilidades:
- **Mínima**: 0.8497 (84.97%)
- **Máxima**: 0.9658 (96.58%)
- **Promedio**: ~89% de confianza

### Período de Datos:
- **Desde**: 2026-04-06 08:10:00
- **Hasta**: 2026-05-05 09:15:00
- **Duración**: ~29 días

---

## ✅ Verificación de Cambios Establecidos

### Campo por Campo:

#### ✅ `imagen_id`
```php
'imagen_id' => $p['imagen_id']  // Incrementa de 1 a 63
```
**Estado**: ACTUALIZADO | **Rango**: 1-63

#### ✅ `ritmo_id`
```php
'ritmo_id' => $p['ritmo_id']  // Del array de predicciones
```
**Estado**: ACTUALIZADO | **Valores posibles**: [1, 4, 6, 7, 9]

#### ✅ `probabilidad`
```php
'probabilidad' => $p['probabilidad']  // Del array de predicciones
```
**Estado**: ACTUALIZADO | **Rango**: 0.8497-0.9658

#### ✅ `tiempo_ms`
```php
'tiempo_ms' => null  // Explícitamente NULL
```
**Estado**: ESTABLECIDO A NULL | **Indica**: Tiempo no registrado

#### ✅ `top_predicciones`
```php
'top_predicciones' => $p['top_predicciones']  // JSON con 2 opciones principales
```
**Estado**: ACTUALIZADO | **Formato**: 
```json
[
  {"code": "STACH", "label": "Taquicardia Sinusal", "probability": 94.86},
  {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.14}
]
```

#### ✅ `label_detectado`
```php
'label_detectado' => $p['label_detectado']  // Nombre legible del ritmo
```
**Estado**: ACTUALIZADO | **Ejemplos**: 
- "Taquicardia Sinusal"
- "Fibrilacion Auricular"
- "Ritmo Sinusal Normal"
- "Complejo ventricular prematuro"
- "Bradicardia Sinusal"

#### ✅ `label_code`
```php
'label_code' => $p['label_code']  // Código abreviado
```
**Estado**: ACTUALIZADO | **Valores**: [STACH, AFIB, NORM, PVC, SBRAD]

#### ✅ `tipo`
```php
'tipo' => $p['tipo']  // Clasificación de tipo
```
**Estado**: ACTUALIZADO | **Rango**: [1=Normal, 2=Arritmia]

#### ✅ `estado`
```php
'estado' => 1  // Explícitamente 1 (Activo)
```
**Estado**: FIJADO A 1 | **Significa**: Todos los registros comienzan ACTIVOS

#### ✅ `created_at`
```php
'created_at' => $p['created_at']  // Fecha del array
```
**Estado**: ACTUALIZADO | **Rango**: 2026-04-06 a 2026-05-05

#### ✅ `updated_at`
```php
'updated_at' => $p['created_at']  // Igual a created_at
```
**Estado**: ACTUALIZADO | **Nota**: Se iguala a `created_at` inicialmente

---

## 🚀 Cómo Validar que los Cambios se Establecieron

### 1. **Verificación en Base de Datos**:
```sql
-- Ver todos los registros insertados
SELECT * FROM predicciones;

-- Contar total de registros
SELECT COUNT(*) as total FROM predicciones;
-- Esperado: 63

-- Ver distribución de ritmos
SELECT ritmo_id, label_code, COUNT(*) as cantidad 
FROM predicciones 
GROUP BY ritmo_id, label_code;

-- Ver rango de probabilidades
SELECT 
  MIN(probabilidad) as min_prob,
  MAX(probabilidad) as max_prob,
  AVG(probabilidad) as promedio
FROM predicciones;
```

### 2. **Verificación en PHP/Laravel**:
```php
// Contar registros creados
$total = Prediccion::count();
echo "Total de predicciones: $total"; // Esperado: 63

// Verificar un registro específico
$prediccion = Prediccion::find(1);
dd($prediccion);

// Ver distribución de tipos
$tipos = Prediccion::groupBy('tipo')
    ->selectRaw('tipo, COUNT(*) as cantidad')
    ->get();

// Ver todas las predicciones
Prediccion::all();
```

### 3. **Indicadores de Éxito**:
- ✅ **63 registros** en la tabla `predicciones`
- ✅ **Todos con `estado = 1`** (activos)
- ✅ **`tiempo_ms` = NULL** para todos
- ✅ **Fechas entre** 2026-04-06 y 2026-05-05
- ✅ **Probabilidades entre** 0.8497 y 0.9658
- ✅ **top_predicciones con formato JSON válido**

---

## 📝 Resumen de Cambios

| # | Campo | Acción | Estado |
|---|-------|--------|--------|
| 1 | `prediccion_id` | Búsqueda (no actualiza) | - |
| 2 | `imagen_id` | Asigna desde array | ✅ |
| 3 | `ritmo_id` | Asigna desde array | ✅ |
| 4 | `probabilidad` | Asigna desde array | ✅ |
| 5 | `tiempo_ms` | Fija a NULL | ✅ |
| 6 | `top_predicciones` | Asigna JSON desde array | ✅ |
| 7 | `label_detectado` | Asigna desde array | ✅ |
| 8 | `label_code` | Asigna desde array | ✅ |
| 9 | `tipo` | Asigna desde array | ✅ |
| 10 | `estado` | Fija a 1 (Activo) | ✅ |
| 11 | `created_at` | Asigna desde array | ✅ |
| 12 | `updated_at` | Asigna desde array | ✅ |

**Total de campos con cambios: 11 de 12** (1 es solo para búsqueda)

---

## 🔐 Notas de Integridad

- **updateOrCreate** garantiza que no haya duplicados por `prediccion_id`
- Todos los valores se extraen del array `$predicciones`
- Las fechas se preservan tal como vienen en el array
- El estado siempre es `1` (garantiza que todos sean visibles/activos)
- Las timestamps se sincronizan para que se vean como datos antiguos (no actualizados recientemente)

# 🔍 GUÍA RÁPIDA DE VALIDACIÓN - PrediccionSeeder

## ✅ Campos que se Modifican en PrediccionSeeder

### Cuadro Resumen Ejecutivo

```
┌─────────────────────────────────────────────────────────────────┐
│ CAMPO               │ ESTADO   │ FUENTE      │ VALOR EJEMPLO   │
├─────────────────────────────────────────────────────────────────┤
│ prediccion_id       │ BÚSQUEDA │ Array       │ 1-63            │
│ imagen_id           │ ✅ CAMBIO│ Array       │ 1-63            │
│ ritmo_id            │ ✅ CAMBIO│ Array       │ 1,4,6,7,9       │
│ probabilidad        │ ✅ CAMBIO│ Array       │ 0.8497-0.9658   │
│ tiempo_ms           │ ✅ CAMBIO│ NULL FIJO   │ null            │
│ top_predicciones    │ ✅ CAMBIO│ Array JSON  │ [{...}]         │
│ label_detectado     │ ✅ CAMBIO│ Array       │ "Normal"        │
│ label_code          │ ✅ CAMBIO│ Array       │ "NORM"          │
│ tipo                │ ✅ CAMBIO│ Array       │ 1 o 2           │
│ estado              │ ✅ CAMBIO│ FIJO        │ 1 (Activo)      │
│ created_at          │ ✅ CAMBIO│ Array       │ 2026-04-06...   │
│ updated_at          │ ✅ CAMBIO│ Array       │ 2026-04-06...   │
└─────────────────────────────────────────────────────────────────┘

✅ TOTAL: 11 campos se modifican/asignan (1 solo busca)
```

---

## 📋 Validación por Campo

### 1️⃣ **imagen_id**
- **Descripción**: Referencia a la imagen ECG
- **¿Se modifica?**: ✅ SÍ
- **Rango**: 1 a 63
- **Cómo verificar**: `SELECT DISTINCT imagen_id FROM predicciones ORDER BY imagen_id;`
- **Resultado esperado**: 1, 2, 3, ..., 63 (63 valores únicos)

### 2️⃣ **ritmo_id**
- **Descripción**: Tipo de ritmo cardíaco
- **¿Se modifica?**: ✅ SÍ
- **Valores posibles**: 1 (Normal), 4 (PVC), 6 (AFIB), 7 (STACH), 9 (SBRAD)
- **Cómo verificar**: `SELECT DISTINCT ritmo_id FROM predicciones;`
- **Resultado esperado**: 1, 4, 6, 7, 9

### 3️⃣ **probabilidad**
- **Descripción**: Confianza de predicción (0-1)
- **¿Se modifica?**: ✅ SÍ
- **Rango**: 0.8497 a 0.9658 (~89% promedio)
- **Cómo verificar**: 
  ```sql
  SELECT MIN(probabilidad), MAX(probabilidad), AVG(probabilidad) 
  FROM predicciones;
  ```
- **Resultado esperado**: Min≈0.85, Max≈0.97, Avg≈0.89

### 4️⃣ **tiempo_ms**
- **Descripción**: Tiempo de procesamiento
- **¿Se modifica?**: ✅ SÍ (pero siempre a NULL)
- **Valor**: NULL
- **Cómo verificar**: `SELECT DISTINCT tiempo_ms FROM predicciones;`
- **Resultado esperado**: Solo NULL

### 5️⃣ **top_predicciones**
- **Descripción**: JSON con 2 predicciones principales
- **¿Se modifica?**: ✅ SÍ
- **Formato**: 
  ```json
  [
    {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.24},
    {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.76}
  ]
  ```
- **Cómo verificar**: `SELECT JSON_VALID(top_predicciones) FROM predicciones;`
- **Resultado esperado**: Todas filas devuelven 1 (JSON válido)

### 6️⃣ **label_detectado**
- **Descripción**: Nombre legible del ritmo
- **¿Se modifica?**: ✅ SÍ
- **Valores**: "Ritmo Sinusal Normal", "Fibrilacion Auricular", "Taquicardia Sinusal", 
              "Complejo ventricular prematuro", "Bradicardia Sinusal"
- **Cómo verificar**: `SELECT DISTINCT label_detectado FROM predicciones;`
- **Resultado esperado**: 5 valores únicos

### 7️⃣ **label_code**
- **Descripción**: Código abreviado del ritmo
- **¿Se modifica?**: ✅ SÍ
- **Valores**: NORM, AFIB, STACH, PVC, SBRAD
- **Cómo verificar**: `SELECT DISTINCT label_code FROM predicciones;`
- **Resultado esperado**: NORM, AFIB, STACH, PVC, SBRAD

### 8️⃣ **tipo**
- **Descripción**: Clasificación (Normal vs Arritmia)
- **¿Se modifica?**: ✅ SÍ
- **Valores**: 1 (Normal), 2 (Arritmia)
- **Cómo verificar**: 
  ```sql
  SELECT tipo, COUNT(*) as cantidad FROM predicciones GROUP BY tipo;
  ```
- **Resultado esperado**: 
  - tipo=1: ~32 registros (Normal)
  - tipo=2: ~31 registros (Arritmia)

### 9️⃣ **estado**
- **Descripción**: Estado del registro
- **¿Se modifica?**: ✅ SÍ (pero siempre a 1)
- **Valor**: 1 (Activo/Visible)
- **Cómo verificar**: `SELECT DISTINCT estado FROM predicciones;`
- **Resultado esperado**: Solo valor 1

### 🔟 **created_at**
- **Descripción**: Fecha de creación/evento
- **¿Se modifica?**: ✅ SÍ
- **Rango**: 2026-04-06 08:10:00 a 2026-05-05 09:15:00
- **Cómo verificar**: 
  ```sql
  SELECT MIN(created_at), MAX(created_at) FROM predicciones;
  ```
- **Resultado esperado**: Entre 2026-04-06 y 2026-05-05

### 1️⃣1️⃣ **updated_at**
- **Descripción**: Última actualización
- **¿Se modifica?**: ✅ SÍ (inicialmente igual a created_at)
- **Valor inicial**: Igual a created_at
- **Cómo verificar**: 
  ```sql
  SELECT COUNT(*) FROM predicciones 
  WHERE created_at = updated_at;
  ```
- **Resultado esperado**: 63 (todos inicialmente iguales)

---

## 🎯 Checklist de Validación Rápida

Copia y ejecuta estos comandos SQL en orden:

```sql
-- 1. ¿Se crearon los 63 registros?
SELECT COUNT(*) as total FROM predicciones;
-- Esperado: 63

-- 2. ¿Todos tienen imagen_id entre 1-63?
SELECT COUNT(*) FROM predicciones 
WHERE imagen_id BETWEEN 1 AND 63;
-- Esperado: 63

-- 3. ¿Todos tienen ritmo_id válido?
SELECT COUNT(*) FROM predicciones 
WHERE ritmo_id IN (1, 4, 6, 7, 9);
-- Esperado: 63

-- 4. ¿Todas las probabilidades están en rango?
SELECT COUNT(*) FROM predicciones 
WHERE probabilidad BETWEEN 0 AND 1;
-- Esperado: 63

-- 5. ¿tiempo_ms es NULL en todos?
SELECT COUNT(*) FROM predicciones 
WHERE tiempo_ms IS NULL;
-- Esperado: 63

-- 6. ¿top_predicciones contiene JSON válido?
SELECT COUNT(*) FROM predicciones 
WHERE JSON_VALID(top_predicciones);
-- Esperado: 63

-- 7. ¿Todos tienen estado = 1?
SELECT COUNT(*) FROM predicciones 
WHERE estado = 1;
-- Esperado: 63

-- 8. ¿Las fechas están en rango?
SELECT COUNT(*) FROM predicciones 
WHERE created_at BETWEEN '2026-04-01' AND '2026-05-31';
-- Esperado: 63

-- 9. ¿Todos los campos esenciales están llenos?
SELECT COUNT(*) FROM predicciones 
WHERE label_detectado IS NOT NULL 
AND label_code IS NOT NULL 
AND imagen_id IS NOT NULL;
-- Esperado: 63

-- 10. Ver resumen completo
SELECT 
  COUNT(*) as total_registros,
  COUNT(DISTINCT imagen_id) as imagenes_unicas,
  COUNT(DISTINCT ritmo_id) as ritmos_unicos,
  MIN(probabilidad) as prob_minima,
  MAX(probabilidad) as prob_maxima,
  COUNT(CASE WHEN estado = 1 THEN 1 END) as registros_activos,
  COUNT(CASE WHEN tipo = 1 THEN 1 END) as normales,
  COUNT(CASE WHEN tipo = 2 THEN 1 END) as arritmias
FROM predicciones;
```

---

## 📊 Resultado Esperado del Resumen

```
┌──────────────────────┬─────────────────────┐
│ Métrica              │ Valor Esperado      │
├──────────────────────┼─────────────────────┤
│ total_registros      │ 63                  │
│ imagenes_unicas      │ 63                  │
│ ritmos_unicos        │ 5                   │
│ prob_minima          │ ~0.8497             │
│ prob_maxima          │ ~0.9658             │
│ registros_activos    │ 63                  │
│ normales (tipo=1)    │ ~32                 │
│ arritmias (tipo=2)   │ ~31                 │
└──────────────────────┴─────────────────────┘
```

---

## 🚨 Si Algo No Coincide

| Problema | Causa Probable | Solución |
|----------|----------------|----------|
| Menos de 63 registros | Falta ejecutar el seeder | `php artisan db:seed --class=PrediccionSeeder` |
| estado ≠ 1 | Modificación manual | `UPDATE predicciones SET estado = 1;` |
| tiempo_ms ≠ NULL | Error en seeder | Revisar línea con `'tiempo_ms' => null` |
| JSON inválido | Formato corrupto | Revisar array `top_predicciones` en el seeder |
| Fechas incorrectas | Modificación del array | Restaurar array original del seeder |

---

## 🔐 Integridad de Datos

```
✅ updateOrCreate() previene duplicados
✅ Todos los campos se asignan desde el array
✅ Valores nulos se establecen explícitamente
✅ Timestamps se preservan correctamente
✅ Estado = 1 asegura visibilidad de todos los registros
```

---

## 📝 Conclusión

El seeder PrediccionSeeder:
- ✅ Modifica/asigna **11 campos** de 12 totales
- ✅ Utiliza **updateOrCreate** para seguridad
- ✅ Carga **63 registros** de predicciones de ECG
- ✅ Establece **estado = 1** en todos (visibles)
- ✅ Conserva **fechas originales** del evento
- ✅ Sincroniza **created_at = updated_at** inicialmente

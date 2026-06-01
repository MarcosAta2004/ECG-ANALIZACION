# 📊 Guía de Auditoría Mejorada - Según Roles

## 🎯 Estructura de Roles

```
┌─────────────────────────────────────────────────────────────┐
│                      3 ROLES EN EL SISTEMA                  │
├─────────────────────┬─────────────────────┬─────────────────┤
│  ADMINISTRADOR      │  CARDIOLOGO         │  TECNICO        │
├─────────────────────┼─────────────────────┼─────────────────┤
│ - Gestiona usuarios │ - Valida ECG        │ - Registra ECG  │
│ - Acceso total      │ - Diagnóstica       │ - Captura datos │
│ - Auditoría básica  │ - VALORACIÓN ⭐     │ - Auditoría     │
└─────────────────────┴─────────────────────┴─────────────────┘
```

---

## ✅ Campos de Auditoría por Rol

### Campos Presentes para Todos

```
✅ usuario_id          → Quién realizó la acción
✅ accion              → QUÉ hizo (crear, actualizar, eliminar)
✅ modulo              → DÓNDE (Clínico, Seguridad, Mantenimiento)
✅ entidad             → QUÉ entidad (diagnosticos, pacientes, etc)
✅ entidad_id          → ID de la entidad modificada
✅ descripcion         → Descripción textual de la acción
✅ valores_anteriores  → Estado anterior (JSON)
✅ valores_nuevos      → Estado nuevo (JSON)
✅ ip_address          → IP del usuario
✅ user_agent          → Navegador/cliente
✅ created_at          → Cuándo ocurrió
✅ nivel_confianza     → BAJO, MEDIO, ALTO (Todos los roles)
✅ razon_cambio        → POR QUÉ se cambió (Todos los roles)
✅ severidad           → CRITICA, IMPORTANTE, MENOR (Todos los roles)
```

### ⭐ Campos SOLO para CARDIOLOGO

```
✅ valoracion_medico   → Comentario médico/clínico (SOLO CARDIOLOGO)
✅ cardiologo_id       → ID del cardiólogo que valoró (SOLO CARDIOLOGO)

❌ Si es TECNICO o ADMINISTRADOR:
   - valoracion_medico = NULL
   - cardiologo_id = NULL
```

---

## 📋 Ejemplos de Auditoría por Rol

### 1️⃣ TECNICO Registra Nuevo Paciente

```json
{
  "auditoria_id": 1,
  "usuario_id": 3,                           // ID del técnico
  "accion": "crear",
  "modulo": "Clinico",
  "entidad": "pacientes",
  "entidad_id": "P-2026-00001",
  "descripcion": "Crear en la entidad pacientes (ID: P-2026-00001)",
  "valores_anteriores": null,
  "valores_nuevos": {
    "nombres": "Juan",
    "apellido_paterno": "Pérez",
    "edad": 45
  },
  "nivel_confianza": "ALTO",                 // ✅ Técnico lo llena
  "razon_cambio": "CORRECCION_MANUAL",       // ✅ Técnico lo llena
  "severidad": "MENOR",                      // ✅ Técnico lo llena
  "valoracion_medico": null,                 // ❌ Técnico NO puede llenar
  "cardiologo_id": null,                     // ❌ Automáticamente null
  "created_at": "2026-06-01 10:30:00"
}
```

### 2️⃣ CARDIOLOGO Valida Diagnóstico

```json
{
  "auditoria_id": 2,
  "usuario_id": 2,                           // ID del cardiólogo
  "accion": "actualizar",
  "modulo": "Clinico",
  "entidad": "diagnosticos",
  "entidad_id": 15,
  "descripcion": "Actualizar en la entidad diagnosticos (ID: 15)",
  "valores_anteriores": {
    "estado": "pendiente",
    "ritmo_id": 1
  },
  "valores_nuevos": {
    "estado": "validado",
    "ritmo_id": 6
  },
  "nivel_confianza": "ALTO",                 // ✅ Cardiólogo lo llena
  "razon_cambio": "VALIDACION_MEDICA",       // ✅ Cardiólogo lo llena
  "severidad": "CRITICA",                    // ✅ Cardiólogo lo llena
  "valoracion_medico": "Concordancia verificada. Patrón típico de FA.",
  "cardiologo_id": 2,                        // ✅ AUTO: ID del cardiólogo
  "created_at": "2026-06-01 11:45:00"
}
```

### 3️⃣ ADMINISTRADOR Modifica Usuario

```json
{
  "auditoria_id": 3,
  "usuario_id": 1,                           // ID del administrador
  "accion": "actualizar",
  "modulo": "Seguridad",
  "entidad": "users",
  "entidad_id": 5,
  "descripcion": "Actualizar en la entidad users (ID: 5)",
  "valores_anteriores": {
    "estado": "1"
  },
  "valores_nuevos": {
    "estado": "0"
  },
  "nivel_confianza": "ALTO",                 // ✅ Admin lo llena
  "razon_cambio": "CORRECCION_MANUAL",       // ✅ Admin lo llena
  "severidad": "IMPORTANTE",                 // ✅ Admin lo llena
  "valoracion_medico": null,                 // ❌ Admin NO puede llenar
  "cardiologo_id": null,                     // ❌ Automáticamente null
  "created_at": "2026-06-01 14:20:00"
}
```

---

## 🔐 Validación de Roles en ServicioAuditoria

### Cómo se Valida

```php
// En ServicioAuditoria::registrar()

$usuarioActual = Auth::user() ?? User::find(Session::get('user.id'));

// ✅ Solo si es CARDIOLOGO:
if ($usuarioActual && $usuarioActual->hasRole('CARDIOLOGO')) {
    $arrayAuditoria['valoracion_medico'] = $valoracionMedico;      // ✅ Se llena
    $arrayAuditoria['cardiologo_id'] = $cardiologo_id ?? Auth::id(); // ✅ Se llena
}

// ❌ Si es TECNICO o ADMINISTRADOR:
// Estos campos permanecen NULL automáticamente
```

---

## 📝 Cómo Usar en Controllers

### Registrar Auditoría desde TECNICO

```php
// TecnicoController.php

use App\Services\ServicioAuditoria;

public function crearPaciente(Request $request)
{
    $paciente = Paciente::create($request->validated());
    
    // Auditoría registrada automáticamente por Trait Auditable
    // Pero puedes agregar datos específicos manualmente:
    
    ServicioAuditoria::registrar(
        accion: 'crear',
        modulo: 'Clinico',
        entidad: 'pacientes',
        entidadId: $paciente->paciente_id,
        descripcion: 'Nuevo paciente registrado por técnico',
        valoresNuevos: $paciente->toArray(),
        request: $request,
        // ✅ Parámetros que SÍ puede llenar técnico:
        nivelConfianza: 'ALTO',
        razonCambio: 'CORRECCION_MANUAL',
        severidad: 'MENOR'
        // ❌ valoracionMedico: será ignorado si no es cardiólogo
        // ❌ cardiologo_id: será ignorado si no es cardiólogo
    );
    
    return response()->json(['paciente' => $paciente]);
}
```

### Registrar Auditoría desde CARDIOLOGO

```php
// CardiologoController.php

use App\Services\ServicioAuditoria;

public function validarDiagnostico(Request $request, $diagnosticoId)
{
    // ✅ Validar que es cardiólogo
    if (!ServicioAuditoria::puedeValorar()) {
        return response()->json(['error' => 'No autorizado'], 403);
    }
    
    $diagnostico = Diagnostico::findOrFail($diagnosticoId);
    $anterior = $diagnostico->toArray();
    
    $diagnostico->update([
        'estado' => 'validado',
        'observacion' => $request->observacion
    ]);
    
    // ✅ Auditoría con VALORACIÓN MÉDICA:
    ServicioAuditoria::registrar(
        accion: 'actualizar',
        modulo: 'Clinico',
        entidad: 'diagnosticos',
        entidadId: $diagnosticoId,
        descripcion: 'Validación médica del diagnóstico',
        valoresAnteriores: $anterior,
        valoresNuevos: $diagnostico->toArray(),
        request: $request,
        // ✅ Cardiólogo SÍ puede llenar valoración:
        nivelConfianza: 'ALTO',
        razonCambio: 'VALIDACION_MEDICA',
        severidad: 'CRITICA',
        valoracionMedico: $request->valoracion_medico,  // ⭐ SOLO AQUI
        cardiologo_id: auth()->id()                     // ⭐ AUTO FILLED
    );
    
    return response()->json(['diagnostico' => $diagnostico]);
}
```

---

## 🔍 Validaciones de Campos

### Valores Válidos para `nivel_confianza`

```
BAJO   → Datos inseguros, requieren revisión
MEDIO  → Datos parcialmente verificados
ALTO   → Datos completamente validados
```

### Valores Válidos para `razon_cambio`

```
CORRECCION_MANUAL       → Usuario corrigió manualmente
VALIDACION_MEDICA       → Validación clínica (solo cardiólogo)
DISCREPANCIA_IA         → Diferencia entre IA y médico
ERROR_SISTEMA           → Sistema detectó inconsistencia
REQUIERE_REVISION       → Cambio pendiente de revisión
```

### Valores Válidos para `severidad`

```
CRITICA     → Afecta diagnóstico/tratamiento del paciente
IMPORTANTE  → Afecta datos clínicos relevantes
MENOR       → Afecta solo información secundaria
```

---

## 📊 Consultas SQL para Auditoría

### Ver Auditoría por Rol

```sql
-- Auditorías generadas por CARDIOLOGO con valoración
SELECT a.*, u.login as usuario_login, c.login as cardiologo_login
FROM auditorias a
LEFT JOIN users u ON a.usuario_id = u.id
LEFT JOIN users c ON a.cardiologo_id = c.id
WHERE a.cardiologo_id IS NOT NULL
ORDER BY a.created_at DESC;

-- Auditorías de TECNICO (sin valoración médica)
SELECT a.*, u.login as usuario_login
FROM auditorias a
LEFT JOIN users u ON a.usuario_id = u.id
WHERE a.cardiologo_id IS NULL 
AND u.id IN (SELECT user_id FROM model_has_roles WHERE role_id = 3)
ORDER BY a.created_at DESC;

-- Todas las auditorías con validación médica
SELECT a.*, u.nombres, a.valoracion_medico
FROM auditorias a
LEFT JOIN users u ON a.cardiologo_id = u.id
WHERE a.valoracion_medico IS NOT NULL
ORDER BY a.created_at DESC;
```

---

## ✨ Resumen Ejecutivo

```
┌──────────────────────────────────────────────────────────────┐
│  AUDITORÍA MEJORADA - DISTRIBUCIÓN POR ROLES                │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  TODOS LOS ROLES (3/3):                                     │
│  ✅ usuario_id, accion, modulo, entidad                     │
│  ✅ nivel_confianza, razon_cambio, severidad                │
│  ✅ valores_anteriores, valores_nuevos                      │
│                                                              │
│  SOLO CARDIOLOGO (1/3):                                     │
│  ⭐ valoracion_medico (comentario clínico)                  │
│  ⭐ cardiologo_id (validación automática)                   │
│                                                              │
│  TECNICO y ADMINISTRADOR:                                   │
│  ❌ valoracion_medico = NULL                                │
│  ❌ cardiologo_id = NULL                                    │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

## 🚀 Para Ejecutar Migración

```bash
# Ejecutar la nueva migración
php artisan migrate

# Verificar en BD
SELECT * FROM auditorias;

# Ver estructura de tabla
SHOW COLUMNS FROM auditorias;
```

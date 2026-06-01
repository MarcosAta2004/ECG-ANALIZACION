# 🔧 Implementación Rápida - Controllers

## 📝 Template para Diferentes Roles

### Para TECNICO (Registra ECG)

```php
<?php

namespace App\Http\Controllers;

use App\Services\ServicioAuditoria;
use App\Models\Estudio;

class EstudioTecnicoController extends Controller
{
    public function crear(Request $request)
    {
        $estudio = Estudio::create($request->validated());
        
        // La auditoría se registra automáticamente por Trait Auditable
        // Pero puedes agregar contexto:
        
        ServicioAuditoria::registrar(
            accion: 'crear',
            modulo: 'Clinico',
            entidad: 'estudios',
            entidadId: $estudio->estudio_id,
            descripcion: 'Nuevo estudio ECG registrado',
            valoresNuevos: $estudio->toArray(),
            request: $request,
            // ✅ TECNICO SÍ puede llenar estos:
            nivelConfianza: 'MEDIO',          // Datos iniciales
            razonCambio: 'CORRECCION_MANUAL', // Técnico capturó manualmente
            severidad: 'MENOR'                // Es datos base, no diagnóstico
            // ❌ NO agregar: valoracionMedico, cardiologo_id
        );
        
        return response()->json(['estudio' => $estudio]);
    }
}
```

---

### Para CARDIOLOGO (Valida/Diagnostica)

```php
<?php

namespace App\Http\Controllers;

use App\Services\ServicioAuditoria;
use App\Models\Diagnostico;

class DiagnosticoCardiologoController extends Controller
{
    public function validar(Request $request, $diagnosticoId)
    {
        // ✅ VALIDAR QUE ES CARDIOLOGO
        if (!ServicioAuditoria::puedeValorar()) {
            abort(403, 'Solo cardiólogos pueden validar diagnósticos');
        }
        
        $diagnostico = Diagnostico::findOrFail($diagnosticoId);
        $anterior = $diagnostico->toArray();
        
        $diagnostico->update([
            'estado' => 'validado',
            'observacion' => $request->observacion
        ]);
        
        // ✅ CARDIOLOGO SÍ PUEDE LLENAR VALORACIÓN:
        ServicioAuditoria::registrar(
            accion: 'actualizar',
            modulo: 'Clinico',
            entidad: 'diagnosticos',
            entidadId: $diagnosticoId,
            descripcion: 'Validación clínica realizada',
            valoresAnteriores: $anterior,
            valoresNuevos: $diagnostico->toArray(),
            request: $request,
            // ✅ CARDIOLOGO SÍ puede llenar TODO:
            nivelConfianza: 'ALTO',
            razonCambio: 'VALIDACION_MEDICA',   // ⭐ ESPECIFICO CARDIOLOGO
            severidad: 'CRITICA',
            valoracionMedico: $request->valoracion_medico,  // ⭐ SOLO AQUI
            cardiologo_id: auth()->id()  // ✅ Se llena automáticamente
        );
        
        return response()->json([
            'message' => 'Validación registrada',
            'diagnostico' => $diagnostico
        ]);
    }

    public function reportarDiscrepancia(Request $request, $diagnosticoId)
    {
        if (!ServicioAuditoria::puedeValorar()) {
            abort(403, 'Solo cardiólogos pueden reportar discrepancias');
        }
        
        $diagnostico = Diagnostico::findOrFail($diagnosticoId);
        
        ServicioAuditoria::registrar(
            accion: 'actualizar',
            modulo: 'Clinico',
            entidad: 'diagnosticos',
            entidadId: $diagnosticoId,
            descripcion: 'Discrepancia reportada entre IA y médico',
            valoresNuevos: [
                'ritmo_ia' => $diagnostico->ritmo_id_ia,
                'ritmo_medico' => $request->ritmo_medico
            ],
            request: $request,
            nivelConfianza: 'ALTO',
            razonCambio: 'DISCREPANCIA_IA',     // ⭐ PARA DISCREPANCIAS
            severidad: 'CRITICA',
            valoracionMedico: "IA predijo ritmo {$diagnostico->ritmo_id_ia}, pero médico observa {$request->ritmo_medico}. " . 
                             $request->observacion,
            cardiologo_id: auth()->id()
        );
        
        return response()->json(['message' => 'Discrepancia registrada']);
    }
}
```

---

### Para ADMINISTRADOR (Gestiona Sistema)

```php
<?php

namespace App\Http\Controllers;

use App\Services\ServicioAuditoria;
use App\Models\User;

class AdministradorController extends Controller
{
    public function cambiarEstadoUsuario(Request $request, $userId)
    {
        $usuario = User::findOrFail($userId);
        $anterior = ['estado' => $usuario->estado];
        
        $usuario->update(['estado' => $request->estado]);
        
        // ❌ ADMINISTRADOR NO PUEDE llenar valoracionMedico
        ServicioAuditoria::registrar(
            accion: 'actualizar',
            modulo: 'Seguridad',
            entidad: 'users',
            entidadId: $userId,
            descripcion: "Usuario {$usuario->login} cambió de estado",
            valoresAnteriores: $anterior,
            valoresNuevos: ['estado' => $usuario->estado],
            request: $request,
            // ✅ Admin puede llenar estos:
            nivelConfianza: 'ALTO',
            razonCambio: 'CORRECCION_MANUAL',
            severidad: 'IMPORTANTE'
            // ❌ NO agregar: valoracionMedico, cardiologo_id
            // (Serán NULL automáticamente)
        );
        
        return response()->json(['user' => $usuario]);
    }

    public function detectarError(Request $request)
    {
        ServicioAuditoria::registrar(
            accion: 'actualizar',
            modulo: $request->modulo,
            entidad: $request->entidad,
            entidadId: $request->entidad_id,
            descripcion: 'Error del sistema detectado y reportado',
            valoresNuevos: $request->datos ?? [],
            request: $request,
            nivelConfianza: 'BAJO',         // Error = baja confianza
            razonCambio: 'ERROR_SISTEMA',   // ⭐ PARA ERRORES
            severidad: $request->severidad ?? 'IMPORTANTE'
            // ❌ NO llenar: valoracionMedico
        );
        
        return response()->json(['message' => 'Error registrado en auditoría']);
    }
}
```

---

## 🎯 Diferencias Clave por Rol

```php
// TECNICO:
ServicioAuditoria::registrar(
    razonCambio: 'CORRECCION_MANUAL',        // ✅ Lo hace
    severidad: 'MENOR',                      // ✅ Lo hace
    valoracionMedico: null,                  // ❌ IGNORADO
    cardiologo_id: null                      // ❌ IGNORADO
);

// CARDIOLOGO:
ServicioAuditoria::registrar(
    razonCambio: 'VALIDACION_MEDICA',        // ✅ Lo hace
    severidad: 'CRITICA',                    // ✅ Lo hace
    valoracionMedico: 'Mi análisis clínico', // ✅ SE ACEPTA
    cardiologo_id: auth()->id()              // ✅ SE ACEPTA
);

// ADMINISTRADOR:
ServicioAuditoria::registrar(
    razonCambio: 'CORRECCION_MANUAL',        // ✅ Lo hace
    severidad: 'IMPORTANTE',                 // ✅ Lo hace
    valoracionMedico: 'Intento escribir',    // ❌ IGNORADO (no es médico)
    cardiologo_id: null                      // ❌ IGNORADO
);
```

---

## 🔍 Validación en Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\ServicioAuditoria;

class ValidarDiagnosticoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ Solo cardiólogo puede validar
        return ServicioAuditoria::puedeValorar();
    }

    public function rules(): array
    {
        $isMedico = ServicioAuditoria::puedeValorar();
        
        return [
            'observacion' => 'nullable|string|max:500',
            'nivel_confianza' => 'required|in:BAJO,MEDIO,ALTO',
            'razon_cambio' => 'required|in:CORRECCION_MANUAL,VALIDACION_MEDICA,DISCREPANCIA_IA,ERROR_SISTEMA,REQUIERE_REVISION',
            'severidad' => 'required|in:CRITICA,IMPORTANTE,MENOR',
            
            // ✅ Solo si es médico:
            'valoracion_medico' => $isMedico 
                ? 'required|string|max:2000' 
                : 'nullable'  // Si no es médico, se ignora
        ];
    }

    public function messages(): array
    {
        return [
            'authorize' => 'Solo cardiólogos pueden validar diagnósticos',
            'valoracion_medico.required' => 'Cardiólogo debe proporcionar valoración',
            'nivel_confianza.required' => 'Especifique nivel de confianza',
        ];
    }
}
```

---

## 🧪 Testing

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_no_puede_llenar_valoracion_medica()
    {
        $tecnico = User::factory()->create();
        $tecnico->assignRole('TECNICO');
        $this->actingAs($tecnico);
        
        // Intenta registrar auditoría con valoracionMedico
        ServicioAuditoria::registrar(
            accion: 'crear',
            modulo: 'Clinico',
            entidad: 'estudios',
            entidadId: 1,
            descripcion: 'Test',
            nivelConfianza: 'ALTO',
            valoracionMedico: 'Intento del técnico'  // ❌ Será ignorado
        );
        
        $auditoria = Auditoria::latest()->first();
        $this->assertNull($auditoria->valoracion_medico);  // ✅ NULL
        $this->assertNull($auditoria->cardiologo_id);      // ✅ NULL
    }

    public function test_cardiologo_puede_llenar_valoracion_medica()
    {
        $cardiologo = User::factory()->create();
        $cardiologo->assignRole('CARDIOLOGO');
        $this->actingAs($cardiologo);
        
        $valoracion = 'Concordancia verificada entre IA y médico';
        
        ServicioAuditoria::registrar(
            accion: 'actualizar',
            modulo: 'Clinico',
            entidad: 'diagnosticos',
            entidadId: 1,
            descripcion: 'Test',
            razonCambio: 'VALIDACION_MEDICA',
            valoracionMedico: $valoracion  // ✅ Se acepta
        );
        
        $auditoria = Auditoria::latest()->first();
        $this->assertEquals($valoracion, $auditoria->valoracion_medico);  // ✅ Se guardó
        $this->assertEquals($cardiologo->id, $auditoria->cardiologo_id);  // ✅ Auto-filled
    }

    public function test_puede_valorar_validacion()
    {
        // Sin autenticación
        $this->assertFalse(ServicioAuditoria::puedeValorar());
        
        // Con cardiólogo
        $cardiologo = User::factory()->create();
        $cardiologo->assignRole('CARDIOLOGO');
        $this->actingAs($cardiologo);
        
        $this->assertTrue(ServicioAuditoria::puedeValorar());  // ✅ true
    }
}
```

---

## 📊 Query Útil para Auditoría

```php
// En Controller o comando:

// Ver todas las valoraciones médicas
$valoraciones = Auditoria::whereNotNull('valoracion_medico')
    ->with(['usuario', 'cardiologo'])
    ->latest()
    ->get();

foreach ($valoraciones as $audit) {
    echo "Cardiólogo: {$audit->cardiologo->nombres}\n";
    echo "Valoración: {$audit->valoracion_medico}\n";
    echo "Fecha: {$audit->created_at}\n";
}

// Ver discrepancias IA vs Médico
$discrepancias = Auditoria::where('razon_cambio', 'DISCREPANCIA_IA')
    ->with('cardiologo')
    ->count();

echo "Discrepancias reportadas: $discrepancias\n";
```

<?php

namespace Tests\Feature;

use App\Models\Imagen;
use App\Models\Prediccion;
use App\Models\RitmoCardiaco;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests de integración: comunicación Laravel ↔ FastAPI.
 * Usa Http::fake() para simular respuestas del modelo sin necesitar uvicorn.
 */
class ImagenAnalisisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Respuesta simulada exitosa del modelo IA.
     */
    private function respuestaExitosaIA(): array
    {
        return [
            'label'      => 'NORM',
            'confidence' => 0.95,
            'scores'     => array_fill(0, 13, 0.073),
            'leads'      => ['I','II','III','aVR','aVL','aVF','V1','V2','V3','V4','V5','V6'],
            'signals'    => array_fill(0, 12, array_fill(0, 500, 0.0)),
            'metrics'    => [
                'heart_rate'  => 72.0,
                'beats'       => 12,
                'variability' => 0.045,
                'amplitude'   => 1.2,
            ],
            'top_predictions' => [
                ['label' => 'Ritmo Sinusal Normal', 'code' => 'NORM',  'probability' => 95.0],
                ['label' => 'Bradicardia Sinusal',  'code' => 'SBRAD', 'probability' => 2.0],
                ['label' => 'Arritmia Sinusal',     'code' => 'SARRH', 'probability' => 1.5],
                ['label' => 'Taquicardia Sinusal',  'code' => 'STACH', 'probability' => 1.0],
                ['label' => 'Bloqueo AV 1er grado', 'code' => '1AVB', 'probability' => 0.5],
            ],
        ];
    }

    /**
     * Crea un Imagen en BD con el archivo físico en el storage fake.
     * Es necesario porque el controller abre el archivo con fopen().
     */
    private function crearImagenConArchivo(): Imagen
    {
        $filename = 'ecg/test_ecg_' . uniqid() . '.png';
        $imagen = Imagen::factory()->create([
            'ruta'    => $filename,
            'formato' => 'png',
        ]);
        // Crear el archivo en el disco fake
        Storage::disk('public')->put($filename, str_repeat('A', 1024));
        return $imagen;
    }

    /** @test */
    public function analizar_imagen_ya_analizada_devuelve_error_409(): void
    {
        $this->loginComo('tecnico');

        $imagen = $this->crearImagenConArchivo();
        // Ya tiene predicción
        Prediccion::factory()->create(['imagen_id' => $imagen->imagen_id]);

        $response = $this->postJson(route('imagenes.analizar', $imagen));

        $response->assertStatus(409);
        $response->assertJsonFragment(['error' => 'Esta imagen ya tiene una predicción registrada.']);
    }

    /** @test */
    public function analizar_imagen_cuando_fastapi_no_disponible_devuelve_codigo_error(): void
    {
        $this->loginComo('tecnico');

        // Simular que FastAPI falla
        Http::fake([
            '*' => Http::response(['detail' => 'Connection refused'], 503)
        ]);

        $imagen = $this->crearImagenConArchivo();

        $response = $this->postJson(route('imagenes.analizar', $imagen));

        // Debe manejar el error con 502
        $this->assertContains($response->status(), [502, 500]);
        $response->assertJsonStructure(['error']);
    }

    /** @test */
    public function analizar_imagen_llama_a_fastapi_y_guarda_prediccion(): void
    {
        $this->loginComo('tecnico');

        Http::fake([
            '*' => Http::response($this->respuestaExitosaIA(), 200)
        ]);

        $imagen = $this->crearImagenConArchivo();

        $response = $this->postJson(route('imagenes.analizar', $imagen));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'prediccion_id',
            'ritmo',
            'label',
            'probabilidad',
            'tiempo_ms',
            'top_predictions',
        ]);

        $this->assertDatabaseHas('predicciones', [
            'imagen_id' => $imagen->imagen_id,
        ]);
    }

    /** @test */
    public function probabilidad_se_guarda_normalizada_entre_cero_y_uno(): void
    {
        $this->loginComo('tecnico');

        $data = $this->respuestaExitosaIA();
        $data['confidence'] = 0.92;

        Http::fake([
            '*' => Http::response($data, 200)
        ]);

        $imagen = $this->crearImagenConArchivo();

        $response = $this->postJson(route('imagenes.analizar', $imagen));
        $response->assertStatus(200);

        $prediccion = Prediccion::where('imagen_id', $imagen->imagen_id)->first();
        $this->assertNotNull($prediccion);
        $this->assertGreaterThanOrEqual(0.0, $prediccion->probabilidad);
        $this->assertLessThanOrEqual(1.0, $prediccion->probabilidad);
    }

    /** @test */
    public function codigo_ritmo_inexistente_en_catalogo_devuelve_error_422(): void
    {
        $this->loginComo('tecnico');

        // IA devuelve un código que no está en la tabla ritmos_cardiacos
        $data = $this->respuestaExitosaIA();
        $data['top_predictions'][0]['code'] = 'CODIGO_INVALIDO_XYZ';

        Http::fake([
            '*' => Http::response($data, 200)
        ]);

        $imagen = $this->crearImagenConArchivo();

        $response = $this->postJson(route('imagenes.analizar', $imagen));

        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Estudio;
use App\Models\Imagen;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests de feature para el módulo de carga de Imágenes ECG.
 */
class ImagenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Usar disco de storage falso para no tocar archivos reales
        Storage::fake('public');
    }

    /** @test */
    public function store_carga_imagen_png_valida(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['estado' => 1]);

        $archivo = UploadedFile::fake()->image('ecg_test.png', 1200, 800);

        $response = $this->post(route('imagenes.store'), [
            'estudio_id' => $estudio->estudio_id,
            'archivo'    => $archivo,
        ]);

        $response->assertRedirect(route('upload'));
        $response->assertSessionHas('ok', 'enabled');

        $this->assertDatabaseHas('imagenes', [
            'estudio_id' => $estudio->estudio_id,
            'formato'    => 'png',
        ]);
    }

    /** @test */
    public function store_carga_imagen_jpg_valida(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['estado' => 1]);

        $archivo = UploadedFile::fake()->image('ecg_test.jpg', 1200, 800);

        $response = $this->post(route('imagenes.store'), [
            'estudio_id' => $estudio->estudio_id,
            'archivo'    => $archivo,
        ]);

        $response->assertRedirect(route('upload'));
        $this->assertDatabaseHas('imagenes', [
            'estudio_id' => $estudio->estudio_id,
            'formato'    => 'jpg',
        ]);
    }

    /** @test */
    public function store_rechaza_formato_no_permitido(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['estado' => 1]);

        $archivo = UploadedFile::fake()->create('ecg.exe', 100, 'application/octet-stream');

        $response = $this->post(route('imagenes.store'), [
            'estudio_id' => $estudio->estudio_id,
            'archivo'    => $archivo,
        ]);

        $response->assertSessionHasErrors(['archivo']);
    }

    /** @test */
    public function store_rechaza_imagen_mayor_a_10mb(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['estado' => 1]);

        // 11MB > 10MB límite
        $archivo = UploadedFile::fake()->create('ecg_grande.png', 11264, 'image/png');

        $response = $this->post(route('imagenes.store'), [
            'estudio_id' => $estudio->estudio_id,
            'archivo'    => $archivo,
        ]);

        $response->assertSessionHasErrors(['archivo']);
    }

    /** @test */
    public function store_no_permite_segunda_imagen_en_el_mismo_estudio(): void
    {
        $this->loginComo('tecnico');
        $estudio = Estudio::factory()->create(['estado' => 1]);

        // Primera imagen (ya existe en BD)
        Imagen::factory()->create(['estudio_id' => $estudio->estudio_id]);

        // Intentar subir segunda imagen al mismo estudio
        $archivo = UploadedFile::fake()->image('ecg_segunda.png', 1200, 800);

        $response = $this->post(route('imagenes.store'), [
            'estudio_id' => $estudio->estudio_id,
            'archivo'    => $archivo,
        ]);

        $response->assertSessionHasErrors(['estudio_id']);
    }

    /** @test */
    public function store_requiere_estudio_id(): void
    {
        $this->loginComo('tecnico');
        $archivo = UploadedFile::fake()->image('ecg.png');

        $response = $this->post(route('imagenes.store'), [
            'archivo' => $archivo,
        ]);

        $response->assertSessionHasErrors(['estudio_id']);
    }

    /** @test */
    public function destroy_desactiva_imagen_sin_borrarla(): void
    {
        $this->loginComo('tecnico');
        $imagen = Imagen::factory()->create(['estado' => 1]);

        $response = $this->delete(route('imagenes.destroy', $imagen));

        $response->assertRedirect(route('upload'));
        $imagen->refresh();
        $this->assertEquals(0, $imagen->estado);
        $this->assertDatabaseHas('imagenes', ['imagen_id' => $imagen->imagen_id]);
    }

    /** @test */
    public function activar_reactiva_una_imagen_desactivada(): void
    {
        $this->loginComo('tecnico');
        $imagen = Imagen::factory()->inactivo()->create();

        $response = $this->put(route('imagenes.activar', $imagen));

        $response->assertRedirect(route('upload'));
        $imagen->refresh();
        $this->assertEquals(1, $imagen->estado);
    }
}

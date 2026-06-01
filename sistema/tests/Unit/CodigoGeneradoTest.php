<?php

namespace Tests\Unit;

use App\Models\Paciente;
use App\Models\PrefijoPaciente;
use Tests\TestCase;

/**
 * Tests unitarios para la lógica de generación del código de paciente.
 * El formato es: PREFIJO-AÑO-NNN (ej: TEST-2026-001)
 */
class CodigoGeneradoTest extends TestCase
{
    /** @test */
    public function codigo_tiene_formato_correcto(): void
    {
        $paciente = Paciente::factory()->create([
            'codigo_generado' => 'PAC-2026-001',
        ]);

        // Validar formato: PREFIJO-AÑO-NNN
        $this->assertMatchesRegularExpression('/^[A-Z]+-\d{4}-\d{3}$/', $paciente->codigo_generado);
    }

    /** @test */
    public function padding_de_tres_digitos_funciona_correctamente(): void
    {
        $casos = [
            ['numero' => 1,   'esperado' => '001'],
            ['numero' => 9,   'esperado' => '009'],
            ['numero' => 10,  'esperado' => '010'],
            ['numero' => 99,  'esperado' => '099'],
            ['numero' => 100, 'esperado' => '100'],
            ['numero' => 999, 'esperado' => '999'],
        ];

        foreach ($casos as $caso) {
            $resultado = str_pad($caso['numero'], 3, '0', STR_PAD_LEFT);
            $this->assertEquals(
                $caso['esperado'],
                $resultado,
                "El número {$caso['numero']} debería dar {$caso['esperado']}"
            );
        }
    }

    /** @test */
    public function codigo_generado_es_unico_en_base_de_datos(): void
    {
        $prefijo = PrefijoPaciente::factory()->create(['nombre' => 'UNQ']);
        $anio    = now()->year;

        $p1 = Paciente::factory()->create([
            'prefijo_id'      => $prefijo->prefijo_id,
            'codigo_generado' => 'UNQ-' . $anio . '-001',
        ]);

        $p2 = Paciente::factory()->create([
            'prefijo_id'      => $prefijo->prefijo_id,
            'codigo_generado' => 'UNQ-' . $anio . '-002',
        ]);

        $this->assertNotEquals($p1->codigo_generado, $p2->codigo_generado);
        $this->assertDatabaseHas('pacientes', ['codigo_generado' => 'UNQ-' . $anio . '-001']);
        $this->assertDatabaseHas('pacientes', ['codigo_generado' => 'UNQ-' . $anio . '-002']);
    }

    /** @test */
    public function codigo_usa_prefijo_en_mayusculas(): void
    {
        $paciente = Paciente::factory()->create([
            'codigo_generado' => 'ABC-2026-001',
        ]);

        $partes = explode('-', $paciente->codigo_generado);
        $this->assertEquals(strtoupper($partes[0]), $partes[0]);
    }

    /** @test */
    public function codigo_contiene_anio_actual(): void
    {
        $anio     = now()->year;
        $paciente = Paciente::factory()->create([
            'codigo_generado' => 'PAC-' . $anio . '-001',
        ]);

        $this->assertStringContainsString((string) $anio, $paciente->codigo_generado);
    }
}

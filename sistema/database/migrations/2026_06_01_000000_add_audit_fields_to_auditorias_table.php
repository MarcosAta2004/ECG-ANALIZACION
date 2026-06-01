<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            // ⭐ CAMPO PRIORITARIO: Valoración del cardiólogo
            // Solo se llena si el usuario tiene rol CARDIOLOGO
            $table->longText('valoracion_medico')
                ->nullable()
                ->after('descripcion');

            // ID del cardiólogo que realizó la valoración
            // Se llena automáticamente si auth()->user()->hasRole('CARDIOLOGO')
            $table->foreignId('cardiologo_id')
                ->nullable()
                ->after('valoracion_medico')
                ->constrained('users')
                ->nullOnDelete();

            // Confianza en los datos auditados
            // Relevancia: Todos los roles generan auditoría con este nivel
            $table->enum('nivel_confianza', ['BAJO', 'MEDIO', 'ALTO'])
                ->nullable()
                ->after('cardiologo_id');

            // Razón del cambio: Contexto de por qué se realizó la acción
            // Relevancia: Todos los roles generan auditoría con esta razón
            $table->enum('razon_cambio', [
                'CORRECCION_MANUAL',      // Cambio manual por usuario
                'VALIDACION_MEDICA',      // Solo cardiólogo
                'DISCREPANCIA_IA',        // Diferencia entre IA y médico
                'ERROR_SISTEMA',          // Error detectado
                'REQUIERE_REVISION'       // Requiere validación
            ])
            ->nullable()
            ->after('nivel_confianza');

            // Severidad del cambio: Impacto en la salud del paciente
            // Relevancia: Todos los roles generan auditoría con esta clasificación
            $table->enum('severidad', ['CRITICA', 'IMPORTANTE', 'MENOR'])
                ->nullable()
                ->after('razon_cambio');
        });
    }

    public function down(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropForeign(['cardiologo_id']);
            $table->dropColumn([
                'valoracion_medico',
                'cardiologo_id',
                'nivel_confianza',
                'razon_cambio',
                'severidad'
            ]);
        });
    }
};

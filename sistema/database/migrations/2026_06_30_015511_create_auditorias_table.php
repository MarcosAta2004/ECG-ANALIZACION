<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id('auditoria_id');

            // 1. QUIÉN Y A QUIÉN
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('paciente_id')->nullable();
            $table->foreign('paciente_id')->references('paciente_id')->on('pacientes')->nullOnDelete();

            // 2. QUÉ HIZO Y DÓNDE
            $table->string('accion', 50);
            $table->string('modulo', 100);

            // 3. LOS CAMPOS QUE PEDÍA EL SEEDER
            $table->string('entidad')->nullable();
            $table->string('entidad_id')->nullable();

            // 4. DETALLES
            $table->string('descripcion')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();

            // 5. DATOS TÉCNICOS
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};

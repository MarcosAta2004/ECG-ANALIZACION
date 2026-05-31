<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id('auditoria_id');

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // acción realizada en el sistema
            $table->string('accion', 100);

            // módulo afectado (pacientes, estudios, etc.)
            $table->string('modulo', 100);

            $table->string('entidad')->nullable();
            $table->string('entidad_id')->nullable();

            $table->longText('descripcion')->nullable();

            // JSON de cambios
            $table->longText('valores_anteriores')->nullable();
            $table->longText('valores_nuevos')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
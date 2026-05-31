<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->increments('paciente_id');

            $table->unsignedInteger('prefijo_id');
            $table->foreign('prefijo_id')->references('prefijo_id')->on('prefijos_paciente');

            // Ej: PACIENTE-2025-001 — generado automáticamente
            $table->string('codigo_generado', 100)->unique();

            $table->date('fecha_nacimiento')->nullable();
            $table->char('sexo', 1)->nullable(); // M | F
            $table->float('peso')->nullable();   // Kilogramos

            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users');

            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
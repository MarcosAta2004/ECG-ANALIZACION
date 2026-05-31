<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudios', function (Blueprint $table) {
            $table->increments('estudio_id');

            $table->unsignedInteger('paciente_id');
            $table->foreign('paciente_id')->references('paciente_id')->on('pacientes');

            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users');

            // Edad al momento del estudio — se registra aquí porque cambia con el tiempo
            $table->unsignedTinyInteger('edad')->nullable();

            $table->text('observaciones')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudios');
    }
};
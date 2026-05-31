<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predicciones', function (Blueprint $table) {
            $table->increments('prediccion_id');

            $table->unsignedInteger('imagen_id');
            $table->foreign('imagen_id')->references('imagen_id')->on('imagenes');

            // Clase predicha por el modelo CNN-LSTM
            $table->unsignedInteger('ritmo_id');
            $table->foreign('ritmo_id')->references('ritmo_id')->on('ritmos_cardiacos');

            // Probabilidad de la clase ganadora — entre 0.0 y 1.0
            $table->float('probabilidad');

            // Milisegundos que tardó la inferencia
            $table->unsignedInteger('tiempo_ms')->nullable();

            // JSON con probabilidades del top de clases detectadas
            $table->json('top_predicciones')->nullable();

            // Etiqueta detectada por el modelo
            $table->string('label_detectado')->nullable();

            // Código de la etiqueta (ej: AFIB, NORM, STACH, AFLT)
            $table->string('label_code')->nullable();

            // Tipo de resultado (arritmia o normal)
            $table->string('tipo')->nullable();

            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predicciones');
    }
};
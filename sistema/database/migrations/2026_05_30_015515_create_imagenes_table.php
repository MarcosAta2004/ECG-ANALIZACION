<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('imagenes', function (Blueprint $table) {
            $table->increments('imagen_id');

            // UNIQUE garantiza la relación 1:1 con estudios
            $table->unsignedInteger('estudio_id')->unique();
            $table->foreign('estudio_id')->references('estudio_id')->on('estudios');

            // Ruta legible generada automaticamente: ecg/YYYYMMDD-HHMMSS.ext
            $table->string('ruta', 255);
            $table->string('formato', 50)->nullable();    // png | jpg | pdf
            $table->string('resolucion', 50)->nullable(); // Ej: 1425x548
            $table->unsignedInteger('tamano_kb')->nullable();

            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes');
    }
};

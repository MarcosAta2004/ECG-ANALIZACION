<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->increments('reporte_id');

            $table->unsignedInteger('estudio_id');
            $table->foreign('estudio_id')->references('estudio_id')->on('estudios');

            $table->unsignedBigInteger('generado_por');
            $table->foreign('generado_por')->references('id')->on('users');

            $table->text('resumen')->nullable();

            // Ruta en disco del PDF generado por Laravel con DomPDF
            $table->string('ruta_pdf', 255)->nullable();

            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
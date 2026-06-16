<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id('reporte_id'); // Cambiado a id() moderno

            // Relaciones protegidas (No permite borrar el estudio o al doctor si hay reporte)
            // 1. Creamos la columna exactamente como un Integer sin signo
            $table->unsignedInteger('estudio_id'); 

            // 2. Le asignamos la llave foránea protegiendo el borrado
            $table->foreign('estudio_id')
                ->references('estudio_id')
                ->on('estudios')
                ->restrictOnDelete();

            $table->foreignId('generado_por')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->text('resumen')->nullable();
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
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ritmos_cardiacos', function (Blueprint $table) {
            $table->increments('ritmo_id');

            $table->unsignedInteger('grupo_id');
            $table->foreign('grupo_id')->references('grupo_id')->on('grupos_cardiacos');

            $table->unsignedInteger('nivel_id');
            $table->foreign('nivel_id')->references('nivel_id')->on('niveles_gravedad');

            $table->unsignedInteger('clasificacion_id');
            $table->foreign('clasificacion_id')->references('clasificacion_id')->on('clasificaciones_arritmia');

            // Código SCP-ECG: NORM | AFIB | AFLT | PVC | PAC | 1AVB | STACH | WPW | SBRAD | PSVT | BIGU | SVARR | SARRH
            $table->string('label', 50)->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ritmos_cardiacos');
    }
};
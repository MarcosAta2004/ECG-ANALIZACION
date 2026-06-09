<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->increments('diagnostico_id');

            $table->unsignedInteger('estudio_id')->unique();
            $table->foreign('estudio_id')->references('estudio_id')->on('estudios');

            // Diagnóstico final registrado por el cardiólogo
            $table->unsignedInteger('ritmo_id');
            $table->foreign('ritmo_id')->references('ritmo_id')->on('ritmos_cardiacos');

            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users');

            // true = cardiólogo confirma la predicción | false = discrepa
            $table->boolean('concordancia')->nullable();
            
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_revision')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosticos');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prefijos_paciente', function (Blueprint $table) {
            $table->increments('prefijo_id');
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prefijos_paciente');
    }
};
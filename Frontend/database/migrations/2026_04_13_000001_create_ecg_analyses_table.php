<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecg_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->float('patient_age');
            $table->tinyInteger('patient_sex');   // 0=F, 1=M
            $table->float('patient_weight');
            $table->string('label');              // "normal ECG", "atrial fibrillation", etc.
            $table->string('label_code');         // "NORM", "AFIB", etc.
            $table->string('type');               // "normal" | "arritmia"
            $table->float('confidence');          // porcentaje 0-100
            $table->json('top_predictions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecg_analyses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecg_analyses', function (Blueprint $table) {
            $table->string('doctor_result')->nullable()->after('top_predictions'); // 'normal' | 'arritmia'
            $table->string('doctor_label')->nullable()->after('doctor_result');    // diagnóstico libre
            $table->text('doctor_notes')->nullable()->after('doctor_label');       // notas clínicas
            $table->timestamp('reviewed_at')->nullable()->after('doctor_notes');
        });
    }

    public function down(): void
    {
        Schema::table('ecg_analyses', function (Blueprint $table) {
            $table->dropColumn(['doctor_result', 'doctor_label', 'doctor_notes', 'reviewed_at']);
        });
    }
};

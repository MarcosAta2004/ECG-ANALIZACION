<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecg_analyses', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'ecg_analyses_user_created_at_idx');
            $table->index(['user_id', 'type'], 'ecg_analyses_user_type_idx');
            $table->index(['user_id', 'doctor_result'], 'ecg_analyses_user_doctor_result_idx');
            $table->index(['user_id', 'reviewed_at'], 'ecg_analyses_user_reviewed_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ecg_analyses', function (Blueprint $table) {
            $table->dropIndex('ecg_analyses_user_created_at_idx');
            $table->dropIndex('ecg_analyses_user_type_idx');
            $table->dropIndex('ecg_analyses_user_doctor_result_idx');
            $table->dropIndex('ecg_analyses_user_reviewed_at_idx');
        });
    }
};

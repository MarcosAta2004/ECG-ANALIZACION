<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecg_analyses', function (Blueprint $table) {
            $table->string('patient_identifier')->nullable()->after('filename');
        });
    }

    public function down(): void
    {
        Schema::table('ecg_analyses', function (Blueprint $table) {
            $table->dropColumn('patient_identifier');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_records', function (Blueprint $table) {
            // カラムが既に存在しないかチェックしながら追加（より安全な書き方）
            if (!Schema::hasColumn('care_records', 'body_temp')) {
                $table->decimal('body_temp', 3, 1)->nullable()->after('content');
            }
            if (!Schema::hasColumn('care_records', 'blood_pressure_high')) {
                $table->integer('blood_pressure_high')->nullable()->after('body_temp');
            }
            if (!Schema::hasColumn('care_records', 'blood_pressure_low')) {
                $table->integer('blood_pressure_low')->nullable()->after('blood_pressure_high');
            }
            if (!Schema::hasColumn('care_records', 'water_intake')) {
                $table->integer('water_intake')->nullable()->after('blood_pressure_low');
            }
        });
    }

    public function down(): void
    {
        Schema::table('care_records', function (Blueprint $table) {
            $table->dropColumn(['body_temp', 'blood_pressure_high', 'blood_pressure_low', 'water_intake']);
        });
    }
};
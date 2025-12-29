<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行（カラム追加）
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // client_nameの後に郵便番号を追加
            $table->string('postcode', 7)->after('client_name')->nullable();
            
            // contact_telの後に被保険者番号と認定期間を追加
            $table->string('insurace_number')->after('contact_tel')->nullable(); // 被保険者番号
            $table->date('care_start_date')->after('insurace_number')->nullable(); // 認定有効開始日
            $table->date('care_end_date')->after('care_start_date')->nullable();   // 認定有効終了日
            
            // care_managerの後にケアマネ連絡先を追加
            $table->string('care_manager_tel')->after('care_manager')->nullable(); // ケアマネ連絡先
        });
    }

    /**
     * マイグレーションのロールバック（カラム削除）
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'postcode',
                'insurace_number',
                'care_start_date',
                'care_end_date',
                'care_manager_tel'
            ]);
        });
    }
};
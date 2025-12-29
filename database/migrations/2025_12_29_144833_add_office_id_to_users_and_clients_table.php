<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * カラムの追加
     */
    public function up(): void
    {
        // usersテーブル（スタッフ）への追加
        Schema::table('users', function (Blueprint $table) {
            // officesテーブルのidと紐付けるカラム
            $table->foreignId('office_id')
                  ->nullable() // 既存ユーザーもいるため最初は空を許可
                  ->after('id') // IDのすぐ後ろに配置
                  ->constrained('offices') // officesテーブルのidであることを指定
                  ->onDelete('set null'); // 事業所が消えてもユーザーは残す
        });

        // clientsテーブル（利用者）への追加
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('office_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('offices')
                  ->onDelete('cascade'); // 事業所が消えたら利用者データも消す（運用に合わせて）
        });
    }

    /**
     * カラムの削除（ロールバック用）
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行する（テーブルを作る）
     */
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id(); // 自動連番のID
            $table->string('name'); // 事業所名
            $table->string('postcode', 7); // 郵便番号（ハイフンなし7桁想定）
            $table->string('address'); // 住所（Googleマップの起点になる重要な項目）
            $table->string('tel')->nullable(); // 電話番号（空でもOK）
            $table->timestamps(); // 作成日・更新日
        });
    }

    /**
     * マイグレーションを取り消す（テーブルを消す）
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};

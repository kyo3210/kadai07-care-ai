<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションの実行（テーブル作成）
     */
    public function up(): void
    {
        // 1. 利用者テーブル (clients)
        Schema::create('clients', function (Blueprint $table) {
            $table->string('id')->primary(); // ケアマネが入力するIDを主キーにする
            $table->string('client_name');
            $table->string('address');
            $table->string('contact_tel');
            $table->string('care_manager');
            $table->timestamps(); // 作成日・更新日のカラム
        });

        // 2. ケア記録テーブル (care_records)
        Schema::create('care_records', function (Blueprint $table) {
            $table->id(); // 自動連番の記録ID
            $table->string('client_id'); // clientsテーブルのidと紐づく
            $table->text('content');
            $table->string('recorded_by');
            $table->datetime('recorded_at');
            $table->timestamps();

            // 外部キー制約（client_idが必ず存在することを保証）
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * マイグレーションの取り消し（テーブル削除）
     */
    public function down(): void
    {
        Schema::dropIfExists('care_records');
        Schema::dropIfExists('clients');
    }
};
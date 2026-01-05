<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CareController;
use App\Http\Controllers\OfficeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http; // 郵便番号API用に追加

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. 初期リダイレクト
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 2. メインダッシュボード画面
Route::get('/dashboard', function () {
    return view('index'); 
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. Web-API ルート (JavaScriptからの非同期通信用)
Route::middleware(['auth'])->prefix('web-api')->group(function () {
    
    // --- 利用者(Client)関連 ---
    // 利用者一覧の取得 (GET)
    Route::get('/clients', [ClientController::class, 'index']); 
    // 利用者情報の新規保存・更新 (POST)
    Route::post('/clients', [ClientController::class, 'store']); 
    // 利用者情報の削除 (DELETE) ★追加
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
    
    // 全ケア記録の取得 (一覧表示用)
    Route::get('/all-records', [CareController::class, 'getAllRecords']);

    // 利用者検索
    Route::get('/search', [CareController::class, 'search']);

    // --- ケア記録(Record)関連 ---
    // ケア記録・バイタル情報の保存 (POST)
    Route::post('/records', [CareController::class, 'storeRecord']);
    
    // AIチャット (Gemini API 連携)
    Route::post('/ask-ai', [CareController::class, 'askAI']);

    // --- 事業所(Office)関連 ---
    // 事業所リストの取得 (GET)
    Route::get('/offices', [OfficeController::class, 'index']);
    // 事業所情報の更新 (POST) ★JS側の /offices/update と整合性をとりました
    Route::post('/offices/update', [OfficeController::class, 'update']);

    Route::get('/staff', [OfficeController::class, 'indexStaff']);
    Route::post('/staff', [OfficeController::class, 'storeStaff']);

    // --- 外部API連携 (CORS回避用プロキシ) ---
// 住所検索プロキシ
    Route::get('/zipcode/{zip}', function($zip) {
        return Http::get("https://zipcloud.ibsnet.co.jp/api/search?zipcode={$zip}")->json();
    });
});

// 4. プロフィール管理 (Breeze標準)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 5. 認証関連ルート (Breeze標準)
require __DIR__.'/auth.php';
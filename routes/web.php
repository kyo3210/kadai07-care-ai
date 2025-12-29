<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CareController;
use App\Http\Controllers\OfficeController;
use Illuminate\Support\Facades\Route;

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
    // ★重要: 利用者情報の新規保存 (POST) - これがないと保存時にMethodNotAllowedエラーになります
    Route::post('/clients', [ClientController::class, 'store']); 
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
    // 事業所情報の更新 (POST)
    Route::post('/office/update', [OfficeController::class, 'update']);
});

// 4. プロフィール管理 (Breeze標準)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 5. 認証関連ルート (Breeze標準)
require __DIR__.'/auth.php';


<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CareController;

// auth ミドルウェアに変更することで、Breezeのログインセッションをそのまま利用できます
Route::middleware('auth')->group(function () {
    
    // 利用者関連
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);

    // ケア業務・AI関連
    Route::post('/ask-ai', [CareController::class, 'askAI']);
    Route::post('/records', [CareController::class, 'storeRecord']);
    Route::get('/search', [CareController::class, 'search']);
});
<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatbotController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatbotController::class, 'sendMessage'])->name('chat.send');

// Proteksi request per user agar tidak overloading (opsional)
// Membatasi maksimal 5 request per 1 menit untuk setiap IP Address
Route::post('/chat/send', [ChatbotController::class, 'sendMessage'])
    ->name('chat.send')
    ->middleware('throttle:5,1');

    Route::get('/chat/suggestions', [ChatbotController::class, 'getSuggestions'])->name('chat.suggestions');

<?php

use Illuminate\Support\Facades\Route;
Route::prefix('filament-transcribe')->middleware(['web'])->group(function () {
    Route::get('/ping', fn () => response()->json(['ok' => true]))->name('filament-transcribe.ping');
});

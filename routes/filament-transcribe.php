<?php

use Illuminate\Support\Facades\Route;
use Visualbuilder\FilamentTranscribe\Http\Controllers\RecordingController;

Route::prefix('filament-transcribe')->middleware(['web', 'auth'])->group(function () {
    Route::post('/recordings', [RecordingController::class, 'store'])->name('filament-transcribe.recordings.store');
    Route::get('/ping', fn () => response()->json(['ok' => true]))->name('filament-transcribe.ping');
});

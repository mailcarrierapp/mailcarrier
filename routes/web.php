<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use MailCarrier\Http\Controllers\LogController;
use MailCarrier\Http\Controllers\MailCarrierController;

Route::middleware('auth:' . Config::get('filament.auth.guard'))->group(function () {
    Route::get('preview/logs/{log}', [LogController::class, 'preview'])->name('logs.preview');
    Route::get('attachment/{attachment}', [MailCarrierController::class, 'downloadAttachment'])->name('download.attachment');
});

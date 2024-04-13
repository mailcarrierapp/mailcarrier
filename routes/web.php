<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use MailCarrier\Http\Controllers\LogController;
use MailCarrier\Http\Controllers\MailCarrierController;
use MailCarrier\Http\Controllers\SocialAuthController;
use MailCarrier\Http\Controllers\TemplateController;

Route::middleware(['web', 'auth:' . Config::get('filament.auth.guard')])->group(function () {
    Route::get('logs/{log}/preview', [LogController::class, 'preview'])->name('logs.preview');
    Route::get('attachment/{attachment}', [MailCarrierController::class, 'downloadAttachment'])
        ->whereUuid('attachment')
        ->name('download.attachment');
});

Route::prefix('auth')->middleware(['web', 'guest'])->group(function () {
    Route::get('redirect', [SocialAuthController::class, 'redirect'])->name('auth.redirect');
    Route::get('callback', [SocialAuthController::class, 'callback'])->name('auth.callback');
});

Route::get('templates/preview', [TemplateController::class, 'preview'])->name('templates.preview');

Route::middleware('web')->group(function () {
    Route::get('templates/preview2', [TemplateController::class, 'preview2']);
});


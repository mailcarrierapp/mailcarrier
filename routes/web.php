<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use MailCarrier\Http\Controllers\LogController;
use MailCarrier\Http\Controllers\MailCarrierController;
use MailCarrier\Http\Controllers\SocialAuthController;
use MailCarrier\Http\Controllers\WebhookController;
use MailCarrier\Livewire\PreviewTemplate;

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

Route::middleware('web')->group(function () {
    Route::get('templates/preview', PreviewTemplate::class)->name('templates.preview');
});

Route::post('webhook', WebhookController::class)->name('webhook');
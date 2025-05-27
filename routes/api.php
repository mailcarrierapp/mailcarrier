<?php

use Illuminate\Support\Facades\Route;
use MailCarrier\Http\Controllers\MailCarrierController;
use MailCarrier\Http\Controllers\WebhookController;
use MailCarrier\Http\Middleware\ForceJsonRequest;

Route::prefix('api')->middleware(ForceJsonRequest::class)->group(function () {
    Route::post('webhook', WebhookController::class)->name('mailcarrier.webhook.process');
    Route::post('send', [MailCarrierController::class, 'send'])->name('mailcarrier.send');
});

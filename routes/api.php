<?php

use Illuminate\Support\Facades\Route;
use MailCarrier\Http\Controllers\MailCarrierController;
use MailCarrier\Http\Middleware\ForceJsonRequest;

Route::prefix('api')->middleware(ForceJsonRequest::class)->group(function () {
    Route::post('send', [MailCarrierController::class, 'send'])->name('mailcarrier.send');
});

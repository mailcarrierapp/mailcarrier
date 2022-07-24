<?php

use Illuminate\Support\Facades\Route;
use MailCarrier\Http\Controllers\MailCarrierController;

Route::post('send', [MailCarrierController::class, 'send'])->name('send');

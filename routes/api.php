<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WahaWebhookController;

Route::post('/waha/webhook', [WahaWebhookController::class, 'handle']);
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthReportController;

Route::middleware('api')->group(function () {
    Route::post('/health-report', [HealthReportController::class, 'store'])
        ->middleware(['monitored-instance', 'throttle:60,1'])
        ->name('health.report');
    Route::get('/health-status', [HealthReportController::class, 'show'])
        ->middleware(['monitored-instance', 'throttle:60,1'])
        ->name('health.status');
});

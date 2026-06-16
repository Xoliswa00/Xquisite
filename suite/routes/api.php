<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthReportController;

Route::middleware('api')->group(function () {
    Route::post('/health-report', [HealthReportController::class, 'store'])->name('health.report');
    Route::get('/health-status', [HealthReportController::class, 'show'])->name('health.status');
});

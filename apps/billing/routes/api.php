<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ModuleSubscriptionController;
use App\Http\Controllers\Api\LeaseSubscriptionController;
use App\Http\Controllers\Api\LogBridgeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Internal API — called by other Xquisite apps (e.g. suite)
// Protected by X-Internal-Key header
Route::middleware('internal.api')->prefix('internal')->group(function () {
    Route::post('module-subscriptions', [ModuleSubscriptionController::class, 'store']);
    Route::post('module-subscriptions/cancel', [ModuleSubscriptionController::class, 'cancel']);
    Route::get('logs', [LogBridgeController::class, 'index']);
    Route::post('lease-subscriptions', [LeaseSubscriptionController::class, 'store']);
    Route::post('lease-subscriptions/cancel', [LeaseSubscriptionController::class, 'cancel']);
});

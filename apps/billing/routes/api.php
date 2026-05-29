<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ModuleSubscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Internal API — called by other Xquisite apps (e.g. suite)
// Protected by X-Internal-Key header
Route::middleware('internal.api')->prefix('internal')->group(function () {
    Route::post('module-subscriptions', [ModuleSubscriptionController::class, 'store']);
    Route::post('module-subscriptions/cancel', [ModuleSubscriptionController::class, 'cancel']);
});

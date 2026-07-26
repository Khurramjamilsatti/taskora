<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\EstimateController;
use App\Http\Controllers\Api\FormSubmissionController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/site', [SiteController::class, 'show']);
Route::get('/catalogue', [CatalogueController::class, 'show']);
Route::post('/estimate', [EstimateController::class, 'calculate']);

Route::post('/register/customer', [AuthController::class, 'registerCustomer']);
Route::post('/register/provider', [AuthController::class, 'registerProvider']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forms/{type}', [FormSubmissionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/my-forms', [FormSubmissionController::class, 'mine']);
    Route::get('/booking-requests', [FormSubmissionController::class, 'bookingRequests']);
});

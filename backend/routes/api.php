<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EstimateController;
use App\Http\Controllers\Api\FormSubmissionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
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

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'changePassword']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'mine']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);

    Route::get('/booking-requests', [BookingController::class, 'openRequests']);
    Route::get('/provider/jobs', [BookingController::class, 'myJobs']);
    Route::post('/bookings/{booking}/accept', [BookingController::class, 'accept']);
    Route::post('/bookings/{booking}/propose', [BookingController::class, 'propose']);
    Route::post('/bookings/{booking}/accept-quote', [BookingController::class, 'acceptQuote']);
    Route::post('/bookings/{booking}/start', [BookingController::class, 'start']);
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{booking}/feedback', [BookingController::class, 'feedback']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::get('/bookings/{booking}/messages', [ChatController::class, 'index']);
    Route::post('/bookings/{booking}/messages', [ChatController::class, 'store']);
});

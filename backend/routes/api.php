<?php

use App\Http\Controllers\Api\EstimateController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/site', [SiteController::class, 'show']);
Route::post('/estimate', [EstimateController::class, 'calculate']);

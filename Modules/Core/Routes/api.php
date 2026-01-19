<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Core Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Core module.
| These routes are loaded by the CoreServiceProvider.
|
*/

Route::post('/core/login', [AuthController::class, 'login']);


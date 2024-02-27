<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RekapController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'Api', 'prefix' => 'v1'], function () {
    Route::post('login', [AuthController::class, 'store']);
    Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth:api');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('saldo-kas', [RekapController::class, 'saldo_kas_besar']);
    });
  });

<?php

use Illuminate\Support\Facades\Route;
use Voice\JsonAuthorization\App\Http\Controllers\AuthorizableModelController;
use Voice\JsonAuthorization\App\Http\Controllers\AuthorizableSetTypeController;
use Voice\JsonAuthorization\App\Http\Controllers\AuthorizationRuleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::apiResource('authorizable-models', AuthorizableModelController::class)->only(['index', 'show']);
        Route::apiResource('authorization-rules', AuthorizationRuleController::class);
        Route::apiResource('authorizable-set-types', AuthorizableSetTypeController::class);
    });

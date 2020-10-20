<?php

use Illuminate\Support\Facades\Route;

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

Route::namespace('Voice\JsonAuthorization\App\Http\Controllers')
    ->prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::apiResource('authorization-rules', 'AuthorizationRuleController');
        Route::apiResource('authorizable-models', 'AuthorizableModelController');
        Route::apiResource('authorizable-set-types', 'AuthorizableSetTypeController');
    });

<?php

use App\Http\Controllers\Api\SelectOptionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'select-option',
    'as' => 'select-option',
], function () {
    Route::get('districts', [SelectOptionController::class, 'getDistricts']);
    Route::get('sub-districts', [SelectOptionController::class, 'getSubDistricts']);
    Route::get('health-centers', [SelectOptionController::class, 'getHealthCenter']);
    Route::get('genders', [SelectOptionController::class, 'getGenders']);
    Route::get('cluster', [SelectOptionController::class, 'getClusters']);
    Route::get('targets', [SelectOptionController::class, 'getTargets']);
    Route::get('services', [SelectOptionController::class, 'getServices']);
});

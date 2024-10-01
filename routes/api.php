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

Route::group([
    'prefix' => 'data',
    'as' => 'data',
], function () {
    Route::get('list-kunjungan', [\App\Http\Controllers\Api\MainController::class, 'listKunjungan']);
    Route::get('total-terlayani', [\App\Http\Controllers\Api\MainController::class, 'totalTerlayani']);
    Route::get('summary-kunjungan', [\App\Http\Controllers\Api\MainController::class, 'summaryKunjungan']);
    Route::get('peoples', [\App\Http\Controllers\Api\MainController::class, 'summaryPeoples']);
    Route::get('sasaran-terlayani', [\App\Http\Controllers\Api\MainController::class, 'sasaranTerlayani']);
    Route::get('sasaran-puskesmas-terlayani', [\App\Http\Controllers\Api\MainController::class, 'sasaranPuskesmasTerlayani']);
    Route::get('morbiditas', [\App\Http\Controllers\Api\MainController::class, 'morbiditas']);
});

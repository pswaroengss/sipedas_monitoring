<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\App\Http\Controllers\DashboardController;

Route::group([
    'controller' => DashboardController::class,
    'middleware' => ['web', 'central.auth'],
], function () {
    Route::get('/dashboard', 'index')->name('dashboard.index');
    Route::get('/dashboard/data', 'data')->name('dashboard.data');
    Route::get('/modules/dashboard/dashboard.js', 'dashboardJs')->name('dashboard.asset.js');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Monitoring\App\Http\Controllers\MonitoringController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'controller' => MonitoringController::class,
    'middleware' => ['web', 'central.auth'],
], function () {
    Route::get('/monitoring', 'index')->name('monitoring.index');
    Route::post('/monitoring/process', 'process')->name('monitoring.process');
    Route::get('/monitoring/result', 'result')->name('monitoring.result');
    Route::get('/get_waroeng_penjualan', 'getWaroengPenjualan')->name('monitoring.waroeng');
    Route::get('/monitoring/kategori', 'getKategoriList')->name('monitoring.kategori');
    Route::get('/monitoring/fokus', 'getFokusByKategori')->name('monitoring.fokus');
    Route::get('/modules/monitoring/monitoring.js', 'monitoringJs')->name('monitoring.asset.js');
});

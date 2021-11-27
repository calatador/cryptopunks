<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
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


Route::get('/',[AssetController::class, 'welcome']);

Route::get('/cryptopunks',[AssetController::class, 'cryptopunks']);
Route::get('/cryptopunk',[AssetController::class, 'cryptopunk']);
Route::get('/list',[AssetController::class, 'listing']);
Route::get('/month',[AssetController::class, 'month']);


Route::get('/test',[AssetController::class, 'test']);
Route::get('/history',[AssetController::class, 'history']);



// cron jobs
Route::get('/cron/assets',[\App\Http\Controllers\CronController::class, 'syncAssets']);
Route::get('/cron/history',[\App\Http\Controllers\CronController::class, 'syncHistory']);
Route::get('/cron/prices',[\App\Http\Controllers\CronController::class, 'syncPrice']);









<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HighChartController;

use App\Charts\SampleChart;
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

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::post('/store', [HomeController::class, 'store'])->name('store');
Route::post('/destroy', [HomeController::class, 'destroy'])->name('destroy');
Route::get('/tag_destroy/{id}', [HomeController::class, 'tag_destroy'])->name('tag_destroy');


Route::get('/edit/{id}', [HomeController::class, 'edit'])->name('edit');
Route::post('/update', [HomeController::class, 'update'])->name('update');

Route::get('/submit/{id}', [HomeController::class, 'submit'])->name('submit');
Route::post('/enroll', [HomeController::class, 'enroll'])->name('enroll');

Route::get('charts.sample_chart', [SampleChart::class, 'charts.sample_chart'])->name('charts.sample_chart');

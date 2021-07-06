<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware'=>['auth']], function(){
    Route::group(['prefix'=>'mark'], function(){
        Route::get('/create', [App\Http\Controllers\MarkController::class, 'create'])->name('mark.create');
        Route::get('/index', [App\Http\Controllers\MarkController::class, 'index'])->name('mark.index');
        Route::post('/store', [App\Http\Controllers\MarkController::class, 'store'])->name('mark.store');
        Route::get('/edit/{mark}', [App\Http\Controllers\MarkController::class, 'edit'])->name('mark.edit');
        Route::post('/update/{mark}', [App\Http\Controllers\MarkController::class, 'update'])->name('mark.update');
        Route::post('/destroy/{mark}', [App\Http\Controllers\MarkController::class, 'destroy'])->name('mark.destroy');
    });
    Route::group(['prefix'=>'company'], function(){
        Route::get('/create', [App\Http\Controllers\CompanyController::class, 'create'])->name('company.create');
        Route::get('/index', [App\Http\Controllers\CompanyController::class, 'index'])->name('company.index');
        Route::post('/store', [App\Http\Controllers\CompanyController::class, 'store'])->name('company.store');
        Route::get('/edit/{company}', [App\Http\Controllers\CompanyController::class, 'edit'])->name('company.edit');
        Route::post('/update/{company}', [App\Http\Controllers\CompanyController::class, 'update'])->name('company.update');
        Route::post('/destroy/{company}', [App\Http\Controllers\CompanyController::class, 'destroy'])->name('company.destroy');
    });
});

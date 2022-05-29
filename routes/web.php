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
        Route::get('/data', [App\Http\Controllers\CompanyController::class, 'data'])->name('company.data');
        Route::get('/create', [App\Http\Controllers\CompanyController::class, 'create'])->name('company.create');
        Route::get('/index', [App\Http\Controllers\CompanyController::class, 'index'])->name('company.index');
        Route::post('/store', [App\Http\Controllers\CompanyController::class, 'store'])->name('company.store');
        Route::get('/edit/{company}', [App\Http\Controllers\CompanyController::class, 'edit'])->name('company.edit');
        Route::post('/update/{company}', [App\Http\Controllers\CompanyController::class, 'update'])->name('company.update');
        Route::post('/destroy/{company}', [App\Http\Controllers\CompanyController::class, 'destroy'])->name('company.destroy');
    });
    Route::group(['prefix'=>'product'], function(){
        Route::get('/create', [App\Http\Controllers\ProductController::class, 'create'])->name('product.create');
        Route::get('/index', [App\Http\Controllers\ProductController::class, 'index'])->name('product.index');
        Route::post('/store', [App\Http\Controllers\ProductController::class, 'store'])->name('product.store');
        Route::get('/edit/{product}', [App\Http\Controllers\ProductController::class, 'edit'])->name('product.edit');
        Route::post('/update/{product}', [App\Http\Controllers\ProductController::class, 'update'])->name('product.update');
        Route::post('/destroy/{product}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('product.destroy');
    });
    Route::group(['prefix'=>'order'], function(){
        Route::get('/data', [App\Http\Controllers\OrderController::class, 'data'])->name('order.data');
        Route::get('/create', [App\Http\Controllers\OrderController::class, 'create'])->name('order.create');
        Route::get('/index', [App\Http\Controllers\OrderController::class, 'index'])->name('order.index');
        Route::post('/store', [App\Http\Controllers\OrderController::class, 'store'])->name('order.store');
        Route::get('/edit/{order}', [App\Http\Controllers\OrderController::class, 'edit'])->name('order.edit');
        Route::post('/update/{order}', [App\Http\Controllers\OrderController::class, 'update'])->name('order.update');
        Route::post('/destroy/{order}', [App\Http\Controllers\OrderController::class, 'destroy'])->name('order.destroy');
    });
    Route::group(['prefix'=>'pair'], function(){
        Route::get('/create', [App\Http\Controllers\PairController::class, 'create'])->name('pair.create');
        Route::get('/index', [App\Http\Controllers\PairController::class, 'index'])->name('pair.index');
        Route::post('/store', [App\Http\Controllers\PairController::class, 'store'])->name('pair.store');
    });
});

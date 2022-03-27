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

Route::name('books.')->controller(\App\Http\Controllers\BookController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::delete('/{book}', 'destroy')->name('destroy')->whereNumber('book');
    Route::name('pages.')->prefix('{book}')->controller(\App\Http\Controllers\PageController::class)->group(function () {
        Route::get('/', 'index')->name('index')->whereNumber('book');
        Route::post('/', 'store')->name('store')->whereNumber('book');
        Route::get('/{page}', 'show')->name('show')->whereNumber('book');
        Route::delete('/{page}', 'destroy')->name('destroy')->whereNumber('book');
        Route::put('/{page}', 'update')->name('update')->whereNumber('book');
        Route::post('/{page}/last-text', 'lastText')->name('last-text')->whereNumber('book');
    });
});

Route::prefix('phrases')->name('phrases.')->controller(\App\Http\Controllers\PhraseController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{phrase}', 'edit')->name('edit');
    Route::put('/{phrase}', 'update')->name('update');
    Route::post('/', 'store')->name('store');
    Route::post('/extract', 'extract')->name('extract');
});



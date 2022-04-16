<?php

use Illuminate\Http\Request;
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
Route::inertia('/login', 'Login')->name('login');
Route::post('/login', function (Request $request){
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);
    $credentials = $request->only(['email', 'password']);
    if (Auth::attempt($credentials)) {
        return redirect()->intended('/');
    }
    return redirect()->back()->withInput($request->only('email'))->withErrors([
        'email' => 'These credentials do not match our records.'
    ]);
});
Route::post('/logout', function (){
    Auth::logout();
    return redirect('/login');
})->middleware('auth')->name('logout');

Route::inertia('/', 'Home')->name('home')->middleware('auth');

Route::prefix('books')->name('books.')->middleware(['auth', 'can:books.read'])->controller(\App\Http\Controllers\BookController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->middleware('can:books.create')->name('store');
    Route::delete('/{book}', 'destroy')->middleware('can:books.delete,book')->name('destroy');
    Route::name('pages.')->prefix('{book}/pages')->controller(\App\Http\Controllers\PageController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store')->middleware('can:pages.create');
        Route::get('/{page}', 'show')->name('show')->middleware('can:pages.read,page');
        Route::delete('/{page}', 'destroy')->name('destroy')->middleware('can:pages.delete,page');
        Route::put('/{page}', 'update')->name('update')->middleware('can:pages.update,page');
        Route::post('/{page}/last-text', 'lastText')->name('last-text')->middleware('can:pages.update,page');
        Route::post('/{page}/status', 'setStatus')->name('status')->middleware('can:pages.update,page');
    });
});

Route::prefix('phrases')->middleware('auth')->name('phrases.')->controller(\App\Http\Controllers\PhraseController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/{phrase}', 'edit')->name('edit');
    Route::put('/{phrase}', 'update')->name('update');
    Route::delete('/{phrase}', 'destroy')->name('destroy');
    Route::post('/extract', 'extract')->name('extract');
});

Route::middleware('can:users')->resource('users', \App\Http\Controllers\UserController::class);
Route::prefix('permissions')->name('permissions.')->middleware('can:permissions')->controller(\App\Http\Controllers\PermissionController::class)->group(function (){
    Route::post('pages/{page}', 'pageSetUsersPermission')->name('page.set.users');
    Route::get('pages/{page}', 'pageGetUsersPermission')->name('page.get.users');
});

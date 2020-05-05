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

Route::group(['prefix' => 'payments', 'middleware' => ['auth'], 'as' => 'payments.'], function() {
    Route::get('create', 'PaymentsController@create')->name('create');
    Route::post('/', 'PaymentsController@store')->name('store');
});



Auth::routes();


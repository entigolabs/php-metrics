<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/red', '\App\Http\Controllers\EndpointController@red');

Route::get('/green', '\App\Http\Controllers\EndpointController@green');

Route::get('/blue', '\App\Http\Controllers\EndpointController@blue');

Route::get('/metrics', '\App\Http\Controllers\EndpointController@metrics');

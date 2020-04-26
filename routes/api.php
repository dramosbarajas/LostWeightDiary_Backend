<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/***************************************
 * * Rutas de usuario
 **************************************/

Route::post('login', 'User\UserController@login')->name('login');
Route::post('register', 'User\UserController@register')->name('register');
Route::get('forgot/{correo}', 'User\UserController@forgotPassword')->name('forgot');
Route::post('recover', 'User\UserController@recoverPassword')->name('recover');
Route::get('verify/{token}', 'User\UserController@verifyToken')->name('verify');

Route::middleware('auth:api')->get('logout', 'User\UserController@logout')->name('logout');
Route::middleware('auth:api')->post('change', 'User\UserController@changePassword')->name('change');

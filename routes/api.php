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

Route::post('login', 'User\UserController@login');
Route::post('register', 'User\UserController@register');
Route::post('forgot', 'User\UserController@forgotPassword');
Route::post('recover', 'User\UserController@recoverPassword');
Route::name('verify')->get('verify/{token}', 'User\UserController@verifyToken');

Route::get('logout', 'User\UserController@logout')->middleware('auth:api');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

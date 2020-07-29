<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'API\userController@login');

Route::get('dashboard/{id}','API\dashboardController@getdashboard');

Route::post('user/product/new','API\productController@create');
Route::post('user/product/update/{id}','API\productController@update');
Route::get('user/product/show/{id}','API\productController@show');
Route::delete('user/product/delete/{id}','API\productController@delete');

Route::post('user/knock/new/{id}','API\knockController@create');
Route::post('user/knock/approve/{id}','API\knockController@approve');
Route::post('user/knock/promote/{id}','API\knockController@promote');
Route::post('user/knock/demote/{id}','API\knockController@demote');
Route::post('user/knock/block/{id}','API\knockController@block');

Route::post('user/notification/new','API\notificationController@create');
Route::post('user/notification/update/{id}','API\notificationController@update');
Route::get('user/notification/show/{id}','API\notificationController@show');
Route::delete('user/notification/delete/{id}','API\notificationController@delete');

Route::post('user/cart/new','API\cartController@create');
Route::post('user/cart/update/{id}','API\cartController@update');
Route::get('user/cart/show/{id}','API\cartController@show');
Route::delete('user/cart/delete/{id}','API\cartController@delete');
Route::delete('user/cart/deleteByUserID/{id}','API\cartController@deleteByUserid');

Route::post('user/order/new','API\orderController@create');
Route::post('user/order/update/{id}','API\orderController@update');
Route::get('user/order/show/{id}','API\orderController@show');
Route::delete('user/order/delete/{id}','API\orderController@delete');

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

Route::post('user/knock/new/customer/{id}','API\customerKnockController@create');
Route::post('user/knock/approve/customer/{id}','API\customerKnockController@approve');
Route::post('user/knock/reject/customer/{id}','API\customerKnockController@reject');
Route::get('user/knock/show/customer/{id}','API\customerKnockController@show');

Route::post('user/relation/update/customer/{id}','API\customerRelationshipController@update');
Route::post('user/relation/block/customer/{id}','API\customerRelationshipController@block');
Route::get('user/relation/show/customer/{id}','API\customerRelationshipController@show');
Route::get('user/relation/show/seller/productlist/customer','API\customerRelationshipController@productlist');

Route::post('user/knock/new/agent/{id}','API\agentKnockController@create');
Route::post('user/knock/approve/agent/{id}','API\agentKnockController@approve');
Route::post('user/knock/reject/agent/{id}','API\agentKnockController@reject');
Route::get('user/knock/show/agent/{id}','API\agentKnockController@show');

Route::post('user/relation/update/agent/{id}','API\agentRelationshipController@update');
Route::post('user/relation/block/agent/{id}','API\agentRelationshipController@block');
Route::get('user/relation/show/agent/{id}','API\agentRelationshipController@show');
Route::get('user/relation/show/seller/productlist/agent','API\agentRelationshipController@productlist');

Route::post('user/request/new/agent','API\custAgentRequestController@create');
Route::post('user/request/approve/agent/{id}','API\custAgentRequestController@approve');
Route::post('user/request/reject/agent/{id}','API\custAgentRequestController@reject');
Route::get('user/request/show/agent/{id}','API\custAgentRequestController@show');

Route::post('user/relation/block/agentcustomer/{id}','API\custAgentRelationshipController@block');
Route::get('user/relation/show/agentcustomer/{id}','API\custAgentRelationshipController@show');

Route::post('user/seller/new/employee','API\employeeSellerRelationshipController@createEmployee');
Route::post('user/seller/block/employee/{id}','API\employeeSellerRelationshipController@block');
Route::get('user/seller/show/employee/{id}','API\employeeSellerRelationshipController@show');

Route::post('user/product/new','API\productController@create');
Route::post('user/product/upload','API\productController@upload');
Route::post('user/product/update/{id}','API\productController@update');
Route::get('user/product/show/{id}','API\productController@show');
Route::delete('user/product/delete/{id}','API\productController@delete');

Route::post('user/notification/new','API\notificationController@create');
Route::post('user/notification/update/{id}','API\notificationController@update');
Route::get('user/notification/show/{id}','API\notificationController@show');
Route::delete('user/notification/delete/{id}','API\notificationController@delete');

Route::post('user/cart/new','API\cartController@create');
Route::get('user/cart/show/{id}','API\cartController@show');
Route::get('user/cart/check/{id}','API\cartController@check');
Route::get('user/cart/count/{id}','API\cartController@count');
Route::delete('user/cart/delete/{id}','API\cartController@delete');
Route::delete('user/cart/delete/all/{id}','API\cartController@deleteByUserid');

Route::post('user/order/request/new','API\orderController@createRequest');
Route::get('user/order/request/show/{id}','API\orderController@showRequest');
Route::post('user/order/request/accept/{id}','API\orderController@acceptRequest');
Route::post('user/order/request/reject/{id}','API\orderController@rejectRequest');
Route::get('user/order/show/{id}','API\orderController@showOrders');
Route::get('user/order/show/past/{id}','API\orderController@showPastOrders');

Route::post('user/chat/new','API\chatController@create');
Route::post('user/chat/update/{id}','API\chatController@update');
Route::get('user/chat/show/{id}','API\chatController@show');
Route::delete('user/chat/delete/{id}','API\chatController@delete');

Route::post('user/history/new','API\historyController@create');
Route::post('user/history/update/{id}','API\historyController@update');
Route::get('user/history/show/{id}','API\historyController@show');
Route::delete('user/history/delete/{id}','API\historyController@delete');

Route::post('user/payment/new','API\paymentController@create');
Route::post('user/payment/update/{id}','API\paymentController@update');
Route::get('user/payment/show/{id}','API\paymentController@show');
Route::delete('user/payment/delete/{id}','API\paymentController@delete');

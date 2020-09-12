<?php
use App\User;
use App\Product;
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
Route::get('/admin','AdminController@admin');
Route::post('/admin','AdminController@login');
Route::middleware([AdminCheck::class])->group(function () {
    route::get('/logout','AdminController@logout');
    route::get('/admin/owner','ownersController@show');
    route::post('/update/seller','ownersController@update');
    route::post('/SellerAdd','ownersController@create');
    route::get('/seller/delete/{uid}','ownersController@delete');
    route::get('/seller/accounts/{sid}','ownersController@accounts');
    route::post('/admin/seller/employee','ownersController@AddEmployee');
    route::post('/admin/seller/update/employee','ownersController@updateEmployee');

    route::get('/admin/customer','custController@show');
});

Route::get('/manufacture',function(){
    $city=\DB::table('citys')->get();
    return view('Registration',['citys'=>$city]);
});
Route::post('/manufacture','manufactureController@register');
route::get('/confirmMob',function(){
    return view('confirmMob');
});
Route::get('/login',function(){
    return view('login');
});
Route::post('/login','manufactureController@login');
Route::post('/confirmMob','manufactureController@dashboard');
Route::get('/mobileCheck','manufactureController@mobCheck');

Route::middleware([manufacture::class])->group(function () {
    Route::get('/manufacture/index','manufactureController@index');
    route::get('/mlogout','manufactureController@logout');
    Route::get('/manufacture/orders','manufactureController@orders');
    Route::get('/manufacture/orders/show/{id}','manufactureController@fullorder');
    Route::get('/manufacture/Products','manufactureController@Products');
});

Route::get('product/{name}', 'ImagesController@productPicture');

Route::get('watermark/{name}', 'ImagesController@watermarkPicture');
route::get('/f',function(){
    return view('firebase');
});
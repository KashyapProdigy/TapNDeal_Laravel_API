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

Route::get('/krishna', function () {
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
});

Route::get('product/{name}', 'ImagesController@productPicture');

Route::get('watermark/{name}', 'ImagesController@watermarkPicture');

<?php
use App\User;
use App\Product;
use Illuminate\Support\Facades\Route;
use App\Notifications\onesignal;
use Illuminate\Notifications\Notifiable;

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
    route::get('/admin/users','AdminController@users');
    route::get('changeType','AdminController@changeType');
    route::get('/admin/reference','AdminController@reference');
    route::get('/admin/refered/users/{ref}','AdminController@refUser');
    route::get('/admin/owner','ownersController@show');
    route::get('/admin/products','ownersController@Products');
    Route::get('/admin/products/delete/{pid}','ownersController@deletepro');
    Route::get('/admin/products/enable/{pid}','ownersController@enable');
    Route::get('/admin/products/disable/{pid}','ownersController@disable');
    Route::post('/admin/products/add','importExcel@import');
    Route::post('/admin/images/add','manufactureController@addImages');
    route::post('/update/seller','ownersController@update');
    route::post('/SellerAdd','ownersController@create');
    route::get('/seller/delete/{uid}','ownersController@delete');
    route::get('/seller/accounts/{sid}','ownersController@accounts');
    route::post('/admin/seller/employee','ownersController@AddEmployee');
    route::post('/admin/seller/update/employee','ownersController@updateEmployee');
    route::get('/admin/customer','custController@show');
    route::get('/admin/agents','agentController@show');
    route::post('/admin/agents/add','agentController@create');
    route::get('admin/orders','orderController@showAll');
    Route::get('admin/orders/show/{id}','orderController@fullorder');
    route::get('admin/payments','AdminController@payments');
    route::get('admin/feedbacks','AdminController@feedbacks');
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
    Route::get('/manufacture/Products/delete/{pid}','manufactureController@delete');
    Route::get('/manufacture/Products/enable/{pid}','manufactureController@enable');
    Route::get('/manufacture/Products/disable/{pid}','manufactureController@disable');
    Route::get('/manufacture/accounts','accounts@sellerAccounts');
    Route::post('/manufacture/emp/add','accounts@empAdd');
    Route::post('/manufacture/emp/edit','accounts@empEdit');
    Route::get('/manufacture/emp/delete/{id}','accounts@empDelete');
    Route::post('/manufacture/products/add','importExcel@import');
    Route::post('/manufacture/images/add','manufactureController@addImages');
});

Route::get('product/{name}', 'ImagesController@productPicture');
Route::get('Banner/{name}', 'ImagesController@bannerPicture');
Route::get('profile/{name}', 'ImagesController@profilePicture');
Route::get('watermark/{name}', 'ImagesController@watermarkPicture');
Route::get('n',function(){
    $usr=User::whereIn('id',[1])->get();
    $data['title']='Testing';
    $data['msg']="msg";
    Notification::send($usr, new onesignal($data));
    // $data['id']=$usr->id;
    // $data['msg']="hello";
    // \onesignal::sendNoti($data);
    dd('abc');
});
Route::get('pdf/{name}','ImagesController@pdf');

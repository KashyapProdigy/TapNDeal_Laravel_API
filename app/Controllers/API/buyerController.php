<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user;
use App\Order;

class buyerController extends Controller
{
    public function orderCount($cust_id)
    {
        $o_co=Order::where('cust_id',$cust_id)->count();
        return response()->json(['error' => false ,'count'=>$o_co],200);
    }
}

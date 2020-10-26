<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;

class orderController extends Controller
{
    public function showAll(Type $var = null)
    {
        $orders=Order::join('users','users.id','cust_id')
        ->join('order_status','order_status.id','orders.status_id')
        ->select('orders.*','order_status.*','orders.id as oid','users.*')->get();
        return view('Admin.orders',['list'=>$orders]);
    }
    public function fullorder($id)
    {
        $orders=Order::where('orders.id',$id)
        ->join('users','users.id','cust_id')
        ->select('orders.products','orders.agent_reference','orders.total_price','users.*')->first();
        return view('Admin.FullOrder',['ord'=>$orders]);
    }
}

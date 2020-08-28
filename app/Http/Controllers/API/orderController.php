<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Order;
use Validator;

class orderController extends Controller
{
    public function show($id)
    {
        $order=Order::where('id',$id)->get()->toarray();
        if(!empty($order))
        {
            return response()->json(['error' => false ,'data'=>$order],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'product_id' => 'required',
            'seller_id' => 'required',
            'customer_id' => 'required',
            'qty'=>'required',
            'total_amount'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $order=new Order;
        $order->product_id=$req->product_id;
        $order->seller_id=$req->seller_id;
        $order->customer_id=$req->customer_id;
        $order->qty=$req->qty;
        $order->total_amount=$req->total_amount;
        $order->status_id=1;

        if($order->save())
        {
            return response()->json(['error' => false ,'message'=>' Order Record Inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

    }
    public function update(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'product_id' => 'required',
            'seller_id' => 'required',
            'customer_id' => 'required',
            'qty'=>'required',
            'total_amount'=>'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $order_data=[
        'product_id'=>$req->product_id,
        'seller_id'=>$req->seller_id,
        'customer_id'=>$req->customer_id,
        'qty'=>$req->qty,
        'total_amount'=>$req->total_amount,
        ];

        $order_update=Order::where('id',$id)->update($order_data);
        if($order_update==1)
        {
            return response()->json(['error' => false ,'message'=>' Order updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);

    }
    public function delete($id)
    {
        $order_del=Order::find($id);
        if($order_del)
        {
            $order_del->delete();
            return response()->json(['error' => false ,'message'=>'Order Record Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }


}

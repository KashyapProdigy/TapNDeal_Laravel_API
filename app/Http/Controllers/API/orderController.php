<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Cart;
use App\OrderRequest;
use App\OrderTranslation;
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
    public function createRequest(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'seller_id' => 'required',
            'cust_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        $cartrecord = Cart::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->get()->toarray();

        if($cartrecord == null)
        {
            return response()->json(['error' => true ,'message'=>'Nothing in Cart'], 500);
        }

        if($cartrecord != null)
        {
            $cart = DB::table('carts')
                            ->join('products','products.id','carts.product_id')
                            ->select('products.id as product_id','products.name as product_name','products.image as product_image','products.category','carts.qty','products.price as product_price')
                            ->where('carts.cust_id',$req->cust_id)
                            ->get()->toarray();

            foreach ($cart as $record) {
                $record->total_price = $record->product_price * $record->qty;
            }

            $order_details = new OrderDetail;
            $order_details->seller_id = $req->seller_id;
            $order_details->cust_id = $req->cust_id;
            $order_details->products = $cart;

            if($order_details->save())
            {
                $order_request = new OrderRequest;
                $order_request->seller_id = $req->seller_id;
                $order_request->cust_id = $req->cust_id;
                $order_request->$order_details->id;
                if($order_request->save())
                {
                    return response()->json(['error' => false ,'message'=>'Request Created'], 200);
                }
            }

            return response()->json(['error' => false ,'message'=>$order_details], 200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'Something Went Wrong'], 500);
        }

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

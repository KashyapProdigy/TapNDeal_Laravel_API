<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cart;
use Carbon\Carbon;
use Validator;

class cartController extends Controller
{
    public function show($id)
    {
        $cart=Cart::where('user_id',$id)->get()->toarray();
        if(!empty($cart))
        {
            return response()->json(['error' => false ,'data'=>$cart],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
            'qty'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $cart=new Cart;
        $cart->product_id=$req->product_id;
        $cart->user_id=$req->user_id;
        $cart->qty=$req->qty;
        $cart->date_time=Carbon::now();


        if($cart->save())
        {
            return response()->json(['error' => false ,'message'=>' Cart Record Inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

    }
    public function update(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
            'qty'=>'required',
            'date_time'=>'required|date_format:Y-m-d H:i:s'

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $cart_data=[
        'product_id'=>$req->product_id,
        'user_id'=>$req->user_id,
        'qty'=>$req->qty,
        'date_time'=>$req->date_time,
        ];

        $cart_update=Cart::where('id',$id)->update($cart_data);
        if($cart_update==1)
        {
            return response()->json(['error' => false ,'message'=>' Cart updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);

    }
    public function delete($id)
    {
        $cart_del=Cart::find($id);
        if($cart_del)
        {
            $cart_del->delete();
            return response()->json(['error' => false ,'message'=>'Cart Record Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }

    public function deleteByUserid($id)
    {
        $cart_del=Cart::where('user_id',$id)->get();
        if($cart_del)
        {
            Cart::where('user_id',$id)->delete();
            return response()->json(['error' => false ,'message'=>'Cart Records Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }
}

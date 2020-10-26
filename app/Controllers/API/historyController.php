<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\History;
use App\Order;
use Carbon\Carbon;
use Validator;

class historyController extends Controller
{
    public function show($id)
    {
        $history=History::where('id',$id)->get();
        if(!empty($history))
        {
            return response()->json(['error' => false ,'data'=>$history],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'cust_id' => 'required',
            'seller_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required',
            'total_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $history=new History;
        $history->cust_id=$req->cust_id;
        $history->seller_id=$req->seller_id;
        $history->product_id=$req->product_id;
        $history->qty=$req->qty;
        $history->total_amount=$req->total_amount;
        $history->order_date=Order::select('created_at')->where('product_id',$req->product_id)->where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->where('total_amount',$req->total_amount)->get();


        if($history->save())
        {
            return response()->json(['error' => false ,'message'=>'Inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
    }
    public function update(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'cust_id' => 'required',
            'seller_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required',
            'total_amount' => 'required',
            'order_date'=>'required|date_format:Y-m-d'

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $history_data=[
        'cust_id'=>$req->cust_id,
        'seller_id'=>$req->seller_id,
        'product_id'=>$req->product_id,
        'qty'=>$req->qty,
        'total_amount'=>$req->total_amount,
        'order_date'=>$req->order_date,
        ];

        $history_update=History::where('id',$id)->update($history_data);
        if($history_update==1)
        {
            return response()->json(['error' => false ,'message'=>' History updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found or Record already updated'],500);

    }
    public function delete($id)
    {
        $history_del=History::find($id);
        if($history_del)
        {
            $history_del->delete();
            return response()->json(['error' => false ,'message'=>'History Record Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }


}

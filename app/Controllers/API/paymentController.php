<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Payment;
use Carbon\Carbon;
use Validator;

class paymentController extends Controller
{
    public function show($id)
    {
        $payment=Payment::where('id',$id)->get();
        if(!empty($payment))
        {
            return response()->json(['error' => false ,'data'=>$payment],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'transaction_id' => 'required',
            'user_id' => 'required',
            'amount' => 'required',
            'method' => 'required',
            'description' => 'required',
            'date_time'=>'required|date_format:Y-m-d H:i:s'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $payment=new Payment;
        $payment->transaction_id=$req->transaction_id;
        $payment->user_id=$req->user_id;
        $payment->amount=$req->amount;
        $payment->method=$req->method;
        $payment->description=$req->description;
        $payment->date_time=$req->date_time;


        if($payment->save())
        {
            return response()->json(['error' => false ,'message'=>'Inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

    }
    public function update(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'transaction_id' => 'required',
            'user_id' => 'required',
            'amount' => 'required',
            'method' => 'required',
            'description' => 'required',
            'date_time'=>'required|date_format:Y-m-d H:i:s'

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $payment_data=[
        'transaction_id'=>$req->transaction_id,
        'user_id'=>$req->user_id,
        'amount'=>$req->amount,
        'method'=>$req->method,
        'description'=>$req->description,
        'date_time'=>$req->date_time,
        ];

        $payment_update=Payment::where('id',$id)->update($payment_data);
        if($payment_update==1)
        {
            return response()->json(['error' => false ,'message'=>' Payment updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found or Record already updated'],500);

    }
    public function delete($id)
    {
        $payment_del=Payment::find($id);
        if($payment_del)
        {
            $payment_del->delete();
            return response()->json(['error' => false ,'message'=>'Payment Record Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }


}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Payment;
use App\subscribe_user;
use Validator;
use App\User;
class subscriptionController extends Controller
{
    public function viewPlan($utype)
    {
        $plans=\DB::table('subscription_plan')->where('user_type',$utype)->get();
        if(count($plans)>0)
        {
            return response()->json(['error' => false ,'plans'=>$plans],200);
        }
        return response()->json(['error' => true ,'message'=>'Plan not available for this user'],400);
    }
    public function subscribe(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'user_id' => 'required',
            'plan_id'=>'required',
            'trans_id'=>'required',
            'payment_method'=>'required',
            'pay_status'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $plan=\DB::table('subscription_plan')->where('id',$req->plan_id)->first();
        if(!$plan)
        {
            return response()->json(['error' => true ,'message'=>'subscription plan is invalid...!'],400);
        }
        $pay=new Payment;
        $pay->transaction_id=$req->trans_id;
        $pay->user_id=$req->user_id;
        $pay->amount=$plan->amount;
        $pay->method=$req->payment_method;
        $pay->description="Subscribe a plan";
        $pay->payment_status=$req->pay_status;
        $pay->date_time=date('y-m-d H:i:s');
        
        $user=User::find($req->user_id);
        if(!$user)
        {
            return response()->json(['error' => true ,'message'=>'subscription plan is invalid...!'],400);
        }
        $pay->save();
        $end_date=$user->end_date;
        if($end_date > date('Y-m-d H:i:is'))
        {
            $end_date=date('Y-m-d H:i:s', strtotime($end_date. ' + '.$plan->days.' days')); 
        }
        else{
            $end_date=date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + '.$plan->days.' days')); 
        }
        $sub=new subscribe_user;
        $sub->uid=$req->user_id;
        $sub->plan_id=$plan->id;
        $sub->subscription_date=date('Y-m-d');
        $sub->end_date=$end_date;
        $sub->pay_id=$pay->id;
        $sub->subscription_date=date('Y-m-d H:i:s');
        $sub->save();
        $user->end_date=$end_date;
        if($user->save())
        {
            return response()->json(['error' => false ,'message'=>'subcription success..'],200);
        }
        return response()->json(['error' => true ,'message'=>'somethings went wrong'],500);
    }
    public function history($uid)
    {
        $user=User::find($uid);
        if($user)
        {    
            $payment=Payment::where('user_id',$uid)->get()->toarray();
            $subscription=subscribe_user::where('uid',$uid)->get()->toarray();
            $expire_date=$user->end_date;
            return response()->json(['error' => false ,'payment'=>$payment,'subscription'=>$subscription,'expire_date'=>$expire_date],200);
        }
        return response()->json(['error' => true ,'message'=>'User not found..!'],400);
    }
}

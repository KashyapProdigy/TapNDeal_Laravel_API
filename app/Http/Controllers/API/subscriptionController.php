<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



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
            'amount'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

    }
}

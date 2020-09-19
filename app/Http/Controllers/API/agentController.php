<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Order;
use App\custome_agent;
use Validator;

class agentController extends Controller
{
    public function generateRefCode($name)
    {
        $i=0;
        $ref = strtoupper(substr($name, 0, 3)).date('dmy').strtoupper(substr($name, -1, 1));
        $u=custome_agent::where('ref_code',$ref)->count();
        while($u>0){
            $name=$name."".$i++;
            $ref = strtoupper(substr($name, 0, 3)).date('dmy').strtoupper(substr($name, -1, 1));
            $u=custome_agent::where('ref_code',$ref)->count();
        }
        return $ref;
    }
    public function List()
    {
        $agents=User::join('user_type','user_type.id','users.type_id')
        ->select('users.id as agent_id','name','ref_code','mobile')
        ->where('user_type','Agent')->get();
        if(count($agents)>0)
            return response()->json(['error' => false ,'data'=>$agents],200);

        return response()->json(['error' => true ,'messege'=>'There have no agents..!'],200);
    }
    public function orderList($ref)
    {
        $o_list=Order::where('agent_reference',$ref)->get();
        $list=array();
        $order=array();
        foreach($o_list as $o)
        {
            $list=Order::where('orders.id',$o['id'])->join('order_status','status_id','order_status.id')->first();
            $list['seller']=User::where('id',$o['seller_id'])->select('id','name')->first();
            $list['buyer']=User::where('id',$o['cust_id'])->select('id','name')->first();
            $order[]=$list;
        }
        if(count($order)>0)    
            return response()->json(['error' => false ,'data'=>$order],200);
        
        return response()->json(['error' => true ,'messege'=>'No orders found for this Agent..!'],200);
    }
    public function orderCount($ref)
    {
        $o_co=Order::where('agent_reference',$ref)->count();
        return response()->json(['error' => false ,'count'=>$o_co],200);
    }
    public function customeAgent(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required',
            'mobile'=>'required|unique:custome_agents,mobile|digits:10',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $validator = Validator::make($req->all(), [
            'mobile'=>'required|unique:users,mobile',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $ref=$this->generateRefCode($req->name);
        $ca=new custome_agent;
        $ca->name=$req->name;
        $ca->mobile=$req->mobile;
        $ca->ref_code=$ref;
        $ca->save();
        return response()->json(['error' => true ,'message'=>$ca], 401);
    }
}

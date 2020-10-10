<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Order;
use App\custome_agent;
use Validator;
use App\emp_sel_rel;
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
    public function List($uid)
    {
        $user=User::find($uid);
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$uid)->first();
            $user=User::find($seller->seller_id);
        }
        if($user->type_id==1)
        {
            $agents=User::join('user_type','user_type.id','users.type_id')
            ->join('agent_sel_category_rel','agent_sel_category_rel.agent_id','users.id')
            ->select('users.id as agent_id','name','ref_code','mobile')
            ->where([['user_type','Agent'],['seller_id',$user['id']]])->get();
        }
        if($user->type_id==3)
        {
            $agents=User::join('user_type','user_type.id','users.type_id')
            ->join('cust_agent_rel','cust_agent_rel.agent_id','users.id')
            ->select('users.id as agent_id','name','ref_code','mobile')
            ->where([['user_type','Agent'],['cust_id',$user['id']]])->get();
        }
        
        if(count($agents)>0)
            return response()->json(['error' => false ,'data'=>$agents],200);

        return response()->json(['error' => true ,'messege'=>'There have no agents..!','data'=>[]],200);
    }
    public function orderList($ref)
    {
        $o_list=Order::where('agent_reference',$ref)->orderby('orders.created_at','desc')->get();
        $list=array();
        $order=array();
        foreach($o_list as $o)
        {
            $list=Order::where('orders.id',$o['id'])->join('order_status','status_id','order_status.id')->first();
            $list['seller']=User::where('id',$o['seller_id'])->select('id','name','mobile')->first();
            $list['buyer']=User::where('id',$o['cust_id'])->select('id','name','mobile')->first();
            $order[]=$list;
        }
        if(count($order)>0)    
            return response()->json(['error' => false ,'data'=>$order],200);
        
        return response()->json(['error' => true ,'messege'=>'No orders found for this Agent..!','data'=>[]],200);
    }
    public function pastOrderList($ref)
    {
        $o_list=Order::where('agent_reference',$ref)
        ->select('orders.id','seller_id','cust_id')
        
        ->join('order_status','status_id','order_status.id')
        ->whereIn('order_status.status_name',['Dispatched','Rejected'])
        ->orderby('orders.created_at','desc')
        ->get();
        $list=array();
        $order=array();
        foreach($o_list as $o)
        {
            $list=Order::where('orders.id',$o['id'])->select('orders.*','order_status.status_name')->join('order_status','status_id','order_status.id')
            ->first();
            $list['seller']=User::join('company_info','sid','users.id')->where('users.id',$o['seller_id'])->select('users.id','name','mobile','cname')->first();
            $list['buyer']=User::join('company_info','sid','users.id')->where('users.id',$o['cust_id'])->select('users.id','name','cname')->first();
            $order[]=$list;
        }
        if(count($order)>0)    
            return response()->json(['error' => false ,'data'=>$order],200);
        
        return response()->json(['error' => true ,'messege'=>'No past orders found for this Agent..!','data'=>[]],200);
    }
    public function ongoingOrderList($ref)
    {
        $o_list=Order::where('agent_reference',$ref)->select('orders.id','seller_id','cust_id')->join('order_status','status_id','order_status.id')->whereIn('order_status.status_name',['Accepted','Ready'])->orderby('orders.created_at','desc')->get();
        $list=array();
        $order=array();
        foreach($o_list as $o)
        {
            $list=Order::where('orders.id',$o['id'])->select('orders.*','order_status.status_name')->join('order_status','status_id','order_status.id')
            ->first();
            $list['seller']=User::join('company_info','sid','users.id')->where('users.id',$o['seller_id'])->select('users.id','name','mobile','cname')->first();
            $list['buyer']=User::join('company_info','sid','users.id')->where('users.id',$o['cust_id'])->select('users.id','name','cname')->first();
            $order[]=$list;
        }
        if(count($order)>0)    
            return response()->json(['error' => false ,'data'=>$order],200);
        
        return response()->json(['error' => true ,'messege'=>'No orders found for this Agent..!','data'=>[]],200);
    }
    public function newOrderList($ref)
    {
        $o_list=Order::where('agent_reference',$ref)->join('order_status','status_id','order_status.id')->select('orders.id','seller_id','cust_id')->where('order_status.status_name','Received')->orderby('orders.created_at','desc')->get();
        $list=array();
        $order=array();
        foreach($o_list as $o)
        {
            $list=Order::where('orders.id',$o['id'])->select('orders.*','order_status.status_name')->join('order_status','status_id','order_status.id')
            ->first();
            $list['seller']=User::join('company_info','sid','users.id')->where('users.id',$o['seller_id'])->select('users.id','name','mobile','cname')->first();
            $list['buyer']=User::join('company_info','sid','users.id')->where('users.id',$o['cust_id'])->select('users.id','name','cname')->first();
            $order[]=$list;
        }
        if(count($order)>0)    
            return response()->json(['error' => false ,'data'=>$order],200);
        
        return response()->json(['error' => true ,'messege'=>'No orders found for this Agent..!','data'=>[]],200);
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
        return response()->json(['error' => true ,'message'=>$ca], 200);
    }
}

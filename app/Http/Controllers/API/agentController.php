<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Order;

class agentController extends Controller
{
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
}

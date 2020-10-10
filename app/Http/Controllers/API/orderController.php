<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Cart;
use App\User;
use Validator;
use App\emp_sel_rel;
use App\Notifications\statusChange;
use App\Notifications\orderPlace;
use App\custome_agent;
use App\CustomerCategoryRelationship;

class orderController extends Controller
{
    public function createRequest(Request $req)
    {
        
        $validator = Validator::make($req->all(), [
            'cust_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $User=User::find($req->cust_id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$req->cust_id)->first();
            $req->cust_id=$seller->seller_id;
        }
        $cartrecord = Cart::select('seller_id','col_wise_qty')->where('cust_id',$req->cust_id)->first();

        if($cartrecord == null)
        {
            return response()->json(['error' => true ,'message'=>'Nothing in Cart'], 500);
        }

        if($cartrecord != null)
        {
            $order_amount = 0;
            $cartProducts = DB::table('carts')
                            ->join('products','products.id','carts.product_id')
                            ->select('products.id as product_id','products.name as product_name','products.image as product_image','products.category','carts.qty','carts.col_wise_qty','products.price as product_price')
                            ->where('carts.cust_id',$req->cust_id)
                            ->get()->toarray();
            $cartID = Cart::select('id')->where('cust_id', $req->cust_id)->get()->toarray();

            foreach ($cartProducts as $record) {
                $record->total_price = $record->product_price * $record->qty;
                $order_amount = $order_amount + $record->total_price;
            }
            
            if(DB::table('carts')->whereIn('id',$cartID)->delete())
            {
                $firm=\DB::table('company_info')->where('sid', $cartrecord->seller_id)->first();
                $words = explode(" ",$firm->cname);
                $fcode = "";
        
                foreach ($words as $w) {
                    $fcode .= $w[0];
                }
                $i=1;
                do{
                    $o_name=$fcode.'-'.$i++;
                }while(Order::where('order_name',$o_name)->first());
                
            $orderinsert = new Order;
            $orderinsert->order_name=$o_name;
            $orderinsert->seller_id = $cartrecord->seller_id;
            $orderinsert->cust_id = $req->cust_id;
            $orderinsert->agent_reference = $req->agent_reference;
            $orderinsert->products = json_encode($cartProducts);
            $orderinsert->total_price = $order_amount;
            $orderinsert->status_id = 1;
            $orderinsert->notes=$req->notes;
            if($orderinsert->save())
            {
                if($req->cust_id != $cartrecord->seller_id)
                {
                    $relrecord=CustomerCategoryRelationship::where('cust_id',$req->cust_id)->where('seller_id',$cartrecord->seller_id)->first();
                    if(!$relrecord)
                    {
                        $relation_data = new CustomerCategoryRelationship;
                        $relation_data->cust_id = $req->cust_id;
                        $relation_data->seller_id=$cartrecord->seller_id;
                        $relation_data->category = 'B';
                        $relation_data->save();
                    }
                }
                    $usr=User::find($cartrecord->seller_id);
                    $cust=User::find($req->cust_id);
                    $msg="Order has been placed by ".$cust->name;
                    $arr=['msg'=>$msg];
                    \Notification::send($usr, new orderPlace($arr));

                $salesman=emp_sel_rel::join('users','emp_sel_rel.emp_id','users.id')->where([['seller_id',$cartrecord->seller_id],['type_id',4]])->get();
                
                foreach($salesman as $s)
                {
                    $msg="Order has been placed by ".$cust->name;
                    $arr=['msg'=>$msg];
                    $sal=User::find($s['emp_id']);
                    \Notification::send($sal, new orderPlace($arr));
                }
                
                if($req->agent_reference!="Order without agent" && $req->agent_reference!=" ")
                {
                    $agent=User::where('ref_code',$req->agent_reference)->first();
                    if($agent)
                    {
                        $sel=User::find($cartrecord->seller_id);
                        $msg1="Order has been placed by ".$cust->name." to ".$sel->name;
                        $arr1=['msg'=>$msg1];
                        \Notification::send($agent, new orderPlace($arr1));
                    }
                   
                }
                return response()->json(['error' => false ,'message'=>"Order Requested Successfully"], 200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Something Went Wrong'], 500);
            }

            }
        }
        else{
            return response()->json(['error' => true ,'message'=>'Something Went Wrong'], 500);
        }
    }

    public function showRequest($id)
    {
        $User=User::find($id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $User=User::find($id);
            
        }
        $listreturn = DB::table('orders')
        ->join('users','users.id','orders.cust_id')
        ->join('company_info','sid','users.id')
        ->join('order_status','order_status.id','orders.status_id')
        ->select('users.name as cust_name','users.id as cust_id','users.mobile','orders.agent_reference','orders.id as order_id','orders.order_name','orders.total_price as order_price','order_status.status_name','orders.created_at as order_date','orders.products','orders.notes','cname as cust_name')
        ->where('orders.seller_id',$id)
        ->where('order_status.status_name','Received')
        ->orderby('orders.created_at','desc')
        ->get()->toarray();

        if(!empty($listreturn))
        {
            foreach($listreturn as $record){
                $count = 0;
                $record->seller_name=$User->name;
                $agent=User::where('ref_code',$record->agent_reference)->first();
                if(!$agent)
                {
                    $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                }
                if(!$agent['name'])
                {
                    $agent['name']=" ";
                    $agent['mobile']=" ";
                }
                $record->agent_name=$agent['name'];
                $record->agent_mobile=$agent['mobile'];
                $record->products = json_decode($record->products);
                foreach($record->products as $temp)
                {
                    $count ++;
                }
                $record->no_of_products = $count;
            }
            return response()->json(['error' => false ,'data'=>$listreturn],200);
        }
        else{return response()->json(['error' => false ,'data'=> null],200);}
        return response()->json(['error' => true ,'message'=>'Something went wrong']);
    }
    public function custNewOrder($cid)
    {   
        $user=User::find($cid);
        $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.seller_id')
                            ->join('order_status','order_status.id','orders.status_id')
                            ->join('company_info','sid','users.id')
                            ->select('users.name as seller_name','users.id as sel_id','users.mobile','orders.agent_reference','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname')
                            ->where('orders.cust_id',$cid)
                            ->where('order_status.status_name','Received')
                            ->orderby('orders.created_at','desc')
                            ->get()->toarray();

                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $agent=User::where('ref_code',$record->agent_reference)->first();
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->cust_name=$user->name;
                        $record->products = json_decode($record->products);
                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{
                    return response()->json(['error' => false ,'data'=> null],200);
                }
    }
    public function showOrders($id)
    {
        $User=User::find($id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $User=User::find($id);
            
        }

        if($User == null )
        {
            return response()->json(['error' => true ,'message'=>'User Not Found']);
        }
        else{
            if($User->type_id == 1)
            {
                $listreturn = DB::table('orders')
                ->join('users','users.id','orders.cust_id')
                ->join('company_info','sid','users.id')
                ->join('order_status','order_status.id','orders.status_id')
                ->select('users.name as cust_name','users.id as cust_id','users.mobile','orders.agent_reference','orders.id as order_id','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname as cust_name')
                ->where('orders.seller_id',$id)
                ->whereIn('order_status.status_name',['Accepted','Ready'])
                ->orderby('orders.created_at','desc')
                ->get()->toarray();
                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $record->seller_name=$User->name;
                        $agent=User::where('ref_code',$record->agent_reference)->first();
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->products = json_decode($record->products);
                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }
                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{return response()->json(['error' => false ,'data'=> null],200);}
            }
            if($User->type_id == 3)
            {
                $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.seller_id')
                            ->join('company_info','sid','users.id')
                            ->join('order_status','order_status.id','orders.status_id')
                            ->select('users.name as seller_name','users.id as sel_id','users.mobile','orders.agent_reference','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname')
                            ->where('orders.cust_id',$id)
                            ->whereIn('order_status.status_name',['Accepted','Ready'])
                            ->orderby('orders.created_at','desc')
                            ->get()->toarray();

                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $agent=User::where('ref_code',$record->agent_reference)->first();
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->cust_name=$User->name;
                        $record->products = json_decode($record->products);
                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{return response()->json(['error' => false ,'data'=> null],200);}
            }
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong']);
    }

    public function showPastOrders($id)
    {
        $User=User::find($id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $User=User::find($id);
            
        }

        if($User == null )
        {
            return response()->json(['error' => true ,'message'=>'User Not Found']);
        }
        else{
            if($User->type_id == 1)
            {
                $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.cust_id')
                            ->join('order_status','order_status.id','orders.status_id')
                            ->join('company_info','sid','users.id')
                            ->select('users.name as cust_name' ,'users.id as cust_id','users.mobile','orders.agent_reference','orders.id as order_id','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname as cust_name')
                            ->where('orders.seller_id',$id)
                            ->whereIn('order_status.status_name',['Dispatched','Rejected'])
                            ->orderby('orders.created_at','desc')
                            ->get()->toarray();
                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $record->seller_name=$User->name;
                        $agent=User::where('ref_code',$record->agent_reference)->first();
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                            
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->products = json_decode($record->products);
                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }
                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{return response()->json(['error' => false ,'data'=> null],200);}
            }
            if($User->type_id == 3)
            {
                $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.seller_id')
                            ->join('order_status','order_status.id','orders.status_id')
                            ->join('company_info','sid','users.id')
                            ->select('users.name as seller_name','users.mobile','orders.agent_reference','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname')
                            ->where('orders.cust_id',$id)
                            ->whereIn('order_status.status_name',['Dispatched','Rejected'])
                            ->orderby('orders.created_at','desc')
                            ->get()->toarray();

                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $agent=User::where('ref_code',$record->agent_reference)->first();
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->cust_name=$User->name;
                        $record->products = json_decode($record->products);
                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{return response()->json(['error' => false ,'data'=> null],200);}
            }
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong']);
    }

    public function acceptRequest($id)
    {
        $order_data=[
            'isApproved'=>1,
            'status_id'=>2
            ];
            $order_update=Order::where('id',$id)->update($order_data);
            $ord=Order::find($id);
            $seller=User::find($ord->seller_id);
            $cust=User::find($ord->cust_id);
            $msg='Order '.$ord->order_name.' has been Accepted';
            $arr=['msg'=>$msg];
            \Notification::send($cust, new statusChange($arr));

            $salesman=emp_sel_rel::join('users','users.id','emp_sel_rel.emp_id')->where([['type_id',6],['seller_id',$ord->seller_id]])->first();
            if($salesman)
            {
                $usr=User::find($salesman->id);
                $msg='New order '.$ord->order_name.' received please get the product ready';
                $arr=['msg'=>$msg];
                \Notification::send($usr, new statusChange($arr));
            }
            if($ord->agent_reference)
            {
                $agent=User::where('ref_code',$ord->agent_reference)->first();
                if($agent)
                {
                    $msg='Order has been created by '.$seller->name.' of your client '.$cust->name;
                    $arr=['msg'=>$msg];
                    \Notification::send($agent, new statusChange($arr));
                }
            }
            if($order_update==1)
            {
                return response()->json(['error' => false ,'message'=>' Order Accepted Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Record not found'],500);

    }

    public function rejectRequest($id)
    {
        {
            $order_data=[
                'isApproved'=>2,
                'status_id'=>5
                ];
                $order_update=Order::where('id',$id)->update($order_data);
                $ord=Order::find($id);
                $usr=User::find($ord->cust_id);
                $arr=['name'=>$ord->order_name,'status'=>'Rejected'];
                // \Notification::send($usr, new statusChange($arr));
                if($order_update==1)
                {
                    return response()->json(['error' => false ,'message'=>' Order Rejected Successfully'],200);
                }
                return response()->json(['error' => true ,'message'=>'Record not found'],500);
        }
    }
    public function allStatus()
    {
        $status=\DB::table('order_status')->whereNotIn('status_name',['Received','Rejected'])->get();
        return response()->json(['error' => false ,'status'=>$status],200);
    }
     public function status($type)
    {
        if($type==1 || $type==4)
        {
            $status=\DB::table('order_status')->whereNotIn('status_name',['Received','Rejected'])->get();
            return response()->json(['error' => false ,'status'=>$status],200);
        }
        if($type==6)
        {
            $status=\DB::table('order_status')->where('status_name','Ready')->get();
            return response()->json(['error' => false ,'status'=>$status],200);
        }
        if($type==5)
        {
            $status=\DB::table('order_status')->where('status_name','Dispatched')->get();
            return response()->json(['error' => false ,'status'=>$status],200);
        }
        return response()->json(['error' => false ,'status'=>[]],200);
    }
    public function orderStatus($id)
    {
        $status=Order::join('order_status','order_status.id','orders.status_id')->select('status_id','status_name')->where('orders.id',$id)->first();
        if($status!=null)
            return response()->json(['error' => false ,'status'=>$status],200);
        return response()->json(['error' => true ,'message'=>'order not found'],200);
    }
    public function changeStatus(Request $req,$oid)
    {
        $validator = Validator::make($req->all(), [
            'status_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $ordr=Order::find($oid);
        if($ordr)
        {
            $ordr->status_id=$req->status_id;
            $ordr->save();
            $ostat=\DB::table('order_status')->select('status_name')->where('id',$req->status_id)->first();
            $msg='Order '.$ordr->order_name.' has been '.$ostat->status_name;
            $arr=['msg'=>$msg];
            $usr=User::find($ordr['cust_id']);
            \Notification::send($usr, new statusChange($arr));
            if($req->status_id==3)
            {
                $salesman=emp_sel_rel::join('users','users.id','emp_sel_rel.emp_id')->where([['type_id',5],['seller_id',$ordr->seller_id]])->first();
                $usr=User::find($salesman->id);
                $msg='Please get bill ready for '.$ordr->order_name.' it is ready to dispatch';
                $arr=['msg'=>$msg];
                \Notification::send($usr, new statusChange($arr));
            }
            return response()->json(['error' => false ,'message'=>'Order status change'],200);
        }
        return response()->json(['error' => true ,'message'=>'Order not found'],200);
    }
     public function orderList($id)
    {

        $user=User::find($id);
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $user=User::find($id);
            
        }
        
        if($user)
        {
            if($user->type_id==1)
            {
                $listreturn = DB::table('orders')
                ->join('users','users.id','orders.cust_id')
                ->join('company_info','sid','users.id')
                ->join('order_status','order_status.id','orders.status_id')
                ->select('users.name as cust_name','users.profile_picture','users.id as cust_id','users.mobile','orders.agent_reference','orders.id as order_id','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname as cust_cname')
                ->where('orders.seller_id',$id)
                ->orderby('orders.created_at','desc')
                ->get()->toarray();
                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $cmp=\DB::table('company_info')->where('sid',$id)->first();
                        $record->seller_name=$user->name;
                        $record->seller_cname=$cmp->cname;
                        $agent=User::where('ref_code',$record->agent_reference)->join('company_info','users.id','company_info.sid')->first();
                        $cname=$agent['cname'];
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                            $cname=" ";
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->agent_cname=$cname;
                        $record->products = json_decode($record->products);
                        
                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }
                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{return response()->json(['error' => false ,'data'=> null],200);}
            }
            if($user->type_id == 3)
            {
                $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.seller_id')
                            ->join('company_info','users.id','company_info.sid')
                            ->join('order_status','order_status.id','orders.status_id')
                            ->select('users.name as seller_name','users.mobile','orders.agent_reference','orders.order_name','orders.total_price as order_price','orders.created_at as order_date','orders.products','order_status.status_name','order_status.id as status_id','orders.notes','cname as seller_cname')
                            ->where('orders.cust_id',$id)
                            ->orderby('orders.created_at','desc')
                            ->get()->toarray();

                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
                        $record->products = json_decode($record->products);
                        $agent=User::where('ref_code',$record->agent_reference)->join('company_info','users.id','company_info.sid')->first();
                        $cname=$agent['cname'];
                        if(!$agent)
                        {
                            $agent=custome_agent::where('ref_code',$record->agent_reference)->first();
                            $cname=" ";
                        }
                        if(!$agent['name'])
                        {
                            $agent['name']=" ";
                            $agent['mobile']=" ";
                        }
                        $record->agent_name=$agent['name'];
                        $record->agent_mobile=$agent['mobile'];
                        $record->agent_cname=$cname;
                        $cmp=\DB::table('company_info')->where('sid',$id)->first();
                        $record->cust_name=$cmp->cname;

                        foreach($record->products as $temp)
                        {
                            $count ++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false ,'data'=>$listreturn],200);
                }
                else{return response()->json(['error' => false ,'data'=> null],200);}
            }
            if($user->type_id == 2 || $user->type_id==8)
            {
                $o_list=Order::where('agent_reference',$user->ref_code)
                
                ->join('order_status','status_id','order_status.id')->select('orders.id','seller_id','cust_id')->orderby('orders.created_at','desc')->get();
                $list=array();
                $order=array();
                foreach($o_list as $o)
                {
                    $list=Order::where('orders.id',$o['id'])->select('orders.*','order_status.status_name','orders.total_price as order_price','users.name as cust_name','users.profile_picture')
                    ->join('users','users.id','orders.cust_id')
                    ->join('order_status','status_id','order_status.id')
                    ->first();
                    $list['agent_name']=$user->name;
                    $cmp=\DB::table('company_info')->where('sid',$id)->first();
                    $list['agent_cname']=$cmp->cname;
                    $user1=User::where('id',$o['seller_id'])->select('id','name')->first();
                    $cmp1=\DB::table('company_info')->where('sid',$user1->id)->first();
                    $list['seller_name']=$user1->name;
                    $list['seller_cname']=$cmp1->cname;
                    $order[]=$list;
                }
                if(count($order)>0)    
                    return response()->json(['error' => false ,'data'=>$order],200);
                else{
                    return response()->json(['error' => false ,'data'=>null],200);
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'Invalid user id..'],400);
            }
        }
        else{
            return response()->json(['error' => true ,'message'=>'Invalid user id..'],400);
        }
    }
}

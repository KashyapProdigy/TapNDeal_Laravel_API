<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Cart;
use App\User;
use Validator;

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

        $cartrecord = Cart::select('seller_id')->where('cust_id',$req->cust_id)->first();

        if($cartrecord == null)
        {
            return response()->json(['error' => true ,'message'=>'Nothing in Cart'], 500);
        }

        if($cartrecord != null)
        {
            $order_amount = 0;
            $cartProducts = DB::table('carts')
                            ->join('products','products.id','carts.product_id')
                            ->select('products.id as product_id','products.name as product_name','products.image as product_image','products.category','carts.qty','products.price as product_price')
                            ->where('carts.cust_id',$req->cust_id)
                            ->get()->toarray();
            $cartID = Cart::select('id')->where('cust_id', $req->cust_id)->get()->toarray();

            foreach ($cartProducts as $record) {
                $record->total_price = $record->product_price * $record->qty;
                $order_amount = $order_amount + $record->total_price;
            }

            if(DB::table('carts')->whereIn('id',$cartID)->delete())
            {
            $orderinsert = new Order;
            $orderinsert->seller_id = $cartrecord->seller_id;
            $orderinsert->cust_id = $req->cust_id;
            $orderinsert->agent_reference = $req->agent_reference;
            $orderinsert->products = json_encode($cartProducts);
            $orderinsert->total_price = $order_amount;
            $orderinsert->status_id = 1;

            if($orderinsert->save())
            {
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
        $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.cust_id')
                            ->select('users.name as cust_name','orders.agent_reference','orders.total_price as order_price','orders.created_at as order_date','orders.products')
                            ->where('orders.seller_id',$id)
                            ->where('orders.isApproved',0)
                            ->get()->toarray();

        if(!empty($listreturn))
        {
            foreach($listreturn as $record){
                $count = 0;
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

    public function showOrders($id)
    {
        $User=User::find($id);

        if($User == null )
        {
            return response()->json(['error' => true ,'message'=>'User Not Found']);
        }
        else{
            if($User->type_id == 1)
            {
                $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.cust_id')
                            ->select('users.name as cust_name','orders.agent_reference','orders.total_price as order_price','orders.created_at as order_date','orders.products')
                            ->where('orders.seller_id',$id)
                            ->where('orders.isApproved',1)
                            ->get()->toarray();
                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
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
                            ->select('users.name as seller_name','orders.agent_reference','orders.total_price as order_price','orders.created_at as order_date','orders.products')
                            ->where('orders.cust_id',$id)
                            ->where('orders.isApproved',1)
                            ->get()->toarray();

                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
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

        if($User == null )
        {
            return response()->json(['error' => true ,'message'=>'User Not Found']);
        }
        else{
            if($User->type_id == 1)
            {
                $listreturn = DB::table('orders')
                            ->join('users','users.id','orders.cust_id')
                            ->select('users.name as cust_name','orders.agent_reference','orders.total_price as order_price','orders.created_at as order_date','orders.products')
                            ->where('orders.seller_id',$id)
                            ->where('orders.isDelivered',1)
                            ->get()->toarray();
                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
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
                            ->select('users.name as seller_name','orders.agent_reference','orders.total_price as order_price','orders.created_at as order_date','orders.products')
                            ->where('orders.cust_id',$id)
                            ->where('orders.isDelivered',1)
                            ->get()->toarray();

                if(!empty($listreturn))
                {
                    foreach($listreturn as $record){
                        $count = 0;
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
            ];
            $order_update=Order::where('id',$id)->where('isApproved',0)->update($order_data);
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
                ];
                $order_update=Order::where('id',$id)->where('isApproved',0)->update($order_data);
                if($order_update==1)
                {
                    return response()->json(['error' => false ,'message'=>' Order Rejected Successfully'],200);
                }
                return response()->json(['error' => true ,'message'=>'Record not found'],500);
        }
    }
}

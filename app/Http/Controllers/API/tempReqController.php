<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\temp_req;
use Validator;
use App\User;
use App\temp_req_product;

class tempReqController extends Controller
{
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            
            'request_by' => 'required',
            'request_to'=>'required',
            'request_for' => 'required',
            'remarks' => 'required',
            
        ],[

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        // $se=json_decode($req->request_to);
        $se=implode(',',$req->request_to);
        $se=explode(',',$se);
        foreach($se as $seller)
        {
            $t=new temp_req;
            $t->req_by=$req->request_by;
            $t->req_for=$req->request_for;
            $t->req_to=$seller;
            $t->remarks=$req->remarks;
            $t->save();
        }
        return response()->json(['error' => false ,'message'=>'Temporary Request Added..'], 200);
    }
    public function show($bid)
    {
        $tr=temp_req::where('req_for',$bid)->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req::where('id',$t['id'])->first();
            $temp['agent']=User::where('id',$t['req_by'])->select('id','name')->first();
            $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
            $rec[]=$temp;
        }
        if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 200);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this buyer..'], 400);
    }
    public function agentShow($aid)
    {
        $tr=temp_req::where('req_by',$aid)->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req::where('id',$t['id'])->first();
            $temp['buyer']=User::where('id',$t['req_for'])->select('id','name')->first();
            $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
            $rec[]=$temp;
        }
         if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 200);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this Agent..'], 400);
    }
    public function sellerShow($sid)
    {
        $tr=temp_req::where('req_to',$sid)->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req::where('id',$t['id'])->first();
            $temp['buyer']=User::where('id',$t['req_for'])->select('id','name')->first();
            $temp['agent']=User::where('id',$t['req_by'])->select('id','name')->first();
            $rec[]=$temp;
        }
         if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 200);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this Seller..'], 400);
    }
    public function responseReq(Request $req)
    {
        $validator = Validator::make($req->all(), [
            
            'sid' => 'required|numeric',
            'pid'=>'required',
            'trid'=>'required|numeric',
            'time_period'=>'required|numeric'
            
        ],[
            'sid.required'=>'Seller id is required..',
            'pid.required'=>'Products ids are required',
            'trid.required'=>'temporary request id required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $tempReq=temp_req::find($req->trid);
        if($tempReq)
        {
            $tempReq->isResponded=1;
            if($tempReq->save())
            {
                $pi=implode(',',$req->pid);
                $pi=explode(',',$pi);
                foreach($pi as $p)
                {    
                    $tr=new temp_req_product;
                    $tr->sid=$req->sid;
                    $tr->trid=$req->trid;
                    $tr->pid=$p;
                    $tr->end_period=date('Y-m-d H:i:s', strtotime("+".$req->time_period." days"));
                    $tr->save();
                }
                return response()->json(['error' => false ,'message'=>"Response Added successfully.."], 200);
            }
                
        }
        return response()->json(['error' => true ,'message'=>"Somethings went wrong."], 400);
    }
    public function showResponseBuyer($bid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->join('users','temp_req_pro.sid','users.id')
        ->join('products','products.id','temp_req_pro.pid')
        ->where('req_for',$bid)
        ->select('users.id as seller_id','users.name as seller_name','products.id as product_id','products.name as product_name','products.*','temp_req_pro.end_period')
        ->get();
        if(count($data)>0)
        {
            return response()->json(['error' => false ,'message'=>$data], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this buyer not found..'], 400);
    }
    public function showResponseAgent($aid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->join('users','temp_req_pro.sid','users.id')
        ->join('products','products.id','temp_req_pro.pid')
        ->where('req_by',$aid)
        ->select('users.id as seller_id','users.name as seller_name','products.id as product_id','products.name as product_name','products.*','temp_req_pro.end_period')
        ->get();
        if(count($data)>0)
        {
            return response()->json(['error' => false ,'message'=>$data], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this agent not found..'], 400);
    }
}

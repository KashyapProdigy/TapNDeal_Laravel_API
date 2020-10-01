<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\temp_req;
use Validator;
use App\User;
use App\temp_req_product;
use App\Product;

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
        $tr=temp_req::where('req_for',$bid)->orderBy('created_at','desc')->get();
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
        $tr=temp_req::where('req_by',$aid)->orderBy('created_at','desc')->get();
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
        $tr=temp_req::where('req_to',$sid)->orderBy('created_at','desc')->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req::where('id',$t['id'])->first();
            $temp['buyer']=User::where('id',$t['req_for'])->select('id','name','mobile')->first();
            $temp['agent']=User::where('id',$t['req_by'])->select('id','name','mobile')->first();
            $respone=temp_req_product::where([['sid',$sid],['trid',$t['id']]])->first();
            if($respone)
            {
                $temp['responded']=true;    
            }
            else{
                $temp['responded']=false;    
            }
            
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
                
                    $tr=new temp_req_product;
                    $tr->sid=$req->sid;
                    $tr->trid=$req->trid;
                    $tr->pid=$pi;
                    $tr->end_period=date('Y-m-d H:i:s', strtotime("+".$req->time_period." days"));
                    $tr->save();
                
                return response()->json(['error' => false ,'message'=>"Response Added successfully.."], 200);
            }
                
        }
        return response()->json(['error' => true ,'message'=>"Somethings went wrong."], 400);
    }
    public function showResponseBuyer($bid,$trid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->where('req_for',$bid)
        ->where('temp_req.id',$trid)
        ->get();
        $li=array();
        $list=array();
        $prod=array();
        foreach($data as $d)
        {
            $prdct=explode(',',$d['pid']);
            $list['seller']=User::where('id',$d['sid'])->first();
            foreach($prdct as $p)
            {
                $prod[]=Product::where('id',$p)->get();
            }
            
            $list['seller']['product']=$prod;
            $li[]=$list;
            $prod=null;
            
        }
        
        if(count($data)>0)
        {
            return response()->json(['error' => false ,'message'=>$li], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this buyer not found..'], 400);
    }
    public function showResponseAgent($aid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->where('req_by',$aid)
        ->get();
        $li=array();
        $list=array();
        $prod=array();
        foreach($data as $d)
        {
            $prdct=explode(',',$d['pid']);
            $list['seller']=User::where('id',$d['sid'])->first();
            foreach($prdct as $p)
            {
                $prod[]=Product::where('id',$p)->get();
            }
            
            $list['seller']['product']=$prod;
            $li[]=$list;
            $prod=null;
            
        }
        if(count($data)>0)
        {
            return response()->json(['error' => false ,'message'=>$li], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this agent not found..'], 400);
    }
    public function showResponseSeller($sid,$trid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->where('req_to',$sid)
        ->where('temp_req.id',$trid)
        ->first();
        $li=array();
        $list=array();
        $prod=array();
        $prdct=explode(',',$data['pid']);
        foreach($prdct as $p)
        {
            $prod[]=Product::where('id',$p)->get();
        }
        if($data)
        {
            return response()->json(['error' => false ,'message'=>$prod], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this buyer not found..'], 400);
    }
    public function delete($trid)
    {
        $tr=temp_req::find($trid);   
        if($tr)
        {
            $tr->delete();
            return response()->json(['error' => false ,'message'=>'Temporary Requirement deleted'], 200);
        }
        return response()->json(['error' => true ,'message'=>'Temporary Requirement not found'], 400);
    }
    public function showStausWise(Requset $req)
    {
        $user=User::find($id);   
        if($user)
        {
        }
        else{
            return response()->json(['error' => true ,'message'=>'Invalid user id..'], 400);
        }
    }
}

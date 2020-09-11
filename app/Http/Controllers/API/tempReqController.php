<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\temp_req;
use Validator;
use App\User;

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
        // dd($req->request_for);
        foreach($req->request_for as $buyer)
        {
            $t=new temp_req;
            $t->req_by=$req->request_by;
            $t->req_to=$req->request_to;
            $t->req_for=$buyer;
            $t->remarks=$req->remarks;
            $t->save();
        }
        return response()->json(['error' => true ,'message'=>'Temporary Request Added..'], 401);
    }
    public function show($bid)
    {
        $tr=temp_req::where('req_for',$bid)->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req::where('req_for',$t['req_for'])->first();
            $temp['agent']=User::where('id',$t['req_by'])->select('id','name')->first();
            $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
            $rec[]=$temp;
        }
        if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 401);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this buyer..'], 401);
    }
}

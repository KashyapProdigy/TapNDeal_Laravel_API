<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\CustomerAgentRequest;
use App\CustomerAgentRelationship;
use Validator;

class custAgentRequestController extends Controller
{
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'agent_id' => 'required',
            'cust_id'  => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $rel=CustomerAgentRelationship::where([['cust_id',$req->cust_id],['agent_id',$req->agent_id]])->first();
        if($rel)
        {
            return response()->json(['error' => true ,'message'=>'Realtion already created']);
        }
        $request_data=new CustomerAgentRelationship;

        $request_cust=User::where('id',$req->cust_id)->where('type_id',3)->first();
        if(!empty($request_cust))
        {
            $request_data->cust_id=$req->cust_id;
            $request_data->agent_id=$req->agent_id;
        }
        else{
            return response()->json(['error' => true ,'message'=>'Customer not found']);
        }
        if($request_data->save())
        {
            return response()->json(['error' => false ,'message'=>'Relation made'],200);
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'something went wrong'],500);
        }
    }

    public function approve($id)
    {
        $record=CustomerAgentRequest::where('id',$id)->first();
        if(!empty($record))
        {
            $request_data=[
                'isApproved'=>'1',
                'isActive'=>0
            ];

            $relation_data = new CustomerAgentRelationship;
            $relation_data->cust_id = $record->cust_id;
            $relation_data->agent_id=$record->agent_id;

            $knock_update=CustomerAgentRequest::where('id',$record->id)->update($request_data);
            if($knock_update==1 && $relation_data->save())
            {
                return response()->json(['error' => false ,'message'=>' Agent Approved Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Already Updated '],500);    
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'Record not found or Already Updated '],500);
        }
    }

    public function reject($id)
    {
        
        $record=CustomerAgentRequest::where('id',$id)->first();
        if(!empty($record))
        {
            $request_data=[
                'isApproved'=>0,
                'isActive'=>0
            ];

            $knock_update=CustomerAgentRequest::where('id',$id)->update($request_data);
            if($knock_update==1)
            {
                return response()->json(['error' => false ,'message'=>' Agent Rejected'],200);
            }
                return response()->json(['error' => true ,'message'=>'Record not found or Already Updated '],500);
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'Something went wrong']);
        }
    }

    public function custshow($id)
    {
        $requests=CustomerAgentRequest::where('cust_id',$id)->where('isActive',1)->where('isApproved',0)->get()->toarray();
        if(!empty($requests))
        {
            return response()->json(['error' => false ,'data'=>$requests],200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'Requests not available']);
        }
    }
    public function agentShow($id)
    {
        $requests=CustomerAgentRequest::where('agent_id',$id)->where('isActive',1)->where('isApproved',0)->get()->toarray();
        if(!empty($requests))
        {
            return response()->json(['error' => false ,'data'=>$requests],200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'Requests not available']);
        }
    }
}

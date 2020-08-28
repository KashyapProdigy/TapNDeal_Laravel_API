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
            'cust_id' => 'required',
            'mobile'  => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $request_data=new CustomerAgentRequest;

        $request_agent=User::where('mobile',$req->mobile)->where('type_id',2)->first();
        if(!empty($request_agent))
        {
            $request_data->cust_id=$req->cust_id;
            $request_data->agent_id=$request_agent->id;
        }
        else{
            return response()->json(['error' => true ,'message'=>'Agent not found']);
        }
        if($request_data->save())
        {
            return response()->json(['error' => false ,'message'=>'insert Successfully'],200);
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'something went wrong'],500);
        }
    }

    public function approve(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'agent_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $record=CustomerAgentRequest::where('cust_id',$id)->where('agent_id',$req->agent_id)->first();
        if(!empty($record))
        {
            $request_data=[
                'cust_id'=>$id,
                'agent_id'=>$req->agent_id,
                'isApproved'=>'1',
                'isActive'=>0
            ];

            $relation_data = new CustomerAgentRelationship;
            $relation_data->cust_id = $id;
            $relation_data->agent_id=$req->agent_id;

            $knock_update=CustomerAgentRequest::where('id',$record->id)->update($request_data);
            if($knock_update==1 && $relation_data->save())
            {
                return response()->json(['error' => false ,'message'=>' Customer Approved Successfully'],200);
            }
                return response()->json(['error' => true ,'message'=>'Record not found or Already Updated '],500);
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'Something went wrong']);
        }
    }

    public function reject(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'agent_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $record=CustomerAgentRequest::where('cust_id',$id)->where('agent_id',$req->agent_id)->first();
        if(!empty($record))
        {
            $request_data=[
                'cust_id'=>$id,
                'agent_id'=>$req->agent_id,
                'isApproved'=>0,
                'isActive'=>0
            ];

            $knock_update=CustomerAgentRequest::where('id',$record->id)->update($request_data);
            if($knock_update==1)
            {
                return response()->json(['error' => false ,'message'=>' Customer Rejected'],200);
            }
                return response()->json(['error' => true ,'message'=>'Record not found or Already Updated '],500);
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'Something went wrong']);
        }
    }

    public function show($id)
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

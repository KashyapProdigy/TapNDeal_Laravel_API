<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\AgentKnock;
use App\AgentCategoryRelationship;
use Validator;

class agentKnockController extends Controller
{
    public function create(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'agent_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }

            $knock_data=new AgentKnock;
            $knock_seller=User::find($id);

            if(!empty($knock_seller))
            {
                $record=AgentKnock::where('agent_id',$req->agent_id)->where('seller_id',$knock_seller->id)->first();
                $relrecord=AgentCategoryRelationship::where('agent_id',$req->agent_id)->where('seller_id',$knock_seller->id)->first();
                if($relrecord != null && $relrecord->isBlocked == 1)
                {
                    return response()->json(['error' => true ,'message'=>'User Blocked By Seller']);
                }
                if($record == null)
                {
                    $knock_data->agent_id=$req->agent_id;
                    $knock_data->seller_id=$knock_seller->id;
                }
                else if($record->isActive == 1 && $record->isApproved == 0)
                {
                    return response()->json(['error' => true ,'knock' => true ,'message'=>'Knock Already Exist']);
                }
                else if($record->isActive == 0 && $record->isApproved == 1)
                {
                    $update_data=[
                        'agent_id'=>$req->agent_id,
                        'seller_id'=>$knock_seller->id,
                        'isApproved'=>0,
                        'isActive'=>1
                    ];
                    $status=AgentKnock::where('id',$record->id)->update($update_data);
                    if($status ==1)
                    {
                    return response()->json(['error' => false ,'message'=>'Knock Successfull'],200);
                    }
                }
                else if($record->isActive == 0 && $record->isApproved == 0)
                {
                    $update_data=[
                        'agent_id'=>$req->agent_id,
                        'seller_id'=>$knock_seller->id,
                        'isApproved'=>0,
                        'isActive'=>1
                    ];
                    $status=AgentKnock::where('id',$record->id)->update($update_data);
                    if($status ==1)
                    {
                    return response()->json(['error' => false ,'message'=>'Knock Successfull'],200);
                    }
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'Seller not found']);
            }
            if($knock_data->save())
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
                'seller_id' => 'required',
                'category' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $knockrecord=AgentKnock::where('agent_id',$id)->where('seller_id',$req->seller_id)->first();
            $relrecord=AgentCategoryRelationship::where('agent_id',$id)->where('seller_id',$req->seller_id)->first();
            $knock_data=[
                'agent_id'=>$id,
                'seller_id'=>$req->seller_id,
                'isApproved'=>1,
                'isActive'=>0
            ];
            if($knockrecord != null)
            {
                    if($relrecord == null)
                    {
                        $relation_data = new AgentCategoryRelationship;
                        $relation_data->agent_id = $id;
                        $relation_data->seller_id=$req->seller_id;
                        $relation_data->category = $req->category;

                        $knock_update=AgentKnock::where('id',$knockrecord->id)->update($knock_data);
                        if($knock_update==1 && $relation_data->save())
                        {
                        return response()->json(['error' => false ,'message'=>' Agent Approved Successfully'],200);
                        }
                        return response()->json(['error' => true ,'message'=>'Record not found'],500);
                    }
                    if($relrecord->isBlocked == 1 )
                    {
                        return response()->json(['error' => true ,'message'=>'Remove User from Blocked']);
                    }
                    if($relrecord != null && $relrecord->isBlocked == 0)
                    {
                        if($relrecord->category == $req->category)
                        {
                            $knockstatus=AgentKnock::where('id',$knockrecord->id)->update($knock_data);
                            if($knockstatus == 1 ){
                            return response()->json(['error' => false ,'message'=>'Approved with new category'],200);
                            }
                        }

                        $rel_data=[
                            'agent_id'=>$id,
                            'seller_id'=>$req->seller_id,
                            'category'=>$req->category,
                            'isBlocked'=> 0
                        ];
                        $knockstatus=AgentKnock::where('id',$knockrecord->id)->update($knock_data);
                        $relstatus=AgentCategoryRelationship::where('id',$relrecord->id)->update($rel_data);
                        if($relstatus == 1 && $knockstatus == 1 )
                        {
                        return response()->json(['error' => false ,'message'=>'Approved with new category'],200);
                        }
                        else {
                            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
                        }
                    }
            }
            else
            {
                return response()->json(['error' => true ,'message'=>'Record not found']);
            }
        }

        public function reject(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $knockrecord=AgentKnock::where('agent_id',$id)->where('seller_id',$req->seller_id)->first();
            if($knockrecord!=null)
            {
                $knock_data=[
                    'agent_id'=>$id,
                    'seller_id'=>$req->seller_id,
                    'isApproved'=>0,
                    'isActive'=>0
                ];

                $knock_update=AgentKnock::where('id',$knockrecord->id)->update($knock_data);
                if($knock_update==1)
                {
                    return response()->json(['error' => false ,'message'=>' Agent Rejected Successfully'],200);
                }
                    return response()->json(['error' => true ,'message'=>'Record not found or Already Updated '],500);
            }
            else
            {
                return response()->json(['error' => true ,'message'=>'Record not found']);
            }
        }

    public function show($id)
    {
        $knockreturn = DB::table('agent_sel_knock_rel')
                            ->join('users','users.id','agent_sel_knock_rel.agent_id')
                            ->select('users.name','agent_sel_knock_rel.*')
                            ->where('agent_sel_knock_rel.seller_id',$id)
                            ->where('agent_sel_knock_rel.isActive',1)
                            ->get()->toarray();
        if(!empty($knockreturn))
        {
            return response()->json(['error' => false ,'data'=>$knockreturn],200);
        }
        else{
            return response()->json(['error' => false ,'data'=>null]);
        }
    }

}

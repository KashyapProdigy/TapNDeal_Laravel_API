<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AgentCategoryRelationship;
use Validator;

class agentRelationshipController extends Controller
{
    public function promote(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $relation_data=[];
            $relation_record=AgentCategoryRelationship::where([['agent_id',$id],['seller_id',$req->seller_id]])->first();
            if(!empty($relation_record))
            {
                if(($relation_record['category'])=="A")
                {
                    $category="A+";
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'agent_id'=>$relation_record->agent_id,
                        'category'=>$category,
                        'isBlocked'=>$relation_record->isBlocked,
                    ];
                }
                elseif(($relation_record['category'])=="B")
                {
                    $category="A";
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'agent_id'=>$relation_record->agent_id,
                        'category'=>$category,
                        'isBlocked'=>$relation_record->isBlocked,
                    ];
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'User not found']);
            }
            $relation_update=AgentCategoryRelationship::where('id',$relation_record['id'])->update($relation_data);
            if($relation_update==1)
            {
                return response()->json(['error' => false ,'message'=>'Agent Promoted'],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Record not found']);
            }
        }

        public function demote(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $relation_data=[];
            $relation_record=AgentCategoryRelationship::where([['agent_id',$id],['seller_id',$req->seller_id]])->first();
            if(!empty($relation_record))
            {
                if(($relation_record['category'])=="A+")
                {
                    $category="A";
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'agent_id'=>$relation_record->agent_id,
                        'category'=>$category,
                        'isBlocked'=>$relation_record->isBlocked,
                    ];
                }
                elseif(($relation_record['category'])=="A")
                {
                    $category="B";
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'agent_id'=>$relation_record->agent_id,
                        'category'=>$category,
                        'isBlocked'=>$relation_record->isBlocked,

                    ];
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'User not found']);
            }
            $relation_update=AgentCategoryRelationship::where('id',$relation_record['id'])->update($relation_data);
            if($relation_update==1)
            {
                return response()->json(['error' => false ,'message'=>'Agent Demoted'],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Record not found']);
            }
        }

        public function block(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $relation_data=[];
            $relation_record=AgentCategoryRelationship::where([['agent_id',$id],['seller_id',$req->seller_id]])->first();
            if(!empty($relation_record))
            {
                if(($relation_record['isBlocked'])==0)
                {
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'agent_id'=>$relation_record->agent_id,
                        'category'=>$relation_record->category,
                        'isBlocked'=>1,
                    ];
                }
                elseif(($relation_record['isBlocked'])==1)
                {
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'agent_id'=>$relation_record->agent_id,
                        'category'=>$relation_record->category,
                        'isBlocked'=>0,
                    ];
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'User not found']);
            }
            $relation_update=AgentCategoryRelationship::where('id',$relation_record['id'])->update($relation_data);
            if($relation_update==1)
            {
                return response()->json(['error' => false ,'message'=>'Relation Updated'],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Record not found']);
            }
        }
        public function show($id)
        {
            $relations=AgentCategoryRelationship::where('seller_id',$id)->get()->toarray()  ;
            if(!empty($relations))
            {
                return response()->json(['error' => false ,'data'=>$relations],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Relations not available']);
            }
        }
}


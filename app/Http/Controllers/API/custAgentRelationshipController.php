<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerAgentRelationship;
use Validator;

class custAgentRelationshipController extends Controller
{
        public function block(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'cust_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $relation_data=[];
            $relation_record=CustomerAgentRelationship::where([['agent_id',$id],['cust_id',$req->cust_id]])->first();
            if(!empty($relation_record))
            {
                if(($relation_record['isBlocked'])==0)
                {
                    $relation_data=[
                        'cust_id'=>$relation_record->cust_id,
                        'agent_id'=>$relation_record->agent_id,
                        'isBlocked'=>1,
                    ];
                }
                elseif(($relation_record['isBlocked'])==1)
                {
                    $relation_data=[
                        'cust_id'=>$relation_record->cust_id,
                        'agent_id'=>$relation_record->agent_id,
                        'isBlocked'=>0,
                    ];
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'User not found']);
            }
            $relation_update=CustomerAgentRelationship::where('id',$relation_record['id'])->update($relation_data);
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
            $relations=CustomerAgentRelationship::where('agent_id',$id)->get()->toarray()  ;
            if(!empty($relations))
            {
                return response()->json(['error' => false ,'data'=>$relations],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Relations not available']);
            }
        }
}


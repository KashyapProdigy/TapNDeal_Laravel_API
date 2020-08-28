<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeSellerRelationship;
use App\User;
use Validator;

class employeeSellerRelationshipController extends Controller
{

    public function createEmployee(Request $req)
    {
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
                'name' => 'required',
                'email' => 'required',
                'mobile' => 'required',
                'password' => 'required',
                'type_id' => 'required|numeric|in:4,5,6',
                'city_id' => 'required',
                'state_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $employee_user=new User;
            $employee_user->name=$req->name;
            $employee_user->email=$req->email;
            $employee_user->mobile=$req->mobile;
            $employee_user->password=$req->password;
            $employee_user->type_id=$req->type_id;
            $employee_user->city_id=$req->city_id;
            $employee_user->state_id=$req->state_id;
            $employee_user->isVerified=1;

            if($employee_user->save())
            {
                $employee_rel=new EmployeeSellerRelationship;
                $employee_rel->emp_id=$employee_user->id;
                $employee_rel->seller_id=$req->seller_id;

                if($employee_rel->save())
                return response()->json(['error' => false ,'message'=>'Inserted Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

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
            $relation_record=EmployeeSellerRelationship::where('emp_id',$id)->where('seller_id',$req->seller_id)->first();
            if(!empty($relation_record))
            {
                if(($relation_record['isBlocked'])==0)
                {
                    $relation_data=[
                        'emp_id'=>$relation_record->emp_id,
                        'seller_id'=>$relation_record->seller_id,
                        'isBlocked'=>1,
                    ];
                }
                elseif(($relation_record['isBlocked'])==1)
                {
                    $relation_data=[
                        'emp_id'=>$relation_record->emp_id,
                        'seller_id'=>$relation_record->seller_id,
                        'isBlocked'=>0,
                    ];
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'User not found']);
            }
            $relation_update=EmployeeSellerRelationship::where('emp_id',$relation_record['emp_id'])->update($relation_data);
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
            $relations=EmployeeSellerRelationship::where('seller_id',$id)->get()->toarray()  ;
            if(!empty($relations))
            {
                return response()->json(['error' => false ,'data'=>$relations],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Relations not available']);
            }
        }
}

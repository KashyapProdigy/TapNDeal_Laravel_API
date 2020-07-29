<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Knock;
use Carbon\Carbon;
use Validator;

class knockController extends Controller
{
        public function create(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'cust_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $knock_data=new Knock;

            $knock_seller=User::find($id);
            if(!empty($knock_seller))
            {
                $date_time=Carbon::now();
                $knock_data->cust_id=$req->cust_id;
                $knock_data->seller_id=$knock_seller->id;
                $knock_data->knock_at=$date_time;
            }
            else{
                return response()->json(['error'=>'Seller not found']);
            }
            if($knock_data->save())
            {
                return response()->json(['success'=>'insert Successfully'],200);
            }
            else
            {
                return response()->json(['error'=>'something went wrong'],500);
            }
        }

        public function approve(Request $req,$id)
        {
            $knock_record=Knock::find($id);
            $knock_data=[];
            if(!empty($knock_record))
            {
                if(($knock_record['category'])=="A")
                {
                    $category="A+";
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$category,
                        'isBlocked'=>$knock_record->isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
                else if(($knock_record['category'])=="B")
                {
                    $category="A";
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$category,
                        'isBlocked'=>$knock_record->isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
            }
            else{
                return response()->json(['error'=>'User not found']);
            }
            $knock_update=Knock::where('id',$id)->update($knock_data);
            if($knock_update==1)
            {
                return response()->json(['success'=>'Knock Approved']);
            }
            else{
                return response()->json(['error'=>'Record not found']);
            }
        }

        public function promote(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $knock_data=[];
            $knock_record=Knock::where([['cust_id',$id],['seller_id',$req->seller_id]])->get()->last();
            if(!empty($knock_record))
            {
                if(($knock_record['category'])=="A")
                {
                    $category="A+";
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$category,
                        'isBlocked'=>$knock_record->isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
                elseif(($knock_record['category'])=="B")
                {
                    $category="A";
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$category,
                        'isBlocked'=>$knock_record->isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
            }
            else{
                return response()->json(['error'=>'User not found']);
            }
            $knock_update=Knock::where('id',$knock_record['id'])->update($knock_data);
            if($knock_update==1)
            {
                return response()->json(['success'=>'Customer Promoted']);
            }
            else{
                return response()->json(['error'=>'Record not found']);
            }
        }

        public function demote(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $knock_data=[];
            $knock_record=Knock::where([['cust_id',$id],['seller_id',$req->seller_id]])->get()->last();
            if(!empty($knock_record))
            {
                if(($knock_record['category'])=="A+")
                {
                    $category="A";
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$category,
                        'isBlocked'=>$knock_record->isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
                elseif(($knock_record['category'])=="A")
                {
                    $category="B";
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$category,
                        'isBlocked'=>$knock_record->isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
            }
            else{
                return response()->json(['error'=>'User not found']);
            }
            $knock_update=Knock::where('id',$knock_record['id'])->update($knock_data);
            if($knock_update==1)
            {
                return response()->json(['success'=>'Customer Demoted']);
            }
            else{
                return response()->json(['error'=>'Record not found']);
            }
        }

        public function block(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $knock_data=[];
            $knock_record=Knock::where([['cust_id',$id],['seller_id',$req->seller_id]])->get()->last();
            if(!empty($knock_record))
            {
                if(($knock_record['isBlocked'])== 0)
                {
                    $isBlocked= 1;
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$knock_record->category,
                        'isBlocked'=>$isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
                elseif(($knock_record['isBlocked'])== 1)
                {
                    $isBlocked=0;
                    $knock_data=[
                        'seller_id'=>$knock_record->seller_id,
                        'cust_id'=>$knock_record->cust_id,
                        'category'=>$knock_record->category,
                        'isBlocked'=>$isBlocked,
                        'knock_at'=>$knock_record->knock_at,
                    ];
                }
            }
            else{
                return response()->json(['error'=>'User not found']);
            }
            $knock_update=Knock::where('id',$knock_record['id'])->update($knock_data);
            if($knock_update==1 && ($knock_data['isBlocked'])== 1)
            {
                return response()->json(['success'=>'Customer Blocked']);
            }
            elseif($knock_update==1 && ($knock_data['isBlocked'])== 0)
            {
                return response()->json(['success'=>'Customer Unblocked']);
            }
            else{
                return response()->json(['error'=>'Record not found']);
            }
        }

}

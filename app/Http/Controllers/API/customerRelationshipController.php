<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerCategoryRelationship;
use App\CustomerKnock;
use App\Product;
use Carbon\Carbon;
use Validator;

class customerRelationshipController extends Controller
{
    public function update(Request $req,$id)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
                'category' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }

            $relation_record=CustomerCategoryRelationship::where([['cust_id',$id],['seller_id',$req->seller_id]])->first();

            if(!empty($relation_record))
            {
                $relation_data=['category'=>$req->category];
                $relation_update=CustomerCategoryRelationship::where('id',$relation_record['id'])->update($relation_data);
                return response()->json(['error' => false ,'message'=>'Customer Category Updated'],200);
                
            }
            else{
                return response()->json(['error' => true ,'message'=>'Record Not Found']);
            }

            return response()->json(['error' => true ,'message'=>'Something Went Wrong']);
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
            $relation_record=CustomerCategoryRelationship::where([['cust_id',$id],['seller_id',$req->seller_id]])->first();
            if(!empty($relation_record))
            {
                if(($relation_record['isBlocked'])==0)
                {
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'cust_id'=>$relation_record->cust_id,
                        'category'=>$relation_record->category,
                        'isBlocked'=>1,
                    ];
                }
                elseif(($relation_record['isBlocked'])==1)
                {
                    $relation_data=[
                        'seller_id'=>$relation_record->seller_id,
                        'cust_id'=>$relation_record->cust_id,
                        'category'=>$relation_record->category,
                        'isBlocked'=>0,
                    ];
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'User not found']);
            }
            $relation_update=CustomerCategoryRelationship::where('id',$relation_record['id'])->update($relation_data);
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
            $relations=CustomerCategoryRelationship::where('seller_id',$id)->get()->toarray()  ;
            if(!empty($relations))
            {
                return response()->json(['error' => false ,'data'=>$relations],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Relations not available']);
            }
        }

        public function productlist(Request $req)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
                'cust_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $banner=\DB::table('banners')->where('manu_id',$req->seller_id)->get()->toarray();
            $relations=CustomerCategoryRelationship::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->first();
            $knock= CustomerKnock::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->first();
            if($knock != null)
            {
                if($knock['isApproved'] == 1)
                {
                    $ac=true;
                }
                else{
                    $ac=false;
                }
                if($knock['isActive']==1)
                {
                    $active=true;
                }
                else
                {
                    $active=false;
                }
            }
            if($relations == null && $knock == null)
            {

                $products = Product::where('seller_id',$req->seller_id)->where([['category','B'],['isDisabled','0'],['fid',null]])->get()->toarray();
                return response()->json(['error' => false,'Knock'=>false,'relation'=>false,'accepted'=>false ,'active'=>false,'data'=>$products,'banner'=>$banner],200);
            }
            else if($relations == null && $knock != null )
            {
                $products = Product::where('seller_id',$req->seller_id)->where([['category','B'],['isDisabled','0'],['fid',null]])->get()->toarray();
                return response()->json(['error' => false ,'Knock'=>true,'relation'=>false ,'accepted'=>$ac,'active'=>$active,'data'=>$products,'banner'=>$banner],200);
            }
            else if($relations!=null)
            {
                if($relations->isBlocked == 1){return response()->json(['error' => true ,'message'=>'User Blocked By Seller']);}
                if($relations->isBlocked != 1)
                {
                    if($relations->category == 'A+')
                    {
                        $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',null]])->get()->toarray();
                        return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                    if($relations->category == 'A')
                    {
                        $cat=['A','B+','B'];
                        $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',null]])->whereIn('category',$cat)->get()->toarray();
                        return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                    if($relations->category == 'B+')
                    {
                        $cat=['B+','B'];
                        $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',null]])->whereIn('category',$cat)->get()->toarray();
                        return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                    if($relations->category == 'B')
                    {
                    $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',null]])->where('category',$relations->category)->get()->toarray();
                    return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                }
            }
            else {
                return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
            }
        }
        public function productListFolder(Request $req)
        {
            $validator = Validator::make($req->all(), [
                'seller_id' => 'required',
                'cust_id' => 'required',
                'fid'=>'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $banner=\DB::table('banners')->where('manu_id',$req->seller_id)->get()->toarray();
            $fid=$req->fid;
            $relations=CustomerCategoryRelationship::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->first();
            $knock= CustomerKnock::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->first();
            if($knock != null)
            {
                if($knock['isApproved'] == 1)
                {
                    $ac=true;
                }
                else{
                    $ac=false;
                }
                if($knock['isActive']==1)
                {
                    $active=true;
                }
                else
                {
                    $active=false;
                }
            }
            if($relations == null && $knock == null)
            {

                $products = Product::where('seller_id',$req->seller_id)->where([['category','B'],['isDisabled','0'],['fid',$fid]])->get()->toarray();
                return response()->json(['error' => false,'Knock'=>false,'relation'=>false,'accepted'=>false ,'active'=>false,'data'=>$products,'banner'=>$banner],200);
            }
            else if($relations == null && $knock != null )
            {
                $products = Product::where('seller_id',$req->seller_id)->where([['category','B'],['isDisabled','0'],['fid',$fid]])->get()->toarray();
                return response()->json(['error' => false ,'Knock'=>true,'relation'=>false ,'accepted'=>$ac,'active'=>$active,'data'=>$products,'banner'=>$banner],200);
            }
            else if($relations!=null)
            {
                if($relations->isBlocked == 1){return response()->json(['error' => true ,'message'=>'User Blocked By Seller']);}
                if($relations->isBlocked != 1)
                {
                    if($relations->category == 'A+')
                    {
                        $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',$fid]])->get()->toarray();
                        return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                    if($relations->category == 'A')
                    {
                        $cat=['A','B+','B'];
                        $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',$fid]])->whereIn('category',$cat)->get()->toarray();
                        return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                    if($relations->category == 'B+')
                    {
                        $cat=['B+','B'];
                        $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',$fid]])->whereIn('category',$cat)->get()->toarray();
                        return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                    if($relations->category == 'B')
                    {
                    $products = Product::where('seller_id',$req->seller_id)->where([['isDisabled','0'],['fid',$fid]])->where('category',$relations->category)->get()->toarray();
                    return response()->json(['error' => false ,'Knock'=>true,'accepted'=>$ac,'active'=>$active,'relation'=>true ,'data'=>$products,'banner'=>$banner],200);
                    }
                }
            }
            else {
                return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
            }
        }

}

// public function demote(Request $req,$id)
// {
//     $validator = Validator::make($req->all(), [
//         'seller_id' => 'required',
//     ]);
//     if ($validator->fails()) {
//         return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
//     }
//     $relation_data=[];
//     $relation_record=CustomerCategoryRelationship::where([['cust_id',$id],['seller_id',$req->seller_id]])->first();
//     if(!empty($relation_record))
//     {
//         if(($relation_record['category'])=="A+")
//         {
//             $category="A";
//             $relation_data=[
//                 'seller_id'=>$relation_record->seller_id,
//                 'cust_id'=>$relation_record->cust_id,
//                 'category'=>$category,
//                 'isBlocked'=>$relation_record->isBlocked,
//             ];
//         }
//         elseif(($relation_record['category'])=="A")
//         {
//             $category="B";
//             $relation_data=[
//                 'seller_id'=>$relation_record->seller_id,
//                 'cust_id'=>$relation_record->cust_id,
//                 'category'=>$category,
//                 'isBlocked'=>$relation_record->isBlocked,

//             ];
//         }
//     }
//     else{
//         return response()->json(['error' => true ,'message'=>'User not found']);
//     }
//     $relation_update=CustomerCategoryRelationship::where('id',$relation_record['id'])->update($relation_data);
//     if($relation_update==1)
//     {
//         return response()->json(['error' => false ,'message'=>'Customer Demoted'],200);
//     }
//     else{
//         return response()->json(['error' => true ,'message'=>'Record not found']);
//     }
// }

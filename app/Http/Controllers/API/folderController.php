<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\folderModel;
use Validator;
use App\Product;
use App\User;
use App\emp_sel_rel;

class folderController extends Controller
{
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fname'=>'required',
            'sid'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $User=User::find($req->sid);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$req->sid)->first();
            $req->sid=$seller->seller_id;
        }
        $folder=folderModel::where([['fname',$req->fname],['sid',$req->sid]])->first();
        if($folder)
        {
            return response()->json(['error' => true ,'message'=>'Folder already exist with this name..!'], 409);
        }
        $fd=new folderModel;
        $fd->fname=$req->fname;
        $fd->sid=$req->sid;
        if($fd->save())
        {
            return response()->json(['error' => false ,'message'=>'New folder created successfully..'], 200);
        }
        return response()->json(['error' => true ,'message'=>'Somthing went\'s wrong..!'], 500);
    }
    public function show($sid)
    {
        $user=User::find($sid);
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$sid)->first();
            $sid=$seller->seller_id;

        }
        $foldes=folderModel::where('sid',$sid)->get();
        foreach($foldes as $f)
        {
            $product=Product::where('fid',$f->id)->orderBy('created_at','desc')->first();
            if($product)
            {
                $im=explode(',',$product->image);
                $f->image=$im[0];
            }
            else{
                $f->image="default.png";
            }
        }
        if($foldes)
        {
            return response()->json(['error' => false ,'foldes'=>$foldes], 200);
        }
        return response()->json(['error' => true ,'message'=>'Folder not found of this seller..!'], 400);
    }
    public function prodShow($fid)
    {
        $products=Product::where('fid',$fid)->orderBy('created_at','desc')->get()->toarray();
        if($products)
        {
            return response()->json(['error' => false ,'foldes'=>$products], 200);
        }
        return response()->json(['error' => true ,'message'=>'Folder is empty..!'], 400);
    }
    public function edit(Request $req,$fid)
    {
        $validator = Validator::make($req->all(), [
            'fname'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $f=folderModel::find($fid);
        if($f)
        {
            $f->fname=$req->fname;
            $f->save();
            return response()->json(['error' => false ,'message'=>'folder name changed..'], 200);
        }
        return response()->json(['error' => true ,'message'=>'Folder not found..'], 400);
    }
    public function delete($fid)
    {
        $f=folderModel::find($fid);
        if($f)
        {
            $product=Product::where('fid',$f->id)->update(['fid'=>null]);
            $f->delete();
            return response()->json(['error' => false ,'message'=>'folder Deleted successfully..'], 200);
        }
        return response()->json(['error' => true ,'message'=>'Folder not found..'], 400);
    }
}

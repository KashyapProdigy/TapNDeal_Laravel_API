<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\folderModel;
use Validator;
use App\Product;

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
        $foldes=folderModel::where('sid',$sid)->get()->toarray();
        if($foldes)
        {
            return response()->json(['error' => false ,'foldes'=>$foldes], 200);
        }
        return response()->json(['error' => true ,'message'=>'Folder not found of this seller..!'], 400);
    }
    public function prodShow($fid)
    {
        $products=Product::where('fid',$fid)->get()->toarray();
        if($products)
        {
            return response()->json(['error' => false ,'foldes'=>$products], 200);
        }
        return response()->json(['error' => true ,'message'=>'Folder is empty..!'], 400);
    }
}

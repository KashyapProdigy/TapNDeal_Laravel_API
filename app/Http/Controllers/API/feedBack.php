<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\feedBackModel;
use Validator;
class feedBack extends Controller
{
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'uid' => 'required',
            'msg'=>'required',
            
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $f=new feedBackModel;
        $f->uid=$req->uid;
        $f->msg=$req->msg;
        $f->date_time=date('Y-m-d H:i:s');
        $f->save();
        return response()->json(['error' => false ,'message'=>'FeedBack send ...!'], 200);
    }
}

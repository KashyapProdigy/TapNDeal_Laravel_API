<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\feedBack;

class feedBack extends Controller
{
    public function create(Request $req)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'msg'=>'required',
            
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

    }
}

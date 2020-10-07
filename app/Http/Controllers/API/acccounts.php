<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class acccounts extends Controller
{
    public function new(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'no_of_acc'=>'required',
            'sid'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $seller=User::find($req->sid);
        if($seller)
        {
            $end=$seller->end_date;
        }
    }
}

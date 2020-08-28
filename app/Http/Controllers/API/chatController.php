<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Chat;
use Carbon\Carbon;
use Validator;

class chatController extends Controller
{
    public function show($id)
    {
        $chat=Chat::where('id',$id)->get();
        if(!empty($chat))
        {
            return response()->json(['error' => false ,'data'=>$chat],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'sender' => 'required',
            'receiver' => 'required',
            'msg' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $chat=new Chat;
        $chat->sender=$req->sender;
        $chat->receiver=$req->receiver;
        $chat->msg=$req->msg;
        $chat->date_time=Carbon::now();


        if($chat->save())
        {
            return response()->json(['error' => false ,'message'=>'Inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

    }
    public function update(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'sender' => 'required',
            'receiver' => 'required',
            'msg' => 'required',
            'date_time'=>'required|date_format:Y-m-d H:i:s'

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $chat_data=[
        'sender'=>$req->sender,
        'receiver'=>$req->receiver,
        'msg'=>$req->msg,
        'date_time'=>$req->date_time,
        ];

        $chat_update=Chat::where('id',$id)->update($chat_data);
        if($chat_update==1)
        {
            return response()->json(['error' => false ,'message'=>' Chat updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found or Record already updated'],500);

    }
    public function delete($id)
    {
        $chat_del=Chat::find($id);
        if($chat_del)
        {
            $chat_del->delete();
            return response()->json(['error' => false ,'message'=>'Chat Record Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }


}

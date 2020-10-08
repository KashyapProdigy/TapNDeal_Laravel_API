<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Chat;
use Carbon\Carbon;
use Validator;
use App\Notifications\ChatNoti;
use App\User;
class chatController extends Controller
{
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'sender' => 'required',
            'receiver' => 'required',
            'msg' => 'required',
            'send_by'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $c=Chat::where([['sender',$req->sender],['receiver',$req->receiver]])->first();
        if($c == null)
        {
            $c=Chat::where([['sender',$req->receiver],['receiver',$req->sender]])->first();
        }
        if($c == null)
        {
            $chat =new Chat;
            $chat->sender=$req->sender;
            $chat->receiver=$req->receiver;
            $chat->msg=$req->msg;
            $chat->send_by=$req->send_by;
            $chat->date_time=date('Y-m-d H:i:s');
            if($chat->save())
            {
                $usr=User::find($req->receiver);
                $send=User::find($req->send_by);
                $msg="New message by ".$send->name." ".$req->msg;
                $arr=['msg'=>$msg];
                \Notification::send($usr, new ChatNoti($arr));
                return response()->json(['error' => false ,'message'=>"Message stored successfully"], 200);
            }
            return response()->json(['error' => true ,'message'=>'somthing went wrong'], 500);
        }
        $chat=Chat::find($c['id']);
        $chat->msg=$req->msg;
        $chat->send_by=$req->send_by;
        $chat->date_time=date('Y-m-d H:i:s');
        $chat->save();
        $usr=User::find($req->receiver);
        $send=User::find($req->send_by);
        $msg="New message by ".$send->name." ".$req->msg;
        $arr=['msg'=>$msg];
        \Notification::send($usr, new ChatNoti($arr));
        return response()->json(['error' => false ,'message'=>"Message stored successfully"], 200);
    }
    public function list($uid)
    {
        $list=Chat::where('sender',$uid)->orwhere('receiver',$uid)->get();
        if(count($list)>0)
        {
            return response()->json(['error' => false ,'data'=>$list], 200);
        }
        return response()->json(['error' => true ,'message'=>"No chat found"],400);
    }
    // public function show($id)
    // {
    //     $chat=Chat::where('id',$id)->get();
    //     if(!empty($chat))
    //     {
    //         return response()->json(['error' => false ,'data'=>$chat],200);
    //     }
    //     return response()->json(['error' => true ,'message'=>'Invalid Id']);
    // }
    // public function create(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'sender' => 'required',
    //         'receiver' => 'required',
    //         'msg' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
    //     }
    //     $chat=new Chat;
    //     $chat->sender=$req->sender;
    //     $chat->receiver=$req->receiver;
    //     $chat->msg=$req->msg;
    //     $chat->date_time=Carbon::now();


    //     if($chat->save())
    //     {
    //         return response()->json(['error' => false ,'message'=>'Inserted Successfully'],200);
    //     }
    //     return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

    // }
    // public function update(Request $req,$id)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'sender' => 'required',
    //         'receiver' => 'required',
    //         'msg' => 'required',
    //         'date_time'=>'required|date_format:Y-m-d H:i:s'

    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
    //     }
    //     $chat_data=[
    //     'sender'=>$req->sender,
    //     'receiver'=>$req->receiver,
    //     'msg'=>$req->msg,
    //     'date_time'=>$req->date_time,
    //     ];

    //     $chat_update=Chat::where('id',$id)->update($chat_data);
    //     if($chat_update==1)
    //     {
    //         return response()->json(['error' => false ,'message'=>' Chat updated Successfully'],200);
    //     }
    //     return response()->json(['error' => true ,'message'=>'Record not found or Record already updated'],500);

    // }
    // public function delete($id)
    // {
    //     $chat_del=Chat::find($id);
    //     if($chat_del)
    //     {
    //         $chat_del->delete();
    //         return response()->json(['error' => false ,'message'=>'Chat Record Deleted'],200);
    //     }
    //     return response()->json(['error' => true ,'message'=>'Record not found']);
    // }


}

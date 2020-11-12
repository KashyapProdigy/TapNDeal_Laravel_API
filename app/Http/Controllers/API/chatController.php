<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Chat;
use Carbon\Carbon;
use Validator;
use App\Notification;
use App\Notifications\onesignal;
use App\User;
use App\AgentCategoryRelationship;
use App\CustomerCategoryRelationship;
use App\CustomerAgentRelationship;
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
                $data['title']='Tap N Deal';
                $data['msg']="New message by ".$send->name." ".$req->msg;

                \Notification::send($usr, new onesignal($data));

                $n=new Notification;
                $n->receiver=$usr->id;
                $n->noti_for=$chat->id;
                $n->description=$data['msg'];
                $n->type="Chat";
                $n->date_time=date('Y-m-d H:i:s');
                $n->save();
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
        $data['title']='Tap N Deal';
        $data['msg']="New message by ".$send->name." ".$req->msg;

        \Notification::send($usr, new onesignal($data));

        $n=new Notification;
        $n->receiver=$usr->id;
        $n->noti_for=$chat->id;
        $n->description=$data['msg'];
        $n->type="Chat";
        $n->date_time=date('Y-m-d H:i:s');
        $n->save();
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
    public function connectedUser($uid)
    {
        $user=User::find($uid);
        if($user->type_id==1)
        {
            $users['agents']=AgentCategoryRelationship::join('users','users.id','agent_id')
            ->join('citys','citys.id','city_id')
            ->join('company_info','company_info.sid','users.id')
            ->select('users.*','company_info.cname','city_name')
            ->where('seller_id',$uid)->get();
            $users['buyers']=CustomerCategoryRelationship::join('users','users.id','cust_id')
            ->join('citys','citys.id','city_id')
            ->join('company_info','company_info.sid','users.id')
            ->select('users.*','company_info.cname','city_name')
            ->where('seller_id',$uid)->get();
            return response()->json(['error' => false ,'users'=>$users], 200);
        }
        if($user->type_id==2)
        {
            $users['sellers']=AgentCategoryRelationship::join('users','users.id','seller_id')
            ->join('citys','citys.id','city_id')
            ->join('company_info','company_info.sid','users.id')
            ->select('users.*','company_info.cname','city_name')
            ->where('agent_id',$uid)->get();
            $users['buyers']=CustomerAgentRelationship::join('users','users.id','cust_id')
            ->join('citys','citys.id','city_id')
            ->join('company_info','company_info.sid','users.id')
            ->select('users.*','company_info.cname','city_name')
            ->where('agent_id',$uid)->get();
            return response()->json(['error' => false ,'users'=>$users], 200);
        }
        if($user->type_id==3)
        {
            $users['sellers']=CustomerCategoryRelationship::join('users','users.id','seller_id')
            ->join('citys','citys.id','city_id')
            ->join('company_info','company_info.sid','users.id')
            ->select('users.*','company_info.cname','city_name')
            ->where('cust_id',$uid)->get();
            $users['agents']=CustomerAgentRelationship::join('users','users.id','agent_id')
            ->join('citys','citys.id','city_id')
            ->join('company_info','company_info.sid','users.id')
            ->select('users.*','company_info.cname','city_name')
            ->where('cust_id',$uid)->get();
            return response()->json(['error' => false ,'users'=>$users], 200);
        }
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

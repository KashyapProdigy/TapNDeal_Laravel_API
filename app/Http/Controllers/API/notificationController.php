<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notification;
use Carbon\Carbon;
use Validator;
use App\User;
use App\emp_sel_rel;
class notificationController extends Controller
{
    public function show($id)
    {
        $notification=Notification::where('receiver',$id)->get()->toarray();
        if(!empty($notification))
        {
            return response()->json(['error' => false ,'data'=>$notification],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'sender' => 'required',
            'receiver' => 'required',
            'description'=>'required',
            'type'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $notification=new Notification;
        $notification->sender=$req->sender;
        $notification->receiver=$req->receiver;
        $notification->description=$req->description;
        $notification->type=$req->type;
        $notification->date_time=Carbon::now();
        $notification->isRead=0;

        if($notification->save())
        {
            return response()->json(['error' => false ,'message'=>' Notification inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Somthing wents wrong'],500);

    }
    public function update(Request $req,$id)
    {
        $validator = Validator::make($req->all(), [
            'sender' => 'required',
            'receiver' => 'required',
            'description'=>'required',
            'type'=>'required',
            'isRead'=>'required|in:1,0',
            'date_time'=>'required|date_format:Y-m-d H:i:s'

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $notification_data=[
        'sender'=>$req->sender,
        'receiver'=>$req->receiver,
        'description'=>$req->description,
        'type'=>$req->type,
        'isRead'=>$req->isRead,
        'date_time'=>$req->date_time,
        ];

        $notification_update=Notification::where('id',$id)->update($notification_data);
        if($notification_update==1)
        {
            return response()->json(['error' => false ,'message'=>' Notification updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);

    }
    public function delete($id)
    {
        $noti_del=Notification::find($id);
        if($noti_del)
        {
            $noti_del->delete();
            return response()->json(['error' => false ,'message'=>'Notification Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }
    public function read($id)
    {
        $User=User::find($id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;   
        }
        $noti=Notification::where('receiver',$id)->update(['isRead'=>1]);
        return response()->json(['error' => false ,'message'=>'Notification Readed'],200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\temp_req;
use Validator;
use App\User;
use App\temp_req_product;
use App\Notification;
use App\Product;
use App\emp_sel_rel;
use App\Notifications\TempReq;
class tempReqController extends Controller
{
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            
            'request_by' => 'required',
            'request_to'=>'required',
            'request_for' => 'required',
            'remarks' => 'required',
            
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        // $se=json_decode($req->request_to);
        $se=implode(',',$req->request_to);
        $se=explode(',',$se);
        $agent=User::find($req->request_by);
        foreach($se as $seller)
        {
            $t=new temp_req;
            $t->req_by=$req->request_by;
            $t->req_for=$req->request_for;
            $t->req_to=$seller;
            $t->remarks=$req->remarks;
            $t->save();

            $usr=User::find($seller);
            // $cust=User::find($req->cust_id);
            $msg="Temporary Request has been created by ".$agent->name;
            $data['msg']=$msg;
            $data['id']=$usr->id;
            \onesignal::sendNoti($data);

            $n=new Notification;
            $n->receiver=$usr->id;
            $n->noti_for=$t->id;
            $n->description=$msg;
            $n->type="New Temporary Request";
            $n->date_time=date('Y-m-d H:i:s');
            $n->save();

            $salesman=emp_sel_rel::join('users','users.id','emp_sel_rel.emp_id')->where([['type_id',4],['seller_id',$seller]])->first();
            if($salesman)
            {
                $usr=User::find($salesman->id);
                $msg="Temporary Request has been created by ".$agent->name;
                $data['msg']=$msg;
                $data['id']=$usr->id;
                \onesignal::sendNoti($data);

                $n=new Notification;
                $n->receiver=$usr->id;
                $n->noti_for=$t->id;
                $n->description=$msg;
                $n->type="New Temporary Request";
                $n->date_time=date('Y-m-d H:i:s');
                $n->save();
            }
        }
        $usr=User::find($req->request_for);
        // $cust=User::find($req->cust_id);
        $msg="Temporary Request has been created by ".$agent->name;
        $data['msg']=$msg;
        $data['id']=$usr->id;
        \onesignal::sendNoti($data);

        $n=new Notification;
        $n->receiver=$usr->id;
        $n->noti_for=$t->id;
        $n->description=$msg;
        $n->type="New Temporary Request";
        $n->date_time=date('Y-m-d H:i:s');
        $n->save();
        return response()->json(['error' => false ,'message'=>'Temporary Request Added..'], 200);
    }
    public function show($bid)
    {
        $tr=temp_req::where('req_for',$bid)->orderBy('created_at','desc')->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')->select('temp_req.*','temp_req_pro.end_period')->where('temp_req.id',$t['id'])->first();
            if($temp)
            {
                $end=$temp['end_period'];
                if($end<date('Y-m-d H:i:s'))
                {
                    $expired=true;
                }
                else{
                    $expired=false;
                }
            }
            else{
                $temp=temp_req::where('id',$t['id'])->first();
                $expired=false;
            }
            
            $temp['agent']=User::where('users.id',$t['req_by'])->join('company_info','sid','users.id')->select('users.id','company_info.cname as name','mobile')->first();
            $temp['seller']=User::where('users.id',$t['req_to'])->join('company_info','sid','users.id')->select('users.id','company_info.cname as name','mobile')->first();
            $temp['expired']=$expired;
            $rec[]=$temp;
        }
        if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 200);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this buyer..'], 400);
    }
    public function agentShow($aid)
    {
        $tr=temp_req::where('req_by',$aid)->orderBy('created_at','desc')->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')->select('temp_req.*','temp_req_pro.end_period')->where('temp_req.id',$t['id'])->first();
            if($temp)
            {
                $end=$temp['end_period'];
                if($end<date('Y-m-d H:i:s'))
                {
                    $expired=true;
                }
                else{
                    $expired=false;
                }
            }
            else{
                $temp=temp_req::where('id',$t['id'])->first();
                $expired=false;
            }
            
            $temp['buyer']=User::where('users.id',$t['req_for'])->join('company_info','sid','users.id')->select('users.id','company_info.cname as name','mobile')->first();
            $temp['seller']=User::where('users.id',$t['req_to'])->join('company_info','sid','users.id')->select('users.id','company_info.cname as name','mobile')->first();
            $temp['expired']=$expired;
            $rec[]=$temp;
        }
         if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 200);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this Agent..'], 400);
    }
    public function sellerShow($sid)
    {
        $user=User::find($sid);
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$sid)->first();
            $sid=$seller->seller_id;
        }
        $tr=temp_req::where('req_to',$sid)->orderBy('created_at','desc')->get();
        $temp=array();
        $rec=array();
        foreach($tr as $t)
        {
            $temp=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')->select('temp_req.*','temp_req_pro.end_period')->where('temp_req.id',$t['id'])->first();
            if($temp)
            {
                $end=$temp['end_period'];
                if($end<date('Y-m-d H:i:s'))
                {
                    $expired=true;
                }
                else{
                    $expired=false;
                }
                
            }
            else{
                $temp=temp_req::where('id',$t['id'])->first();
                $expired=false;
            }
            $temp['buyer']=User::where('users.id',$t['req_for'])->join('company_info','sid','users.id')->select('users.id','company_info.cname as name','mobile')->first();
            $temp['agent']=User::where('users.id',$t['req_by'])->join('company_info','sid','users.id')->select('users.id','company_info.cname as name','mobile')->first();
            $temp['expired']=$expired;
            $respone=temp_req_product::where([['sid',$sid],['trid',$t['id']]])->first();
            if($respone)
            {
                $temp['responded']=true;    
            }
            else{
                $temp['responded']=false;    
            }
            
            $rec[]=$temp;
        }
         if($rec != null)
            return response()->json(['error' => false ,'data'=>$rec], 200);
        
        return response()->json(['error' => true ,'message'=>'Temporary Request not found of this Seller..'], 400);
    }
    public function responseReq(Request $req)
    {
        $validator = Validator::make($req->all(), [
            
            'sid' => 'required|numeric',
            'pid'=>'required',
            'trid'=>'required|numeric',
            'time_period'=>'required|numeric'
            
        ],[
            'sid.required'=>'Seller id is required..',
            'pid.required'=>'Products ids are required',
            'trid.required'=>'temporary request id required'
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
        $tempReq=temp_req::find($req->trid);
        if($tempReq)
        {
            $tempReq->isResponded=1;
            if($tempReq->save())
            {
                $pi=implode(',',$req->pid);
                
                    $tr=new temp_req_product;
                    $tr->sid=$req->sid;
                    $tr->trid=$req->trid;
                    $tr->pid=$pi;
                    $tr->end_period=date('Y-m-d H:i:s', strtotime("+".$req->time_period." days"));
                    $tr->save();

                    $usr=User::find($tempReq->req_for);
                    $seller=User::find($req->sid);
                    $msg=$seller->name." respond to your temporary requirement";
                    $data['msg']=$msg;
                    $data['id']=$usr->id;
                    \onesignal::sendNoti($data);

                    $n=new Notification;
                    $n->receiver=$usr->id;
                    $n->noti_for=$tr->id;
                    $n->description=$msg;
                    $n->type="Temporary Request Response";
                    $n->date_time=date('Y-m-d H:i:s');
                    $n->save();
                return response()->json(['error' => false ,'message'=>"Response Added successfully.."], 200);
            }
                
        }
        return response()->json(['error' => true ,'message'=>"Somethings went wrong."], 400);
    }
    public function showResponseBuyer($bid,$trid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->where('req_for',$bid)
        ->where('temp_req.id',$trid)
        ->get();
        $li=array();
        $list=array();
        $prod=array();
        foreach($data as $d)
        {
            $prdct=explode(',',$d['pid']);
            $list['seller']=User::where('id',$d['sid'])->first();
            foreach($prdct as $p)
            {
                $prod[]=Product::where('id',$p)->get();
            }
            
            $list['seller']['product']=$prod;
            $li[]=$list;
            $prod=null;
            
        }
        
        if(count($data)>0)
        {
            return response()->json(['error' => false ,'message'=>$li], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this buyer not found..'], 400);
    }
    public function showResponseAgent($aid)
    {
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->where('req_by',$aid)
        ->get();
        $li=array();
        $list=array();
        $prod=array();
        foreach($data as $d)
        {
            $prdct=explode(',',$d['pid']);
            $list['seller']=User::where('id',$d['sid'])->first();
            foreach($prdct as $p)
            {
                $prod[]=Product::where('id',$p)->get();
            }
            
            $list['seller']['product']=$prod;
            $li[]=$list;
            $prod=null;
            
        }
        if(count($data)>0)
        {
            return response()->json(['error' => false ,'message'=>$li], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this agent not found..'], 400);
    }
    public function showResponseSeller($sid,$trid)
    {
        $User=User::find($sid);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$sid)->first();
            $sid=$seller->seller_id;   
        }
        $data=temp_req_product::join('temp_req','temp_req.id','temp_req_pro.trid')
        ->where('req_to',$sid)
        ->where('temp_req.id',$trid)
        ->first();
        $end=$data['end_period'];
        if($end<date('Y-m-d H:i:s'))
        {
            $expired=true;
        }
        else{
            $expired=false;
        }
        $li=array();
        $list=array();
        $prod=array();
        $prdct=explode(',',$data['pid']);
        foreach($prdct as $p)
        {
            $prod[]=Product::where('id',$p)->get();
        }
        if($data)
        {
            return response()->json(['error' => false,'expired'=>$expired,'message'=>$prod], 200);
        }
        return response()->json(['error' => true ,'message'=>'Respone of this buyer not found..'], 400);
    }
    public function delete($trid)
    {
        $tr=temp_req::find($trid);   
        if($tr)
        {
            $tr->delete();
            return response()->json(['error' => false ,'message'=>'Temporary Requirement deleted'], 200);
        }
        return response()->json(['error' => true ,'message'=>'Temporary Requirement not found'], 400);
    }
    public function revive(Request $req,$trid)
    {
        $validator = Validator::make($req->all(), [
            'sid' => 'required',
            'time_period'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        
        $data=temp_req_product::where([['trid',$trid],['sid',$req->sid]])->first();
        if($data)
        {
            $end_date=date('Y-m-d H:i:s', strtotime("+".$req->time_period." days"));
            $data->end_period=$end_date;
            if($data->save())
            {
                $tempReq=temp_req::find($trid);
                if($tempReq)
                {
                    $tempReq->isRevive=1;
                    $tempReq->save();
                    $usr=User::find($tempReq->req_for);
                    $seller=User::find($tempReq->req_to);
                    $msg="Temporary Requirement has once again revive by ".$seller->name;
                    $data['msg']=$msg;
                    $data['id']=$usr->id;
                    \onesignal::sendNoti($data);

                    $n=new Notification;
                    $n->receiver=$usr->id;
                    $n->noti_for=$tempReq->id;
                    $n->description=$msg;
                    $n->type="Temporary Request Revive";
                    $n->date_time=date('Y-m-d H:i:s');
                    $n->save();
                    return response()->json(['error' => false ,'message'=>'Temporary Requirement revive successfully..'], 200);
                }
            }
            return response()->json(['error' => true ,'message'=>'somthing wents wrong..!'], 500);
        }
       
    }
    public function showStatusWise($uid)
    {
        $user=User::find($uid);   
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6)
        {
            $seller=emp_sel_rel::where('emp_id',$uid)->first();
            $uid=$seller->seller_id;
            $user=User::find($uid);
            
        }
        if($user)
        {
            if($user->type_id==1)
            {
                $tr=temp_req::where([['req_to',$uid],['isActive',1],['isResponded',0]])->orderBy('created_at','desc')->get();
                $temp=array();
                $new=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['buyer']=User::where('id',$t['req_for'])->select('id','name','mobile')->first();
                    $temp['agent']=User::where('id',$t['req_by'])->select('id','name','mobile')->first();
                    $new[]=$temp;
                }
                $today=date('Y-m-d H:i:s');
                $tr=temp_req_product::join('temp_req','trid','temp_req.id')->where([['req_to',$uid],['end_period','>',$today],['isResponded',1]])->orderBy('temp_req.created_at','desc')->get();
                $temp=null;
                $active=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['buyer']=User::where('id',$t['req_for'])->select('id','name','mobile')->first();
                    $temp['agent']=User::where('id',$t['req_by'])->select('id','name','mobile')->first();
                    $active[]=$temp;
                }

                
                $tr=temp_req_product::join('temp_req','trid','temp_req.id')->where([['req_to',$uid],['end_period','<',$today]])->orderBy('temp_req.created_at','desc')->get();
                $temp=null;
                $past=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['buyer']=User::where('id',$t['req_for'])->select('id','name','mobile')->first();
                    $temp['agent']=User::where('id',$t['req_by'])->select('id','name','mobile')->first();
                    $past[]=$temp;
                }
                return response()->json(['error' => false ,'new'=>$new ,'active'=>$active,'past'=>$past], 200);
                
                return response()->json(['error' => true ,'message'=>'Temporary Request not found of this Seller..'], 400);      
            }
            if($user->type_id==2 || $user->type_id==8)
            {
                $tr=temp_req::where([['req_by',$uid],['isActive',1],['isResponded',0]])->orderBy('created_at','desc')->get();
                $temp=array();
                $new=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['buyer']=User::where('id',$t['req_for'])->select('id','name')->first();
                    $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
                    $new[]=$temp;
                }
                $today=date('Y-m-d H:i:s');
                $tr=temp_req_product::join('temp_req','trid','temp_req.id')->where([['req_by',$uid],['end_period','>',$today],['isResponded',1]])->orderBy('temp_req.created_at','desc')->get();
                $temp=array();
                $active=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['buyer']=User::where('id',$t['req_for'])->select('id','name')->first();
                    $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
                    $active[]=$temp;
                }
                $tr=temp_req_product::join('temp_req','trid','temp_req.id')->where([['req_by',$uid],['end_period','<',$today]])->orderBy('temp_req.created_at','desc')->get();
                $temp=array();
                $past=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['buyer']=User::where('id',$t['req_for'])->select('id','name')->first();
                    $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
                    $past[]=$temp;
                }
                return response()->json(['error' => false ,'new'=>$new,'active'=>$active,'past'=>$past], 200);
            }
            if($user->type_id==3)
            {
                $tr=temp_req::where([['req_for',$uid],['isActive',1],['isResponded',0]])->orderBy('created_at','desc')->get();
                $temp=array();
                $new=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['agent']=User::where('id',$t['req_by'])->select('id','name')->first();
                    $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
                    $new[]=$temp;
                }
                $today=date('Y-m-d H:i:s');
                $tr=temp_req_product::join('temp_req','trid','temp_req.id')->where([['req_for',$uid],['end_period','>',$today],['isResponded',1]])->orderBy('temp_req.created_at','desc')->get();
                $temp=array();
                $active=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['agent']=User::where('id',$t['req_by'])->select('id','name')->first();
                    $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
                    $active[]=$temp;
                }
                $tr=temp_req_product::join('temp_req','trid','temp_req.id')->where([['req_for',$uid],['end_period','<',$today]])->orderBy('temp_req.created_at','desc')->get();
                $temp=array();
                $past=array();
                foreach($tr as $t)
                {
                    $temp=temp_req::where('id',$t['id'])->first();
                    $temp['agent']=User::where('id',$t['req_by'])->select('id','name')->first();
                    $temp['seller']=User::where('id',$t['req_to'])->select('id','name')->first();
                    $past[]=$temp;
                }
                return response()->json(['error' => false ,'new'=>$new,'active'=>$active,'past'=>$past], 200);
            }
        }
        else{
            return response()->json(['error' => true ,'message'=>'Invalid user id..'], 400);
        }
    }
}

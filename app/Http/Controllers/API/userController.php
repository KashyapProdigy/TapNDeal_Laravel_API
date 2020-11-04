<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\ProfileViewLog;
use Illuminate\Support\Facades\DB;
use Validator;
use App\com_info;
use App\emp_sel_rel;
use App\custome_agent;
use Illuminate\Support\Facades\Storage;
use File;
use App\Order;
use App\temp_req;
use App\AgentKnock;
use App\CustomerKnock;
use App\Banners;
use App\Product;

class userController extends Controller
{
    public $successStatus = 200;
    public function generateRefCode($name)
    {
        $i=0;
        $ref = strtoupper(substr($name, 0, 2)).date('dmy').strtoupper(substr($name, -1, 1));
        $u=User::where('ref_code',$ref)->count();
        while($u>0){
            $name=$name."".$i++;
            $ref = strtoupper(substr($name, 0, 2)).date('dmy').strtoupper(substr($name, -1, 1));
            $u=User::where('ref_code',$ref)->count();
        }
        return $ref;
    }
    public function login()
    {
        // $psw=Hash::make(request('password'));
        $user=User::where([['mobile',request('mobile')],[DB::raw('BINARY `password`'), request('password')],['isDeleted',0]])->count();
        if($user==1){

            // $success['token'] =  $user->createToken('MyApp')-> accessToken;
            // $user = Auth::user();
            $update=[
                'firebase_token'=>request('f_token'),
                'login_token'=>request('l_token'),
                'msg_token'=>request('m_token')
            ];
            User::where([['mobile',request('mobile')],['isDeleted',0]])->update($update);

            $ir=\DB::table('appSetting')->first();

            $user = User::where([['mobile',request('mobile')],['isDeleted',0]])->first();
            if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
            {
                $seller=emp_sel_rel::where('emp_id',$user->id)->first();
                $user->seller_id=$seller->seller_id;
            }
            else{
                $user->seller_id=$user->id;
            }
            $user->isReportShow=$ir->isReportShow;
            return response()->json(['error' => false ,'data' => $user], $this-> successStatus);
        }
        else{
            return response()->json(['error'=> true , 'message' => 'unauthorised'], 401);
        }
    }
    public function newAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email'=>'required',
            'mobile'=>'required',
            'pass'=>'required',
            'sid'=>'required',
            'type_id'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $user1=User::where([['mobile',$request->mobile],['isDeleted',0]])->first();
        if($user1)
        {
            return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 401);
            if($user1->type_id!=3)
            {
                return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 401);
            }
            else if($user1->type_id==3 && $request->type_id==3)
            {
                return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 401);
            }
            else{
                $user1->isDeleted=1;
                $user1->save();
            }
        }
        $seller=com_info::join('users','company_info.sid','users.id')->where([['users.id',$request->sid],['type_id',1]])->select('company_info.*','users.*')->first();

        $e_count=emp_sel_rel::where('seller_id',$request->sid)->count();
        if($e_count>=$seller['acc_allow'])
        {
            return response()->json(['error' => true ,'message'=>'This seller already used his all account'],409);
        }
        if($seller)
        {
            $ref=$this->generateRefCode($request->name);
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->password = $request->pass;
            $user->type_id = $request->type_id;
            $user->city_id=$seller->city_id;
            $user->state_id=$seller->state_id;
            $user->isVerified = 1;
            $user->firebase_token=$request->f_token;
            $user->device_id=$request->d_id;
            $user->ref_code=$ref;
            if(!$request->b_scope)
            {
                $request->b_scope=" ";
            }
            $user->business_scope=$request->b_scope;
            $user->save();
            $es=new emp_sel_rel;
            $es->emp_id=$user->id;
            $es->seller_id=$seller->sid;
            $es->save();
            $ci=new com_info;
            $ci->cname=$seller->cname;
            $ci->pan=$seller->pan;
            $ci->gst=$seller->gst;
            $ci->address=$seller->address;
            $ci->sid=$user->id;
            $ci->save();
            return response()->json(['error' => false ,'message'=>'User Added Successfully'],200);
        }
        return response()->json(['error' => false ,'message'=>'Invalid seller id..'],400);
    }
    public function tokenCheck(Request $req)
    {
        $record=User::where([['id',$req->uid],['login_token',$req->l_token]])->count();
        if($record>0)
        {
            return response()->json(['error'=> false , 'record' => true],200 );
        }
        return response()->json(['error'=> true , 'record' => false],400 );
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile'=>'required|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 200);
        }
        if($request->type_id==1 || $request->type_id==2 || $request->type_id==3)
        {
            $validator = Validator::make($request->all(), [
                'cname'=>'required',
                'address'=>'required',
                'b_scope'=>'required'
                ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 200);
            }
        }
        $user1=User::where('mobile',$request->mobile)->first();
        if($user1)
        {
            return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 401);
            if($user1->type_id!=3)
            {
                return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 401);
            }
            else if($user1->type_id==3 && $request->type_id==3)
            {
                return response()->json(['error' => true ,'message'=>'This mobile number already registered..!'], 401);
            }
            else{
                $user1->isDeleted=1;
                $user1->save();
            }
        }
        if($request->type_id==2 || $request->type_id==8)
        {
            $ag=custome_agent::where('mobile',$request->mobile)->first();
            if($ag!=null){
                $ref=$ag['ref_code'];
            }
            else{
                $ref=$this->generateRefCode($request->name);
            }
        }
        else{
            $ref=$this->generateRefCode($request->name);
        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password = $request->password;
        $user->type_id = $request->type_id;
        $user->city_id = $request->city_id;
        $user->state_id = $request->state_id;
        $user->isVerified = 1;
        $user->firebase_token=$request->f_token;
        $user->device_id=$request->d_id;
        $user->ref_code=$ref;
        if(!$request->b_scope)
        {
            $request->b_scope=" ";
        }
        $user->business_scope=$request->b_scope;
        // $user->end_date=date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + 7 days'));
        $user->end_date="2020-12-31";
        if($request->sel_ref!="")
        {
            $seller=com_info::join('users','company_info.sid','users.id')->where([['ref_code',$request->sel_ref],['type_id',1]])->select('users.id as sid','users.city_id','users.state_id','users.acc_allow','company_info.*')->first();
            if($seller == null)
            {
                return response()->json(['error' => true ,'message'=>'invalid Reference code..!'],400);
            }
            if($request->type_id==1 || $request->type_id==2 || $request->type_id==3)
            {
                $user->refered_by=$request->sel_ref;
            }
            else{
                $e_count=emp_sel_rel::where('seller_id',$seller->sid)->count();
                if($e_count>=$seller['acc_allow'])
                {
                    return response()->json(['error' => true ,'message'=>'This seller already used his all account'],401);
                }
                else{
                    $user->city_id=$seller->city_id;
                    $user->state_id=$seller->state_id;
                }
            }

        }
        if($user->save())
        {
            if($request->type_id==4 || $request->type_id==5 || $request->type_id==6 || $request->type_id==8)
            {
                $es=new emp_sel_rel;

                $es->emp_id=$user->id;
                $es->seller_id=$seller->sid;
                $es->save();
                $ci=new com_info;
                $ci->cname=$seller->cname;
                $ci->pan=$seller->pan;
                $ci->gst=$seller->gst;
                $ci->address=$seller->address;
                $ci->sid=$user->id;
                $ci->save();
            }
            else{
            $ci=new com_info;
            $ci->cname=$request->cname;
            $ci->pan=$request->pan;
            $ci->gst=$request->gst;
            $ci->address=$request->address;
            $ci->sid=$user->id;
            $ci->save();
            }
            return response()->json(['error' => false ,'message'=>'User Added Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
    }

    public function resetPassword($id, Request $req)
    {
        $validator = Validator::make($req->all(), [
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        $user=User::find($id);
        $user=$user->makeVisible(['password']);

        if($user != null)
        {
            if($user->password == $req->password)
            {
                return response()->json(['error' => true,'same' => true ,'message'=>'Password Cannot Be Same !'],500);
            }
            $user_data=['password'=>$req->password];
            $user_update=User::where('id',$user->id)->update($user_data);
            if($user_update==1)
            {
                return response()->json(['error' => false ,'message'=>' Password Reset Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Something is Wrong !'],500);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);

    }
    public function mobUser($mno)
    {
        $user=User::join('user_type','user_type.id','users.type_id')
        ->join('citys','users.city_id','citys.id')
        ->join('states','states.id','users.state_id')
        ->select('users.id as uid','users.*','user_type.*','citys.city_name','states.state_name')
        ->where('users.mobile',$mno)->first();
        if($user!=null)
        {
            return response()->json(['error' => false ,'data'=>$user],200);
        }
        return response()->json(['error' => true ,'message'=>'User not found'],400);
    }
    public function updatePass(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'uid' => 'required',
            'password'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $usr=User::find($req->uid);
        if($usr)
        {
            $usr->password=$req->password;
            $usr->save();
            return response()->json(['error' => false ,'message'=>'Password updated..!'],200);
        }
        return response()->json(['error' => true ,'message'=>'User not found..!!'],200);
    }
    public function profileDisplay($id)
    {
        $user=User::join('user_type','user_type.id','users.type_id')
        ->join('citys','users.city_id','citys.id')
        ->join('states','states.id','users.state_id')
        ->select('users.id as uid','users.*','user_type.*','citys.city_name','states.state_name')
        ->where('users.id',$id)->first();
        $citys=\DB::table('citys')->get();
        $states=\DB::table('states')->get();
        if($user != null)
        {
                $user=User::join('company_info','company_info.sid','users.id')
                ->join('user_type','user_type.id','users.type_id')
                ->join('states','states.id','users.state_id')
                ->select('users.id as uid','users.*','user_type.*','company_info.*','citys.city_name','states.state_name')
                ->where('users.id',$id)->join('citys','users.city_id','citys.id')->first();
                return response()->json(['error' => false ,'data'=>$user,'cities'=>$citys,'states'=>$states],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
    }

    public function addViewLog(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'seller_id'=>'required',
            'cust_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        $logrecord=ProfileViewLog::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->first();
        if($logrecord != NULL)
        {
            if(date('d-m-Y',strtotime($logrecord['created_at']))==date('d-m-Y'))
            {
                return response()->json(['error' => true ,'message'=>'Log already available for today..!'],200);
            }
            $data=new ProfileViewLog;
            $data->seller_id=$req->seller_id;
            $data->cust_id=$req->cust_id;
            $data->created_at=date('Y-m-d H:i:s');
            if($data->save())
            {
                return response()->json(['error' => false ,'message'=>'View Logged Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
        }
        if($logrecord == null)
        {
            $data=new ProfileViewLog;
            $data->seller_id=$req->seller_id;
            $data->cust_id=$req->cust_id;
            if($data->save())
            {
                return response()->json(['error' => false ,'message'=>'View Logged Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
    }

    public function showViewLog($id)
    {
        $User=User::find($id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
        }

        $logrecords=ProfileViewLog::join('users','users.id','cust_id')->where('seller_id',$id)->get()->toarray();

        if($logrecords == null)
        {
            return response()->json(['error' => false ,'data'=>null],200);
        }

        if($logrecords != null)
        {
            $recordids = ProfileViewLog::select('id')->where('seller_id',$id)->get()->toarray();
            $records = DB::table('profile_view_logs')
                ->join('users','users.id','profile_view_logs.cust_id')
                ->join('citys','users.city_id','citys.id')
                ->join('company_info','sid','users.id')
                ->join('states','users.state_id','states.id')
                ->select('users.name as cust_name','users.id as cust_id','users.mobile','profile_view_logs.updated_at as view_date','citys.city_name','states.state_name','company_info.cname')
                ->whereIn('profile_view_logs.id',$recordids)
                ->orderby('profile_view_logs.updated_at','DESC')
                ->get()->toarray();

            if($records != null)
            {
                $log_data=['isSeen'=>1];
                $log_update=\DB::table('profile_view_logs')->whereIn('id',$recordids)->update($log_data);
                return response()->json(['error' => false ,'data'=>$records],200);

            }
            else{
                return response()->json(['error' => false ,'data'=>null],200);
            }

        }
        else{
            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
        }
    }
    public function update(Request $request,$uid)
    {
        $u=User::where('id',$uid)->first();
        $ids=[1,2,3];
        if(in_array($u->type_id,$ids))
        {
            $validator = Validator::make($request->all(), [
            'cname' => 'required',
            'address' => 'required',
            'name' => 'required',
            'city_id' => 'required',
            'email'=>'required',
            ]);
        }
        else{
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'city_id' => 'required',
                'email'=>'required',
                ]);
        }
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        if($u['email']!=$request->email)
        {
            $validator = Validator::make($request->all(), [
            'email'=>'required|unique:users,email',
            ]);
        }
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $st=\DB::table('citys')->where('id',$request->city_id)->first();
        $user = User::find($uid);
        $emp=emp_sel_rel::where('seller_id',$uid)->select('emp_id')->get()->toarray();
        if($user)
        {
            $user->name=$request->name;
            $user->email = $request->email;
            $user->city_id = $request->city_id;
            $user->state_id=$st->state_id;
            $user->business_scope=$request->business_scope;
            if($user->save())
            {
                if(in_array($u->type_id,$ids))
                {
                    $ci=com_info::where('sid',$uid)->update([
                        'cname'=>$request->cname,
                        'pan'=>$request->pan,
                        'gst'=>$request->gst,
                        'address'=>$request->address
                    ]);
                    $ci=com_info::whereIn('sid',$emp)->update([
                        'cname'=>$request->cname,
                        'pan'=>$request->pan,
                        'gst'=>$request->gst,
                        'address'=>$request->address
                    ]);
                }
                return response()->json(['error' => false ,'message'=>'User updated Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
        }
        return response()->json(['error' => true ,'message'=>'User not found '],500);

    }
    public function regInfo1($uid)
    {
        $data=array();
        $data['cities']=\DB::table('citys')->get();
        if($uid==1)
        {
            $data['userTypes']=\DB::table('user_type')->whereNotIn('id',[1,2,3,7,8])->get();
        }
        if($uid==2)
        {
            $data['userTypes']=\DB::table('user_type')->where('id',8)->get();
        }
        if (sizeof($data['cities']) > 0) {
            return response()->json(['error' => false, 'data' => $data], 200);
        } else {
            return response()->json(['error' => true, 'message' => $data], 500);
        }
    }
    public function regInfo()
    {
        $data=array();
        $data['cities']=\DB::table('citys')->get();
        $data['userTypes']=\DB::table('user_type')->whereNotIn('id',[1,2,3,7])->get();
        if (sizeof($data['cities']) > 0) {
            return response()->json(['error' => false, 'data' => $data], 200);
        } else {
            return response()->json(['error' => true, 'message' => $data], 500);
        }
    }
    public function agentSearch(Request $req)
    {
        $srch=$req->search;
        $agents=User::where([['name','like','%'.$srch.'%'],['type_id','2']])->get();
        if(count($agents)>0)
        {
            return response()->json(['error' => true, 'data' => $agents], 500);
        }
        return response()->json(['error' => true, 'message' => 'Agents not found'], 500);
    }
    public function agentCatSearch(Request $req,$cat)
    {
        $srch=$req->search;
        $agents=User::where([['name','like','%'.$srch.'%'],['type_id','2']])->get();
        if(count($agents)>0)
        {
            return response()->json(['error' => true, 'data' => $agents], 200);
        }
        return response()->json(['error' => true, 'message' => 'Agents not found'], 500);
    }
    public function suplierSearch(Request $req)
    {
        $srch=$req->search;
        $agents=User::where([['name','like','%'.$srch.'%'],['type_id','1']])->get();
        if(count($agents)>0)
        {
            return response()->json(['error' => false, 'data' => $agents], 200);
        }
        return response()->json(['error' => true, 'message' => 'Supliers not found'], 500);
    }
    public function updatePic(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
        'image'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $usr=User::find($id);
        if($usr)
        {
            $image_path = public_path().'/userProfile/'.$usr->profile_picture;
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            $file = base64_decode($request->image);
            $fname=time()."."."png";
            Storage::disk('profile')->put($fname, $file);
            if(Storage::disk('profile')->exists($fname))
            {
                $usr->profile_picture=$fname;
                $usr->save();
                return response()->json(['error' => false, 'data' => 'Profile photo updated successfully'], 200);
            }

        }
        return response()->json(['error' => true ,'message'=>'User not found'], 200);
    }
    public function firmSearch(Request $req)
    {
        if(isset($req->buyer))
        {
            $srch=$req->buyer;
            $usr=com_info::join('users','company_info.sid','users.id')->where([['cname','like','%'.$srch.'%'],['type_id','3']])->get();
            if(count($usr)>0)
            {
                return response()->json(['error' => true, 'data' => $usr], 200);
            }
            return response()->json(['error' => true, 'message' => 'Buyer not found'], 500);
        }
        if(isset($req->seller))
        {
            $srch=$req->seller;
            $usr=com_info::join('users','company_info.sid','users.id')->where([['cname','like','%'.$srch.'%'],['type_id','1']])->get();
            if(count($usr)>0)
            {
                return response()->json(['error' => true, 'data' => $usr], 200);
            }
            return response()->json(['error' => true, 'message' => 'seller not found'], 500);
        }
        if(isset($req->agent))
        {
            $srch=$req->agent;
            $usr=com_info::join('users','company_info.sid','users.id')->where([['cname','like','%'.$srch.'%'],['type_id','2']])->get();
            if(count($usr)>0)
            {
                return response()->json(['error' => true, 'data' => $usr], 200);
            }
            return response()->json(['error' => true, 'message' => 'Agent not found'], 500);
        }
    }
    public function count($id)
    {
        $user=User::find($id);
        if($user->type_id==4)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $user=User::find($id);
            $order=Order::where([['seller_id',$id],['status_id',1]])->count();
            $tempReq=temp_req::where([['isResponded',0],['req_to',$id]])->count();
            $ak=AgentKnock::where([['isApproved',0],['seller_id',$id]])->count();
            $ck=CustomerKnock::where([['seller_id',$id],['isApproved',0]])->count();
            $knock=$ak+$ck;
            return response()->json(['error' => false, 'order' => $order,'tempReq' => $tempReq,'knock'=>$knock,'chat'=>0], 200);
        }
        if($user->type_id==5 || $user->type_id==6)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $user=User::find($id);
            $order=Order::where([['seller_id',$id],['status_id',1]])->count();
            return response()->json(['error' => false, 'order' => $order,'tempReq' => 0,'knock'=>0,'chat'=>0], 200);
        }
        if($user)
        {
            if($user->type_id==1)
            {
                $order=Order::where([['seller_id',$id],['status_id',1]])->count();
                $tempReq=temp_req::where([['isResponded',0],['req_to',$id]])->count();
                $ak=AgentKnock::where([['isApproved',0],['seller_id',$id]])->count();
                $ck=CustomerKnock::where([['seller_id',$id],['isApproved',0]])->count();
                $knock=$ak+$ck;
                return response()->json(['error' => false, 'order' => $order,'tempReq' => $tempReq,'knock'=>$knock,'chat'=>0], 200);
            }
            if($user->type_id==2)
            {
                $order=Order::where([['agent_reference',$user->ref_code],['status_id',1]])->count();
                $tempReq=temp_req::where([['isResponded',0],['req_by',$id]])->count();
                return response()->json(['error' => false, 'order' => $order,'tempReq' => $tempReq,'knock'=>0,'chat'=>0], 200);
            }
            if($user->type_id==3)
            {
                return response()->json(['error' => false, 'order' => 0,'tempReq' => 0,'knock'=>0,'chat'=>0], 200);
            }
            return response()->json(['error' => true,'order' => 0,'tempReq' => 0,'knock'=>0,'chat'=>0], 500);
        }
        return response()->json(['error' => true,'order' => 0,'tempReq' => 0,'knock'=>0,'chat'=>0], 500);
    }
    public function infoCount($id)
    {
        $user=User::find($id);
        if($user->type_id==4)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $user=User::find($id);
        }
        $banner=Banners::where('manu_id',$id)->first();
        if($user->type_id==1)
        {
            if($banner->img_name)
            {
                $ban=count(explode(',',$banner->img_name));
            }
            else{
                $ban=0;
            }
            $product=Product::where('seller_id',$id)->count();
            $account=emp_sel_rel::where('seller_id',$id)->count();
            $orders=Order::where('seller_id',$id)->count();
        }
        if($user->type_id==2)
        {
            $ban=0;
            $product=0;
            $account=0;
            $orders=Order::where('agent_reference',$user->ref_code)->count();
        }
        if($user->type_id==3)
        {
            $ban=0;
            $product=0;
            $account=0;
            $orders=Order::where('cust_id',$id)->count();
        }
        return response()->json(['error' => false, 'orders' => $orders,'banners' =>$ban,'accounts'=>$account,'products'=>$product], 200);
    }
}

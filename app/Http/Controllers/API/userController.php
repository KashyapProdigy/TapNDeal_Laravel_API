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
        $user=User::where([['mobile',request('mobile')],['password', request('password')]])->count();
        if($user==1){
            $user = User::where('mobile',request('mobile'))->first();
            // $success['token'] =  $user->createToken('MyApp')-> accessToken;
            // $user = Auth::user();
            return response()->json(['error' => false ,'data' => $user], $this-> successStatus);
        }
        else{
            return response()->json(['error'=> true , 'message' => 'unauthorised'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|unique:users,mobile',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        if($request->type_id==2)
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
        $user->ref_code=$ref;
        if($request->sel_ref!="")
        {
            $seller=User::where([['ref_code',$request->sel_ref],['type_id',1]])->select('id','acc_allow')->first();
            if($seller == null)
            {
                return response()->json(['error' => true ,'message'=>'invalid Reference code..!'],400);
            }
            $e_count=emp_sel_rel::where('seller_id',$seller->id)->count();
            if($e_count>=$seller['acc_allow'])
            {
                return response()->json(['error' => true ,'message'=>'This seller already used his all account'],401);
            }
        }
        if($user->save())
        {
            if(isset($seller))
            {
                $es=new emp_sel_rel;
                $es->emp_id=$user->id;
                $es->seller_id=$seller['id'];
                $es->save();
            }
            if($request->type_id==1)
            { 
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
            if($user['type_id']==1)
            {
                $user=User::join('company_info','company_info.sid','users.id')
                ->join('user_type','user_type.id','users.type_id')
                ->join('states','states.id','users.state_id')
                ->select('users.id as uid','users.*','user_type.*','company_info.*','citys.city_name','states.state_name')
                ->where('users.id',$id)->join('citys','users.city_id','citys.id')->first();
                return response()->json(['error' => false ,'data'=>$user,'cities'=>$citys,'states'=>$states],200);    
            }
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
                ->join('states','users.state_id','states.id')
                ->select('users.name as cust_name','users.id as cust_id','users.mobile','profile_view_logs.updated_at as view_date','citys.city_name','states.state_name')
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
        if($u['type_id']==1)
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
        if($user)
        {
        $user->name=$request->name;
        $user->email = $request->email;
        $user->city_id = $request->city_id;
        $user->state_id=$st->state_id;
        if($user->save())
        {   if($u['type_id']==1)
            {
                $ci=com_info::where('sid',$uid)->update([
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
    public function regInfo()
    {
        $data=array();
        $data['cities']=\DB::table('citys')->get();
        $data['userTypes']=\DB::table('user_type')->where('user_type','!=','admin')->get();
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
                return response()->json(['error' => false, 'data' => 'Profile photo updated successfully'], 200);
            }
            
        }
        return response()->json(['error' => true ,'message'=>'User not found'], 200);
    }
}

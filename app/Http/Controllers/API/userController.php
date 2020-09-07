<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\ProfileViewLog;
use Illuminate\Support\Facades\DB;
use Validator;

class userController extends Controller
{

    public $successStatus = 200;
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
        $validator = $request->validate([
           'name' => 'required',
           'mobile' => 'required|unique:users,mobile',
           'password' => 'required',
           'type_id'=>'required',
           'city_id' => 'required',
           'state_id' => 'required',
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password = $request->password;
        $user->type_id = $request->type_id;
        $user->city_id = $request->city_id;
        $user->state_id = $request->state_id;
        $user->isVerified = 1;

        if($user->save())
        {
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

    public function profileDisplay($id)
    {
        $user=User::find($id);

        if($user != null)
        {
            return response()->json(['error' => false ,'data'=>$user],200);
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

        if($logrecord != null)
        {
            if($logrecord->isSeen == 0)
            {
                return response()->json(['error' => false ,'message'=>'This View is already logged !'],200);
            }
            $log_data=['isSeen'=>0];
            $log_update=ProfileViewLog::where('seller_id',$req->seller_id)->where('cust_id',$req->cust_id)->update($log_data);
            if($log_update==1)
            {
                return response()->json(['error' => false ,'message'=>'View Logged Successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'Something went wrong !'],500);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
    }

    public function showViewLog($id)
    {

        $logrecords=ProfileViewLog::where('seller_id',$id)->get()->toarray();

        if($logrecords == null)
        {
            return response()->json(['error' => false ,'data'=>null],200);
        }

        if($logrecords != null)
        {

            $recordids = ProfileViewLog::select('id')->where('seller_id',$id)->where('isSeen',0)->get()->toarray();
            $records = DB::table('profile_view_logs')
                ->join('users','users.id','profile_view_logs.cust_id')
                ->select('users.name as cust_name','profile_view_logs.updated_at as view_date')
                ->whereIn('profile_view_logs.id',$recordids)
                ->get()->toarray();

            if($records != null)
            {
                $log_data=['isSeen'=>1];
                $log_update=ProfileViewLog::whereIn('id',$recordids)->update($log_data);
                if($log_update != null)
                {
                    return response()->json(['error' => false ,'data'=>$records],200);
                }
            }
            else{
                return response()->json(['error' => false ,'data'=>null],200);
            }

        }
        else{
            return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
        }
    }
}

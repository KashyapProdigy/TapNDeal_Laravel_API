<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
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
           'city_id' => 'required',
           'state_id' => 'required',
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password = $request->password;
        $user->type_id = 1;
        $user->city_id = $request->city_id;
        $user->state_id = $request->state_id;
        $user->isVerified = 1;

        if($user->save())
        {
            return response()->json(['error' => false ,'message'=>'User Added Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
    }
}

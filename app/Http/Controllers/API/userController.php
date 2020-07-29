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
        $user=User::where([['email',request('email')],['password', request('password')]])->count();
        if($user==1){
            $user = User::where('email',request('email'))->get()->toarray();
            // $success['token'] =  $user->createToken('MyApp')-> accessToken;
            // $user = Auth::user();
            return response()->json(['success' => $user], $this-> successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
}

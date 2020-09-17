<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class agentController extends Controller
{
    public function show()
    {
        $agents=User::join('user_type','user_type.id','users.type_id')
        ->join('citys','citys.id','users.city_id')
        ->join('states','states.id','users.state_id')
        ->select('users.*','citys.*','states.*','users.id as uid','users.state_id as sid')
        ->where('user_type','agent')
        ->get();
        $city=\DB::table('citys')->get();
        $state=\DB::table('states')->get();
        return view('Admin.agents',['owners'=>$agents,'citys'=>$city,'states'=>$state]);
    }
    public function create(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email|unique:users,email',
            'mobile'=>'required|digits:10|unique:users,mobile',
            'city'=>'required',
        ]);   
        $state=\DB::table('citys')->where('id',$req->city)->first();
        $ref=app('App\Http\Controllers\manufactureController')->generateRefCode($req->name);
        $usr=new User;
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->password=$req->pass;
        $usr->state_id=$state->state_id;
        $usr->ref_code=$ref;
        $usr->type_id="2";
        $usr->isVerified="1";
        if($usr->save())
        {
            return redirect()->back()->with('success','Agent Added succesfully...');
        }
        return redirect()->back()->with('error','Somthing wents wrong...');
    }
}

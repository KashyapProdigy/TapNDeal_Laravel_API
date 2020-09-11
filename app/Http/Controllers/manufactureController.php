<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\user;   
use App\com_info;
class manufactureController extends Controller
{
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
    public function login(Request $req)
    {
        dd($req->mob);
    }
    public function register(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required',
            'address'=>'required',
            'email'=>'required|email|unique:users,email',
            'mobile'=>'required|digits:10|unique:users,mobile',
            'city'=>'required',
            'pincode'=>'required',
            'gst_no'=>'required',
            
        ]);   
        
        $state=\DB::table('citys')->where('id',$req->city)->first();
        
        session()->put('name',$req->name);
        session()->put('email',$req->email);
        session()->put('mobile',$req->mobile);
        session()->put('city',$req->city);
        session()->put('pincode',$req->pincode);
        session()->put('gst',$req->gst_no);
        session()->put('address',$req->address);
        session()->put('state',$state->state_id);
        
        return redirect('confirmMob');
        
    }
    public function dashboard()
    {
        $ref=$this->generateRefCode(session()->get('name'));
        $usr=new User;
        $usr->name=session()->get('name');
        $usr->email=session()->get('email');
        $usr->mobile=session()->get('mobile');
        $usr->city_id=session()->get('city');
        
        $usr->state_id=session()->get('state');
        $usr->type_id="1";
        $usr->isVerified="1";
        $usr->ref_code=$ref;
        if($usr->save())
        {
            $c=new com_info;
            $c->sid=$usr->id;
            $c->gst=session()->get('gst');
            $c->address=session()->get('address');
            $c->pincode=session()->get('pincode');
            $c->save();
            session()->put('manufacture',session()->get('name'));
            return redirect('/manufacture/index');
        }
        
    }
    public function index()
    {
        return view('manufacture.index');
    }
    public function mobCheck(Request $req)
    {
        $u=User::where('mobile',$req->mo)->join('user_type','type_id','user_type.id')->where('user_type.user_type','seller')->count();
        return response()->json(['co'=>$u]);
    }
}

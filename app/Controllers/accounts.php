<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\emp_sel_rel;

class accounts extends Controller
{
    public function sellerAccounts()
    {
        $ac=User::join('emp_sel_rel','emp_id','users.id')
        ->join('user_type','user_type.id','users.type_id')
        ->join('citys','citys.id','users.city_id')
        ->join('states','states.id','users.state_id')
        ->select('users.*','user_type.user_type','citys.*','states.*','users.id as uid','users.state_id as sid')
        ->where('seller_id',session()->get('uid'))
        ->get();
    $seller=User::where('id',session()->get('uid'))->first();
    $city=\DB::table('citys')->get();
    $state=\DB::table('states')->get();
    $e_type=\DB::table('user_type')->whereIn('user_type',['Accountant','Salesman','Packaging'])->get();
    return view('manufacture.accounts',['owners'=>$ac,'citys'=>$city,'states'=>$state,'seller'=>$seller,'e_type'=>$e_type]);
    }
    public function empAdd(Request $req)
    {

        $acc=emp_sel_rel::where('seller_id',session()->get('uid'))->count();
        $alw_acc=User::where('id',session()->get('uid'))->select('acc_allow')->first();
        if($acc >= $alw_acc['acc_allow'])
        {
            return redirect()->back()->with('error','You alredy used your all allowed accounts, you need to purchase more account for add account..!!');
        }
  

        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email',
            'mobile'=>'required|digits:10|unique:users,mobile',
            'city'=>'required',
            'type'=>'required',
        ]);   
        $ref=app('App\Http\Controllers\manufactureController')->generateRefCode($req->name);
        $state=\DB::table('citys')->where('id',$req->city)->first();
        $usr=new User;
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->state_id=$state->state_id;
        $usr->type_id=$req->type;
        $usr->isVerified="1";
        $usr->ref_code=$ref;
        $usr->save();

        $emp=new emp_sel_rel;
        $emp->emp_id=$usr->id;
        $emp->seller_id=$req->seller;
        if($emp->save())
            return redirect()->back()->with('success','Account of this seller Added succesfully...');
        
        return redirect()->back()->with('error','Somthing wents wrong...'); 
    }
    public function empEdit(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email',
            'mobile'=>'required|digits:10',
            'city'=>'required',
            'etype'=>'required',
        ],[
            'etype.required'=>"Employee type is Required..!",
        ]);
        $us=User::where('id',$req->uid)->first();
        if($us['mobile']!=$req->mobile)
        {
            $validatedData = $req->validate([
                'mobile'=>'unique:users',
            ]);
        }
        if($us['email']!=$req->email)
        {
            $validatedData = $req->validate([
                'email'=>'unique:users'
            ]);
        }
        $state=\DB::table('citys')->where('id',$req->city)->first();
        $usr=User::find($req->uid);
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->state_id=$state->state_id;
        $usr->city_id=$req->city;
        $usr->type_id=$req->etype;
        $usr->save();
        return redirect()->back()->with('success','Employee updated succesfully...');
    }
    public function empDelete($uid)
    {
        $u=emp_sel_rel::where([['emp_id',$uid],['seller_id',session()->get('uid')]]);
        if($u->delete())
        {
            $user=User::find($uid);
            if($user->delete())
            {
                return redirect()->back()->with('success','User Deleted succesfully...');
            }
        }
        return redirect()->back()->with('error','Somthing wents wrong...');
    }
}

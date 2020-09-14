<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\emp_sel_rel;
use App\com_info;
class ownersController extends Controller
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
    public function show()
    {
        $owner=User::join('user_type','user_type.id','users.type_id')
        ->join('citys','citys.id','users.city_id')
        ->join('states','states.id','users.state_id')
        ->select('users.*','citys.*','states.*','users.id as uid','users.state_id as sid')
        ->where('user_type','Seller')
        ->get();
        $city=\DB::table('citys')->get();
        $state=\DB::table('states')->get();
        return view('Admin.sellers',['owners'=>$owner,'citys'=>$city,'states'=>$state]);
    }
    public function update(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email',
            'mobile'=>'required|digits:10',
            'city'=>'required',
            'state'=>'required'
        ]);
        $us=User::where('id',$req->uid)->first();
        if($us['mobile']!=$req->mobile)
        {
            $validatedData = $req->validate([
                'mobile'=>'unique:users',
                'email'=>'unique:users'
            ]);
        }
        $usr=User::find($req->uid);
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->state_id=$req->state;
        $usr->save();
        return redirect()->back()->with('success','Seller updated succesfully...');
    }
    public function create(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email|unique:u',
            'mobile'=>'required|digits:10',
            'city'=>'required',
            'pass'=>'required',
            'cpass'=>'same:pass',
            'gst'=>'required',
            'Address'=>'required',
            'pincode'=>'required'
        ],[
            'cpass.same'=>'Confirm password and password not match..!!',
            'pass.required'=>'Password filed is required',
        ]);   
        $state=\DB::table('citys')->where('id',$req->city)->first();
        $ref=$this->generateRefCode($req->name);
        $usr=new User;
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->password=$req->pass;
        $usr->state_id=$state->state_id;
        $usr->type_id="1";
        $usr->isVerified="1";
        $usr->ref_code=$ref;
        if($usr->save())
        {
            $c=new com_info;
            $c->sid=$usr->id;
            $c->gst=$req->gst;
            $c->address=$req->Address;
            $c->pincode=$req->pincode;
            $c->save();
            return redirect()->back()->with('success','Seller Added succesfully...');
        }
        return redirect()->back()->with('error','Somthing wents wrong...');
    }
    public function delete($uid)
    {
        $u=User::find($uid);
        if($u->delete())
            return redirect()->back()->with('success','User Deleted succesfully...');
            
        return redirect()->back()->with('error','Somthing wents wrong...');
    }
    public function accounts($sid)
    {
        $ac=User::join('emp_sel_rel','emp_id','users.id')
            ->join('user_type','user_type.id','users.type_id')
            ->join('citys','citys.id','users.city_id')
            ->join('states','states.id','users.state_id')
            ->select('users.*','user_type.user_type','citys.*','states.*','users.id as uid','users.state_id as sid')
            ->where('seller_id',$sid)
            ->get();
        $seller=User::where('id',$sid)->first();
        $city=\DB::table('citys')->get();
        $state=\DB::table('states')->get();
        $e_type=\DB::table('user_type')->whereIn('user_type',['Accountant','Salesman','Packaging'])->get();
        return view('admin.sellerAccounts',['owners'=>$ac,'citys'=>$city,'states'=>$state,'seller'=>$seller,'e_type'=>$e_type]);
    }
    public function AddEmployee(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email',
            'mobile'=>'required|digits:10',
            'city'=>'required',
            'type'=>'required',
            'state'=>'required',
            'pass'=>'required',
            'cpass'=>'same:pass'
        ],[
            'cpass.same'=>'Confirm password and password not match..!!',
            'pass.required'=>'Password filed is required',
        ]);   
        $usr=new User;
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->password=$req->pass;
        $usr->state_id=$req->state;
        $usr->type_id=$req->type;
        $usr->isVerified="1";
        $usr->save();

        $emp=new emp_sel_rel;
        $emp->emp_id=$usr->id;
        $emp->seller_id=$req->seller;
        if($emp->save())
            return redirect()->back()->with('success','Account of this seller Added succesfully...');
        
        return redirect()->back()->with('error','Somthing wents wrong...');   
    }
    public function updateEmployee(Request $req)
    {
        
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email',
            'mobile'=>'required|digits:10',
            'city'=>'required',
            'state'=>'required',
            'etype'=>'required',
        ],[
            'etype.required'=>"Employee type is Required..!",
        ]);
        $us=User::where('id',$req->uid)->first();
        if($us['mobile']!=$req->mobile)
        {
            $validatedData = $req->validate([
                'mobile'=>'unique:users',
                'email'=>'unique:users'
            ]);
        }
        $usr=User::find($req->uid);
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->state_id=$req->state;
        $usr->type_id=$req->etype;
        $usr->save();
        return redirect()->back()->with('success','Employee updated succesfully...');
    }
}

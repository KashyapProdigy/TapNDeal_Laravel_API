<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\emp_sel_rel;
use App\com_info;
use App\Product;
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
        ->join('company_info','company_info.sid','users.id')
        ->join('citys','citys.id','users.city_id')
        ->join('states','states.id','users.state_id')
        ->select('users.*','citys.*','states.*','users.id as uid','users.state_id as sid','company_info.*')
        ->where('user_type','Seller')
        ->where('isDeleted',0)
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
        $usr->city_id=$req->city;
        $usr->state_id=$state->state_id;
        $usr->save();

        $comp=com_info::where('sid',$req->uid)->update(['cname'=>$req->cname,'pan'=>$req->pan,'address'=>$req->address,'gst'=>$req->gst]);
        return redirect()->back()->with('success','Seller updated succesfully...');
    }
    public function create(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email|unique:users,email',
            'mobile'=>'required|digits:10|unique:users,mobile',
            'city'=>'required',
            'address'=>'required',
            'cname'=>'required',
        ],[
            'cname.required'=>"company name required..!",
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
            $c->cname=$req->cname;
            $c->pan=$req->pan;
            $c->address=$req->address;
            $c->pincode=$req->pincode;
            $c->save();
            return redirect()->back()->with('success','Seller Added successfully...');
        }
        return redirect()->back()->with('error','Something went wrong...');
    }
    public function delete($uid)
    {
        $u=User::find($uid);
        $u->isDeleted=1;
        $u->firebase_token="";
        $u->login_token="propertyoflogicalloop";
        $u->msg_token="";
        if($u->save())
            return redirect()->back()->with('success','User Deleted successfully...');

        return redirect()->back()->with('error','Something went wrong...');
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
        return view('Admin.sellerAccounts',['owners'=>$ac,'citys'=>$city,'states'=>$state,'seller'=>$seller,'e_type'=>$e_type]);
    }
    public function AddEmployee(Request $req)
    {
        $validatedData = $req->validate([
            'name' => 'required|',
            'email' => 'required|email',
            'mobile'=>'required|digits:10',
            'city'=>'required',
            'type'=>'required',
        ]);
        $ref=$this->generateRefCode($req->name);
        $state=\DB::table('citys')->where('id',$req->city)->first();
        $usr=new User;
        $usr->name=$req->name;
        $usr->email=$req->email;
        $usr->mobile=$req->mobile;
        $usr->city_id=$req->city;
        $usr->password=$req->pass;
        $usr->state_id=$state->state_id;
        $usr->type_id=$req->type;
        $usr->ref_code=$ref;
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
        $usr->city_id=$req->city;
        $usr->state_id=$state->state_id;
        $usr->type_id=$req->etype;
        $usr->save();
        return redirect()->back()->with('success','Employee updated succesfully...');
    }
    public function Products()
    {
        $product=Product::join('users','users.id','seller_id')->select('products.*','products.id as pid','users.name as sname')
        ->orderBy('products.created_at','desc')->get();
        $sellers=User::join('company_info','company_info.sid','users.id')->select('users.*','cname as name')->where([['type_id',1],['isDeleted',0]])->get();
        return view('Admin.products',['products'=>$product,'sellers'=>$sellers]);
    }
    public function deletepro($pid)
    {
        $del=Product::where('id',$pid);
        if($del!=null)
        {
            $del->delete();
            return back()->with('success','product deleted successfully');
        }
        return back()->with('danger','somthings went\'s wrong');
    }
    public function enable($pid)
    {
        $p=Product::where('id',$pid)->update(['isDisabled'=>0]);
        if($p!=null)
        {
            return back()->with('success','product Enabled successfully');
        }
        return back()->with('danger','somthings went\'s wrong');
    }
    public function disable($pid)
    {
        $p=Product::where('id',$pid)->update(['isDisabled'=>1]);
        if($p!=null)
        {
            return back()->with('success','product Disabled successfully');
        }
        return back()->with('danger','somthings went\'s wrong');
    }
}

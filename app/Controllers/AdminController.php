<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Order;
use App\Product;
use App\Payment;
class AdminController extends Controller
{
    public function admin()
    {
        if(session::has('admin'))
        {
            $tot_sel=User::where('type_id',1)->count();
            $tot_cust=User::where('type_id',3)->count();
            $tot_agt=User::where('type_id',2)->count();
            $tot_pro=Product::count();
            $to=Order::count();
            $selling=Order::join('order_status','order_status.id','orders.status_id')->where('status_name','Dispatched')->sum('total_price');
            return view('Admin.index',['ts'=>$tot_sel,'tc'=>$tot_cust,'ta'=>$tot_agt,'tp'=>$tot_pro,'to'=>$to,'sel'=>$selling]);
        }
        return view('Admin.login');   
    }
    public function Login(Request $req)
    {
        $admin=\DB::table('users')->join('user_type','users.type_id','user_type.id')->where([['name',$req->name],['password',$req->pass],['user_type','admin']])->first();
        if($admin)
        {
            session()->put('admin','Admin');
            session()->put('amob',$admin->mobile);
            $tot_sel=User::where('type_id',1)->count();
            $tot_cust=User::where('type_id',3)->count();
            $tot_agt=User::where('type_id',2)->count();
            $tot_pro=Product::count();
            $to=Order::count();
            $selling=Order::where('isDelivered',1)->sum('total_price');
            return view('Admin.index',['ts'=>$tot_sel,'tc'=>$tot_cust,'ta'=>$tot_agt,'tp'=>$tot_pro,'to'=>$to,'sel'=>$selling]);
        }
        else{
            return redirect()->back()->with('error','Invalid Mobile number or Password..');
        }
    }
    public function logout()
    {
        Session::flush();
        return redirect('/admin');
    }
    public function users()
    {
        $owner=User::join('user_type','user_type.id','users.type_id')
        ->join('company_info','company_info.sid','users.id')
        ->join('citys','citys.id','users.city_id')
        ->join('states','states.id','users.state_id')
        ->select('users.*','citys.*','states.*','users.id as uid','users.state_id as sid','company_info.*','user_type.user_type')
        ->whereIn('type_id',[1,2,3])
        ->where('isDeleted',0)
        ->get();
        $city=\DB::table('citys')->get();
        $state=\DB::table('states')->get();
        $type=\DB::table('user_type')->whereIn('id',[1,2,3])->get();
        return view('Admin.allusers',['owners'=>$owner,'citys'=>$city,'states'=>$state,'type'=>$type]);
    }
    public function changeType(Request $req)
    {
        $user=User::where('id',$req->uid)->update(['type_id'=>$req->utype]);
        return response()->json(['status'=>$req->uid]);
    }
    public function reference()
    {
        $user=User::where('type_id','!=','7')->get();
        $refby=array();
        foreach($user as $ref)
        {
            $c=User::where('refered_by',$ref['ref_code'])->count();
            $ref->refered=$c;
        }
        return view('Admin.reference',['user'=>$user]);
    }
    public function refUser($ref)
    {
        $user=User::where('refered_by',decrypt($ref))->join('user_type','user_type.id','users.type_id')->get();
        return view('Admin.refUser',['user'=>$user]);
    }
    public function payments()
    {
        $payment=Payment::join('users','users.id','payments.user_id')->join('user_type','user_type.id','users.type_id')->get();
        return view('Admin.payments',['pay'=>$payment]);
    }
}

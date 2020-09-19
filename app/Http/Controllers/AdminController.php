<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Order;
use App\Product;

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
            return view('Admin.index',['ts'=>$tot_sel,'tc'=>$tot_cust,'ta'=>$tot_agt,'tp'=>$tot_pro,'to'=>$to]);
        }
        return view('Admin.login');   
    }
    public function Login(Request $req)
    {
        $admin=\DB::table('users')->join('user_type','users.type_id','user_type.id')->where([['mobile',$req->mob],['password',$req->pass],['user_type','admin']])->first();
        if($admin)
        {
            session()->put('admin','Admin');
            session()->put('amob',$admin->mobile);
            $tot_sel=User::where('type_id',1)->count();
            $tot_cust=User::where('type_id',3)->count();
            $tot_agt=User::where('type_id',2)->count();
            $tot_pro=Product::count();
            $to=Order::count();
            return view('Admin.index',['ts'=>$tot_sel,'tc'=>$tot_cust,'ta'=>$tot_agt,'tp'=>$tot_pro,'to'=>$to]);
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
}

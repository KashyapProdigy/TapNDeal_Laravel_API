<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function admin()
    {
        if(session::has('admin'))
        {
            return view('Admin.index');
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
            return view('Admin.index');
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

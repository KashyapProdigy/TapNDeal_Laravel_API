<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
class importExcel extends Controller
{
    public function import(Request $req) 
    {
        $req->validate([
            'file' => 'required'
        ]);
        if(session::has('admin'))
        {
            $req->validate([
                'sid'=>'required'
            ],[
                'sid.required'=>'Please select seller'
            ]);
            session()->put('uid',$req->sid);
        }
        \Excel::import(new Productimport,request()->file('file'));
        if(session::has('admin'))
        {
            Session::forget('uid');
        }
        return back();
    }
}

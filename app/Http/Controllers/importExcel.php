<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\Productimport;
use Maatwebsite\Excel\Facades\Excel;

class importExcel extends Controller
{
    public function import(Request $req) 
    {
        $req->validate([
            'file' => 'required'
        ]);
        \Excel::import(new Productimport,request()->file('file'));
           
        return back();
    }
    
}

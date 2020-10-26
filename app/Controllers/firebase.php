<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class firebase extends Controller
{
    public function __construct()
   {
       // $this->middleware('auth');
   }

   public function invcaptcha()
   {
       return view('invcaptcha');
   }
}

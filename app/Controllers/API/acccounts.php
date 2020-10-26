<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class acccounts extends Controller
{
    public function new(Request $req)
    {
        $seller=User::find($sid);
        if($seller)
        {
            $today=date('Y-m-d');
            $end=$seller->end_date;
            
            $ts1 = strtotime($today);
            $ts2 = strtotime($end);

            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
            return response()->json(['error' => true ,'message'=>$diff], 200);
        }
    }
}

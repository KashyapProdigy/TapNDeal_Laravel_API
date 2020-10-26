<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
class accounts extends Controller
{
    public function new($sid)
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
            $gst=$diff * 100 * (0.18);
            $amount=$diff * 100 + $gst;
            return response()->json(['error' => false ,'months'=>$diff,'end_date'=>$end,'amount'=>$amount], 200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid seller id..'], 400);
    }
    public function add($sid)
    {
        $user=User::find($sid);
        if($user)
        {
            $user->acc_allow=$user->acc_allow + 1;
            $user->save();
            return response()->json(['error' => true ,'message'=>'Account added..'], 200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid seller id..'], 400);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Knock;
use App\User;
use Carbon\Carbon;
use Validator;

class dashboardController extends Controller
{
    public function getdashboard(Request $req, $id)
    {
        $dashboard=[];
        $loggedInUser=User::find($id);
        if($loggedInUser['usertype'] == 1)
        {
            //User is Seller
            //->orwhere('category','=',"A+")->orwhere('category','=',"A")
            $knockedOnes=Knock::select('seller_id')->where('cust_id',$id)->get()->toarray();
            $dashboard['Knocked_Contacts']=User::whereIn('id',$knockedOnes)->get()->toarray();
            $dashboard['Regular_Contacts']=User::whereNotIN('id',$knockedOnes)->get()->toarray();
        }
        if($loggedInUser['usertype'] == 3)
        {
            //User is Customer
            $knockedOnes=Knock::select('seller_id')->where('cust_id',$id)->where('category','=',"A+")->orwhere('category','=',"A")->get()->toarray();
            $dashboard['Knocked_Contacts']=User::whereIn('id',$knockedOnes)->get();
            $dashboard['Regular_Contacts']=User::whereNotIN('id',$knockedOnes)->get()->toarray();
        }

        return response()->json(['details'=>$dashboard]);
    }
}

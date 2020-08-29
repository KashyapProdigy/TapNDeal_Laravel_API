<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Knock;
use App\User;
use App\Product;
use App\AgentCategoryRelationship;
use App\CustomerCategoryRelationship;
use App\CustomerAgentRelationship;
use Validator;

class dashboardController extends Controller
{
    public function getdashboard(Request $req, $id)
    {
        $dashboard=[];
        $seller=[];
        $customer=[];

        $loggedInUser=User::find($id);
        if($loggedInUser == null)
        {
            return response()->json(['error' => true ,'message'=>"No User Found"]);
        }

        if($loggedInUser->type_id == 1)
        {
            //User is Seller
            $dashboard=Product::where('seller_id',$id)->get()->toarray();
            if(!empty($dashboard))
            {
                return response()->json(['error' => false ,'data'=>$dashboard],200);
            }
            else {
                return response()->json(['error' => true ,'message'=>"Products Not Available"]);
            }
        }

        if($loggedInUser->type_id == 2)
        {
            //User is Agent
            $A_Plus_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
            $A_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
            $B_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
            $Blocked_seller=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('isBlocked',1)->get()->toarray();
            $seller['A_Plus_Sellers']=User::whereIn('id',$A_Plus_list)->get()->toarray();
            $seller['A_Sellers']=User::whereIn('id',$A_list)->get()->toarray();
            $seller['B_Sellers']=User::whereIn('id',$B_list)->get()->toarray();
            $seller['NotConnected_Sellers']=User::where('type_id',1)->where('isVerified',1)->whereNotIN('id',$Blocked_seller)->whereNotIN('id',$A_Plus_list)->whereNotIN('id',$A_list)->whereNotIN('id',$B_list)->get()->toarray();

            $customer_list=CustomerAgentRelationship::select('cust_id')->where('agent_id',$id)->where('isBlocked',0)->get()->toarray();
            $customer=User::whereIn('id',$customer_list)->where('isVerified',1)->get()->toarray();

            $dashboard['Sellers']=$seller;
            $dashboard['Customers']=$customer;

            if(!empty($dashboard))
            {
                return response()->json(['error' => false ,'data'=>$dashboard],200);
            }
            else {
                return response()->json(['error' => true ,'message'=>"Records Not Found"]);
            }
        }

        if($loggedInUser->type_id == 3)
        {
            //User is Customer
            $A_Plus_list=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
            $A_list=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
            $B_list=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
            $Blocked_seller=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('isBlocked',1)->get()->toarray();
            $Blocked_agent=CustomerAgentRelationship::select('agent_id')->where('cust_id',$id)->where('isBlocked',1)->get()->toarray();
            $dashboard['A_Plus_Sellers']=User::whereIn('id',$A_Plus_list)->get()->toarray();
            $dashboard['A_Sellers']=User::whereIn('id',$A_list)->get()->toarray();
            $dashboard['B_Sellers']=User::whereIn('id',$B_list)->get()->toarray();
            $dashboard['NotConnected_Sellers']=User::where('type_id',1)->where('isVerified',1)->whereNotIN('id',$Blocked_seller)->whereNotIN('id',$A_Plus_list)->whereNotIN('id',$A_list)->whereNotIN('id',$B_list)->get()->toarray();
            $dashboard['Agents']=User::where('type_id',2)->where('isVerified',1)->whereNotIN('id',$Blocked_agent)->get()->toarray();

            if(!empty($dashboard))
            {
                return response()->json(['error' => false ,'data'=>$dashboard],200);
            }
            else {
                return response()->json(['error' => true ,'message'=>"Records Not Found"]);
            }
        }

        return response()->json(['error' => true ,'message'=>"Something went wrong"],500);
    }
}

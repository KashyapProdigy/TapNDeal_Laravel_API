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
        $agent=[];

        $loggedInUser=User::find($id);
        if($loggedInUser == null)
        {
            return response()->json(['error' => true ,'message'=>"No User Found"]);
        }

        if($loggedInUser->type_id == 1)
        {
            //User is Seller
            $A_Plus_list=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
            $A_list=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
            $B_Plus_list=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
            $B_list=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
            $Blocked_Agent=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('isBlocked',1)->get()->toarray();
            $agent['A_Plus_agent']=User::whereIn('users.id',$A_Plus_list)->get()->toarray();
            $agent['A_agent']=User::whereIn('users.id',$A_list)->get()->toarray();
            $agent['B_Plus_agent']=User::whereIn('users.id',$B_Plus_list)->get()->toarray();
            $agent['B_agent']=User::whereIn('users.id',$B_list)->get()->toarray();
            $agent['NotConnected_agent']=User::where('type_id',2)->where('isVerified',1)->whereNotIN('users.id',$Blocked_Agent)->whereNotIN('users.id',$A_Plus_list)->whereNotIN('users.id',$A_list)->whereNotIN('users.id',$B_Plus_list)->whereNotIN('users.id',$B_list)->get()->toarray();


                $dashboard=Product::where('seller_id',$id)->get()->toarray();
                // $dashboard['connected_buyer']=\DB::table('cust_sel_category_rel')->join('users','users.id','cust_id')->select('users.*','cust_sel_category_rel.category')->where([['seller_id',$id],['isBlocked',0]])->get();
                // $dashboard['agents']=$agent;
                $banner=\DB::table('banners')->where('manu_id',$id)->get()->toarray();
                if(!empty($dashboard))
                {
                    return response()->json(['error' => false ,'data'=>$dashboard,'banner'=>$banner],200);
                }
                else {
                    return response()->json(['error' => true ,'message'=>"Records Not Found"]);
                }
        }

        if($loggedInUser->type_id == 2)
        {
            //User is Agent
            $A_Plus_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
            $A_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
            $B_Plus_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
            $B_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
            $Blocked_seller=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('isBlocked',1)->get()->toarray();
            $seller['A_Plus_Sellers']=User::whereIn('users.id',$A_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $seller['A_Sellers']=User::whereIn('users.id',$A_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $seller['B_Plus_Sellers']=User::whereIn('users.id',$B_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $seller['B_Sellers']=User::whereIn('users.id',$B_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $seller['NotConnected_Sellers']=User::where('type_id',1)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->where('isVerified',1)->whereNotIN('users.id',$Blocked_seller)->whereNotIN('users.id',$A_Plus_list)->whereNotIN('users.id',$A_list)->whereNotIN('users.id',$B_Plus_list)->whereNotIN('users.id',$B_list)->get()->toarray();

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
            $B_Plus_list=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
            $B_list=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
            $Blocked_seller=CustomerCategoryRelationship::select('seller_id')->where('cust_id',$id)->where('isBlocked',1)->get()->toarray();
            $Blocked_Agent=CustomerAgentRelationship::select('agent_id')->where('cust_id',$id)->where('isBlocked',1)->get()->toarray();
            $con_ag=CustomerAgentRelationship::select('agent_id')->where('cust_id',$id)->get()->toarray();
            $dashboard['A_Plus_Sellers']=User::whereIn('users.id',$A_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $dashboard['A_Sellers']=User::whereIn('users.id',$A_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $dashboard['B_Plus_Sellers']=User::whereIn('users.id',$B_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $dashboard['B_Sellers']=User::whereIn('users.id',$B_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname')->get()->toarray();
            $dashboard['NotConnected_Sellers']=User::where('type_id',1)->select('users.*','company_info.cname')->where('isVerified',1)->whereNotIN('users.id',$Blocked_seller)->whereNotIN('users.id',$A_Plus_list)->whereNotIN('users.id',$A_list)->whereNotIN('users.id',$B_Plus_list)->whereNotIN('users.id',$B_list)->join('company_info','company_info.sid','users.id')->get()->toarray();
            // $dashboard['Agents']=User::where('type_id',2)->where('isVerified',1)->whereNotIN('users.id',$Blocked_Agent)->get()->toarray();
            $dashboard['connected_agents']=CustomerAgentRelationship::join('users','users.id','cust_agent_rel.agent_id')
            ->where([['cust_id',$id],['isBlocked',0]])->get();
            $dashboard['Not_connected_agents']=User::where('type_id',2)->where('isVerified',1)->whereNotIn('users.id',$con_ag)->get();
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

    public function showSellerRelations($id)
    {
        $dashboard=[];
        $customer=[];
        $agent=[];
            $loggedInUser=User::find($id);
            if($loggedInUser == null)
            {
              return response()->json(['error' => true ,'message'=>"No User Found"]);
            }
                $A_Plus_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
                $A_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
                $B_Plus_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
                $B_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
                $Blocked_Customer=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('isBlocked',1)->get()->toarray();
                $customer['A_Plus_Customers']=User::whereIn('id',$A_Plus_Cust_List)->get()->toarray();
                $customer['A_Customers']=User::whereIn('id',$A_Cust_List)->get()->toarray();
                $customer['B_Plus_Customers']=User::whereIn('id',$B_Plus_Cust_List)->get()->toarray();
                $customer['B_Customers']=User::whereIn('id',$B_Cust_List)->get()->toarray();
                $customer['NotConnected_Customers']=User::where('type_id',1)->where('isVerified',1)->whereNotIN('id',$Blocked_Customer)->whereNotIN('id',$A_Plus_Cust_List)->whereNotIN('id',$A_Cust_List)->whereNotIN('id',$B_Plus_Cust_List)->whereNotIN('id',$B_Cust_List)->get()->toarray();

                $A_Plus_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
                $A_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
                $B_Plus_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
                $B_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
                $Blocked_Agent=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('isBlocked',1)->get()->toarray();
                $agent['A_Plus_Agents']=User::whereIn('id',$A_Plus_Agent_List)->get()->toarray();
                $agent['A_Agents']=User::whereIn('id',$A_Agent_List)->get()->toarray();
                $agent['B_Plus_Agents']=User::whereIn('id',$B_Plus_Agent_List)->get()->toarray();
                $agent['B_Agents']=User::whereIn('id',$B_Agent_List)->get()->toarray();
                //$agent['NotConnected_Agents']=User::where('type_id',2)->where('isVerified',1)->whereNotIN('id',$Blocked_Agent)->get()->toarray();

                $dashboard['Customers']=$customer;
                $dashboard['Agents']=$agent;

                if(!empty($dashboard))
                {
                    return response()->json(['error' => false ,'data'=>$dashboard],200);
                }
                else {
                    return response()->json(['error' => true ,'message'=>"Records Not Found"]);
                }
                return response()->json(['error' => true ,'message'=>"Something Went Wrong"],500);
    }
}

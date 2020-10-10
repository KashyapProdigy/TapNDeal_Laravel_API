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
use App\folderModel;
use App\emp_sel_rel;

class dashboardController extends Controller
{
    public function getdashboard(Request $req, $id)
    {
        $dashboard=[];
        $seller=[];
        $customer=[];
        $agent=[];
        $loggedInUser=User::find($id);
        if($loggedInUser->type_id==4 || $loggedInUser->type_id==5 || $loggedInUser->type_id==6 || $loggedInUser->type_id == 8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $loggedInUser=User::find($id);
        }
        if($loggedInUser == null)
        {
            return response()->json(['error' => true ,'message'=>"No User Found",'data'=>[],'banner'=>[],'folders'=>[],'isExpired'=>[]]);
        }

        if($loggedInUser->type_id == 1)
        {
            //User is Seller
            $today=date('Y-m-d H:i:s');
            if($today > $loggedInUser->end_date)
            {
                $isExpired=1;
            }
            else{
                $isExpired=0;
            }
                
                $dashboard=Product::where('seller_id',$id)->get()->toarray();
                $banner=\DB::table('banners')->where('manu_id',$id)->get()->toarray();
                $folders=folderModel::select('id','fname')->where('sid',$id)->get();
                if(!empty($dashboard))
                {
                    return response()->json(['error' => false ,'data'=>$dashboard,'banner'=>$banner,'folders'=>$folders,'isExpired'=>$isExpired],200);
                }
                else {
                    return response()->json(['error' => true ,'message'=>"Records Not Found",'data'=>[],'banner'=>[],'folders'=>[],'isExpired'=>[]]);
                }
        }

        if($loggedInUser->type_id == 2)
        {
            //User is Agent
            $today=date('Y-m-d H:i:s');
            if($today > $loggedInUser->end_date)
            {
                $isExpired=1;
            }
            else{
                $isExpired=0;
            }
            $A_Plus_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
            $A_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
            $B_Plus_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
            $B_list=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
            $Blocked_seller=AgentCategoryRelationship::select('seller_id')->where('agent_id',$id)->where('isBlocked',1)->get()->toarray();
            $seller['A_Plus_Sellers']=User::join('citys','citys.id','users.city_id')->whereIn('users.id',$A_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->get()->toarray();
            $seller['A_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $seller['B_Plus_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $seller['B_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $seller['NotConnected_Sellers']=User::join('citys','citys.id','city_id')->where('type_id',1)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->where('isVerified',1)->whereNotIN('users.id',$Blocked_seller)->whereNotIN('users.id',$A_Plus_list)->whereNotIN('users.id',$A_list)->whereNotIN('users.id',$B_Plus_list)->whereNotIN('users.id',$B_list)->get()->toarray();

            $customer_list=CustomerAgentRelationship::select('cust_id')->where('agent_id',$id)->where('isBlocked',0)->get()->toarray();
            
            $customer=User::whereIn('users.id',$customer_list)->join('citys','citys.id','city_id')->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','city_name')->where('isVerified',1)->get()->toarray();

            $dashboard['Sellers']=$seller;
            $dashboard['Customers']=$customer;
            if(!empty($dashboard))
            {
                return response()->json(['error' => false ,'data'=>$dashboard,'isExpired'=>$isExpired],200);
            }
            else {
                return response()->json(['error' => true ,'data'=>[],'isExpired'=>[]]);
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
            $dashboard['A_Plus_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $dashboard['A_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $dashboard['B_Plus_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_Plus_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $dashboard['B_Sellers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_list)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
            $dashboard['NotConnected_Sellers']=User::join('citys','citys.id','city_id')->where('type_id',1)->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->where('isVerified',1)->whereNotIN('users.id',$Blocked_seller)->whereNotIN('users.id',$A_Plus_list)->whereNotIN('users.id',$A_list)->whereNotIN('users.id',$B_Plus_list)->whereNotIN('users.id',$B_list)->join('company_info','company_info.sid','users.id','citys.city_name')->orderBy('name')->get()->toarray();
            $dashboard['Agents']=User::where('type_id',2)->where('isVerified',1)->whereNotIN('users.id',$Blocked_Agent)->orderBy('name')->get()->toarray();
            $dashboard['connected_agents']=CustomerAgentRelationship::join('users','users.id','cust_agent_rel.agent_id')->join('citys','citys.id','city_id')->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')
            ->where([['cust_id',$id],['isBlocked',0]])->orderBy('name')->get();
            $dashboard['Not_connected_agents']=User::join('citys','citys.id','city_id')->where('type_id',2)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->where('isVerified',1)->whereNotIn('users.id',$con_ag)->orderBy('name')->get();
            if(!empty($dashboard))
            {
                return response()->json(['error' => false ,'data'=>$dashboard,'isExpired'=>2],200);
            }
            else {
                return response()->json(['error' => true ,'message'=>"Records Not Found",'data'=>[],'isExpired'=>[]]);
            }
        }

        return response()->json(['error' => true ,'message'=>"Something went wrong",'data'=>[],'isExpired'=>[]],500);
    }

    public function showSellerRelations($id)
    {
        $dashboard=[];
        $customer=[];
        $agent=[];
        $loggedInUser=User::find($id);
        if($loggedInUser->type_id==4 || $loggedInUser->type_id==5 || $loggedInUser->type_id==6 || $loggedInUser->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;
            $loggedInUser=User::find($id);
        }
            if($loggedInUser == null)
            {
              return response()->json(['error' => true ,'message'=>"No User Found"]);
            }
                $A_Plus_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
                $A_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
                $B_Plus_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
                $B_Cust_List=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
                $Blocked_Customer=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$id)->where('isBlocked',1)->get()->toarray();
                $customer['A_Plus_Customers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_Plus_Cust_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $customer['A_Customers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_Cust_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $customer['B_Plus_Customers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_Plus_Cust_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $customer['B_Customers']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_Cust_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name','citys.city_name')->get()->toarray();
                $customer['NotConnected_Customers']=User::join('citys','citys.id','city_id')->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->where('type_id',3)->where('isVerified',1)->whereNotIN('users.id',$Blocked_Customer)->whereNotIN('users.id',$A_Plus_Cust_List)->whereNotIN('users.id',$A_Cust_List)->whereNotIN('users.id',$B_Plus_Cust_List)->whereNotIN('users.id',$B_Cust_List)->get()->toarray();

                $A_Plus_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"A+")->where('isBlocked',0)->get()->toarray();
                $A_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"A")->where('isBlocked',0)->get()->toarray();
                $B_Plus_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"B+")->where('isBlocked',0)->get()->toarray();
                $B_Agent_List=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('category','=',"B")->where('isBlocked',0)->get()->toarray();
                $Blocked_Agent=AgentCategoryRelationship::select('agent_id')->where('seller_id',$id)->where('isBlocked',1)->get()->toarray();
                $agent['A_Plus_Agents']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_Plus_Agent_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $agent['A_Agents']=User::join('citys','citys.id','city_id')->whereIn('users.id',$A_Agent_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $agent['B_Plus_Agents']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_Plus_Agent_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $agent['B_Agents']=User::join('citys','citys.id','city_id')->whereIn('users.id',$B_Agent_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                //$agent['NotConnected_Agents']=User::where('type_id',2)->where('isVerified',1)->whereNotIN('id',$Blocked_Agent)->get()->toarray();
                $agent['NotConnected_agent']=User::join('citys','citys.id','city_id')->where('type_id',2)->where('isVerified',1)
                    ->whereNotIN('users.id',$Blocked_Agent)->whereNotIN('users.id',$A_Plus_Agent_List)
                    ->whereNotIN('users.id',$A_Agent_List)->whereNotIN('users.id',$B_Plus_Agent_List)
                    ->whereNotIN('users.id',$B_Agent_List)->join('company_info','company_info.sid','users.id')->select('users.*','company_info.cname','citys.city_name')->orderBy('name')->get()->toarray();
                $dashboard['Customers']=$customer;
                $dashboard['Agents']=$agent;

                if(!empty($dashboard))
                {
                    return response()->json(['error' => false ,'data'=>$dashboard],200);
                }
                else {
                    return response()->json(['error' => true ,'message'=>"Records Not Found",'data'=>[]]);
                }
                return response()->json(['error' => true ,'message'=>"Something Went Wrong",'data'=>[]],500);
    }
}
        
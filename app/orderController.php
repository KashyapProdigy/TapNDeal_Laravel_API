<?php

namespace App\Http\Controllers\API;

use App\Cart;
use App\custome_agent;
use App\CustomerCategoryRelationship;
use App\emp_sel_rel;
use App\Http\Controllers\Controller;
use App\Notification;
use App\Notifications\onesignal;
use App\Order;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class orderController extends Controller
{

    public function createRequest(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'cust_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()], 401);
        }
        $User = User::find($req->cust_id);
        if ($User->type_id == 4 || $User->type_id == 5 || $User->type_id == 6 || $User->type_id == 8) {
            $seller = emp_sel_rel::where('emp_id', $req->cust_id)->first();
            $req->cust_id = $seller->seller_id;
        }
        $cartrecord = Cart::select('seller_id', 'col_wise_qty')->where('cust_id', $req->cust_id)->first();

        if ($cartrecord == null) {
            return response()->json(['error' => true, 'message' => 'Nothing in Cart'], 500);
        }

        $notificationData = [];

        if ($cartrecord != null) {
            $order_amount = 0;

            $cartProducts = DB::table('carts')
                ->join('products', 'products.id', 'carts.product_id')
                ->select('products.id as product_id', 'products.name as product_name', 'products.image as product_image', 'products.category', 'carts.qty', 'carts.col_wise_qty', 'products.price as product_price')
                ->where('carts.cust_id', $req->cust_id)
                ->get()->toarray();
            $cartID = Cart::select('id')->where('cust_id', $req->cust_id)->get()->toarray();

            foreach ($cartProducts as $record) {
                $record->total_price = $record->product_price * $record->qty;
                $order_amount = $order_amount + $record->total_price;
            }

            if (DB::table('carts')->whereIn('id', $cartID)->delete()) {
                $firm = \DB::table('company_info')->where('sid', $cartrecord->seller_id)->first();
                $words = explode(" ", $firm->cname);
                $fcode = "";

                foreach ($words as $w) {
                    $fcode .= $w[0];
                }
                $i = 1;
                do {
                    $o_name = $fcode . '-' . $i++;
                } while (Order::where('order_name', $o_name)->first());

                $orderinsert = new Order;
                $orderinsert->order_name = $o_name;
                $orderinsert->seller_id = $cartrecord->seller_id;
                $orderinsert->cust_id = $req->cust_id;
                $orderinsert->agent_reference = $req->agent_reference;
                $orderinsert->products = json_encode($cartProducts);
                $orderinsert->total_price = $order_amount;
                $orderinsert->status_id = 1;
                $orderinsert->notes = $req->notes;

                if ($orderinsert->save()) {
                    foreach ($cartProducts as $anotherRecord) {
                        $qty = $anotherRecord->qty;
                        $productId = $anotherRecord->product_id;

                        $originalProd = Product::find($productId);
                        $originalQty = $originalProd->stock;
                        $updatedQty = $originalQty - $qty;
                        Product::where('id', $productId)->update(['stock' => $updatedQty]);
                        if ($updatedQty < 0) {
                            Product::where('id', $productId)->update(['isDisabled' => 1]);
                        }
                    }

                    if ($req->cust_id != $cartrecord->seller_id) {
                        $relrecord = CustomerCategoryRelationship::where('cust_id', $req->cust_id)->where('seller_id', $cartrecord->seller_id)->first();
                        if (!$relrecord) {
                            $relation_data = new CustomerCategoryRelationship;
                            $relation_data->cust_id = $req->cust_id;
                            $relation_data->seller_id = $cartrecord->seller_id;
                            $relation_data->category = 'B';
                            $relation_data->save();
                        }
                    }
                    $usr = User::find($cartrecord->seller_id);
                    $cust = User::join('company_info', 'company_info.sid', 'users.id')->where('sid', $req->cust_id)->first();
                    $data['title'] = 'Tap N Deal';
                    $data['msg'] = "Order has been placed by " . $cust->cname;
                    $notificationData['type'] = "order";
                    $notificationData['id'] = $orderinsert->id;
                    $data['data'] = $notificationData;
                    \Notification::send($usr, new onesignal($data));

                    $n = new Notification;
                    $n->receiver = $usr->id;
                    $n->noti_for = $orderinsert->id;
                    $n->description = $data['msg'];
                    $n->type = "New order";
                    $n->date_time = date('Y-m-d H:i:s');
                    $n->save();

                    $salesman = emp_sel_rel::join('users', 'emp_sel_rel.emp_id', 'users.id')->where([['seller_id', $cartrecord->seller_id], ['type_id', 4]])->get();
                    $data['title'] = 'Tap N Deal';
                    $data['msg'] = "Order has been placed by " . $cust->cname;
                    $notificationData['type'] = "order";
                    $notificationData['id'] = $orderinsert->id;
                    $data['data'] = $notificationData;
                    foreach ($salesman as $s) {
                        $usr = User::find($s->emp_id);
                        \Notification::send($usr, new onesignal($data));

                        $n = new Notification;
                        $n->receiver = $s->id;
                        $n->noti_for = $orderinsert->id;
                        $n->description = $data['msg'];
                        $n->type = "New order";
                        $n->date_time = date('Y-m-d H:i:s');
                        $n->save();
                    }

                    if ($req->agent_reference != "Order without agent" && $req->agent_reference != " ") {
                        $agent = User::where('ref_code', $req->agent_reference)->first();
                        if ($agent) {
                            $sel = User::find($cartrecord->seller_id);
                            $selller = User::join('company_info', 'company_info.sid', 'users.id')->where('sid', $cartrecord->seller_id)->first();
                            $data['title'] = 'Tap N Deal';
                            $data['msg1'] = "Order has been placed by " . $cust->cname . " to " . $selller->cname;
                            $notificationData['type'] = "order";
                            $notificationData['id'] = $orderinsert->id;
                            $data['data'] = $notificationData;
                            \Notification::send($sel, new onesignal($data));

                            $n = new Notification;
                            $n->receiver = $agent->id;
                            $n->noti_for = $orderinsert->id;
                            $n->description = $data['msg'];
                            $n->type = "New order";
                            $n->date_time = date('Y-m-d H:i:s');
                            $n->save();
                        }

                    }
                    return response()->json(['error' => false, 'message' => "Order Requested Successfully"], 200);
                } else {
                    return response()->json(['error' => true, 'message' => 'Something Went Wrong'], 500);
                }

            }
        } else {
            return response()->json(['error' => true, 'message' => 'Something Went Wrong'], 500);
        }
    }

    public function showRequest($id)
    {
        $User = User::find($id);
        if ($User->type_id == 4 || $User->type_id == 5 || $User->type_id == 6 || $User->type_id == 8) {
            $seller = emp_sel_rel::where('emp_id', $id)->first();
            $id = $seller->seller_id;
            $User = User::find($id);

        }
        $listreturn = DB::table('orders')
            ->join('users', 'users.id', 'orders.cust_id')
            ->join('company_info', 'sid', 'users.id')
            ->join('order_status', 'order_status.id', 'orders.status_id')
            ->select('users.name as cust_name', 'users.id as cust_id', 'users.mobile', 'orders.agent_reference', 'orders.id as order_id', 'orders.order_name', 'orders.total_price as order_price', 'order_status.status_name', 'orders.created_at as order_date', 'orders.products', 'orders.notes', 'cname as cust_name')
            ->where('orders.seller_id', $id)
            ->where('order_status.status_name', 'Received')
            ->orderby('orders.created_at', 'desc')
            ->get()->toarray();

        if (!empty($listreturn)) {
            foreach ($listreturn as $record) {
                $count = 0;
                $record->seller_name = $User->name;

                $sellerName = DB::table('company_info')->select('cname')->where('sid', $User->id)->first();
                $record->seller_name = $sellerName->cname;

                $agent = User::where('ref_code', $record->agent_reference)->first();
                if (!$agent) {
                    $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                    if (!$agent) {
                        $agent['name'] = "";
                        $agent['mobile'] = "";
                    }
                    $record->agent_name = $agent['name'];
                    $record->agent_mobile = $agent['mobile'];
                } else{
                    $agentName = DB::table('company_info')->select('cname')->where('sid', $agent->id)->first();
                    $record->agent_name = $agentName->cname;
                    $record->agent_mobile = $agent['mobile'];
                }

                $record->products = json_decode($record->products);
                foreach ($record->products as $temp) {
                    $count++;
                }
                $record->no_of_products = $count;
            }
            return response()->json(['error' => false, 'data' => $listreturn], 200);
        } else {
            return response()->json(['error' => false, 'data' => null], 200);
        }
        return response()->json(['error' => true, 'message' => 'Something went wrong']);
    }

    public function custNewOrder($cid)
    {
        $user = User::find($cid);
        $listreturn = DB::table('orders')
            ->join('users', 'users.id', 'orders.seller_id')
            ->join('order_status', 'order_status.id', 'orders.status_id')
            ->join('company_info', 'sid', 'users.id')
            ->select('cname as seller_name', 'users.id as sel_id', 'users.mobile', 'orders.agent_reference', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname')
            ->where('orders.cust_id', $cid)
            ->where('order_status.status_name', 'Received')
            ->orderby('orders.created_at', 'desc')
            ->get()->toarray();

        if (!empty($listreturn)) {
            foreach ($listreturn as $record) {
                $count = 0;

                $agent = User::where('ref_code', $record->agent_reference)->first();
                if (!$agent) {
                    $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                    if (!$agent) {
                        $agent['name'] = "";
                        $agent['mobile'] = "";
                    }
                    $record->agent_name = $agent['name'];
                    $record->agent_mobile = $agent['mobile'];
                } else{
                    $agentName = DB::table('company_info')->select('cname')->where('sid', $agent->id)->first();
                    $record->agent_name = $agentName->cname;
                    $record->agent_mobile = $agent['mobile'];
                }

                $record->cust_name = $user->name;
                $sellerName = DB::table('company_info')->select('cname')->where('sid', $user->id)->first();
                $record->cust_name = $sellerName->cname;

                $record->products = json_decode($record->products);
                foreach ($record->products as $temp) {
                    $count++;
                }
                $record->no_of_products = $count;
            }

            return response()->json(['error' => false, 'data' => $listreturn], 200);
        } else {
            return response()->json(['error' => false, 'data' => null], 200);
        }
    }

    public function showOrders($id)
    {
        $User = User::find($id);
        if ($User->type_id == 4 || $User->type_id == 5 || $User->type_id == 6 || $User->type_id == 8) {
            $seller = emp_sel_rel::where('emp_id', $id)->first();
            $id = $seller->seller_id;
            $User = User::find($id);

        }

        if ($User == null) {
            return response()->json(['error' => true, 'message' => 'User Not Found']);
        } else {
            if ($User->type_id == 1) {
                $listreturn = DB::table('orders')
                    ->join('users', 'users.id', 'orders.cust_id')
                    ->join('company_info', 'sid', 'users.id')
                    ->join('order_status', 'order_status.id', 'orders.status_id')
                    ->select('users.id as cust_id', 'users.mobile', 'orders.agent_reference', 'orders.id as order_id', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname as cust_name')
                    ->where('orders.seller_id', $id)
                    ->whereIn('order_status.status_name', ['Accepted', 'Ready'])
                    ->orderby('orders.created_at', 'desc')
                    ->get()->toarray();
                if (!empty($listreturn)) {
                    foreach ($listreturn as $record) {
                        $count = 0;
                        $sellerName = DB::table('company_info')->select('cname')->where('sid', $User->id)->first();
                        $record->seller_name = $sellerName->cname;
                        $agent = User::where('ref_code', $record->agent_reference)->first();
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            if (!$agent) {
                                $agent['name'] = "";
                                $agent['mobile'] = "";
                            }
                            $record->agent_name = $agent['name'];
                            $record->agent_mobile = $agent['mobile'];
                        } else{
                            $agentName = DB::table('company_info')->select('cname')->where('sid', $agent->id)->first();
                            $record->agent_name = $agentName->cname;
                            $record->agent_mobile = $agent['mobile'];
                        }

                        $record->products = json_decode($record->products);

                        foreach ($record->products as $temp) {
                            $count++;
                        }
                        $record->no_of_products = $count;
                    }
                    return response()->json(['error' => false, 'data' => $listreturn], 200);
                } else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            }
            if ($User->type_id == 3) {
                $listreturn = DB::table('orders')
                    ->join('users', 'users.id', 'orders.seller_id')
                    ->join('company_info', 'sid', 'users.id')
                    ->join('order_status', 'order_status.id', 'orders.status_id')
                    ->select('company_info.cname as seller_name', 'users.id as sel_id', 'users.mobile', 'orders.agent_reference', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname')
                    ->where('orders.cust_id', $id)
                    ->whereIn('order_status.status_name', ['Accepted', 'Ready'])
                    ->orderby('orders.created_at', 'desc')
                    ->get()->toarray();

                if (!empty($listreturn)) {
                    foreach ($listreturn as $record) {
                        $count = 0;
                        $sellerName = DB::table('company_info')->select('cname')->where('sid', $User->id)->first();
                        $record->cust_name = $sellerName->cname;

                        $agent = User::where('ref_code', $record->agent_reference)->first();
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            if (!$agent) {
                                $agent['name'] = "";
                                $agent['mobile'] = "";
                            }
                            $record->agent_name = $agent['name'];
                            $record->agent_mobile = $agent['mobile'];
                        } else{
                            $agentName = DB::table('company_info')->select('cname')->where('sid', $agent->id)->first();
                            $record->agent_name = $agentName->cname;
                            $record->agent_mobile = $agent['mobile'];
                        }

                        $record->products = json_decode($record->products);
                        foreach ($record->products as $temp) {
                            $count++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false, 'data' => $listreturn], 200);
                } else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            }
        }
        return response()->json(['error' => true, 'message' => 'Something went wrong']);
    }

    public function showPastOrders($id)
    {
        $User = User::find($id);
        if ($User->type_id == 4 || $User->type_id == 5 || $User->type_id == 6 || $User->type_id == 8) {
            $seller = emp_sel_rel::where('emp_id', $id)->first();
            $id = $seller->seller_id;
            $User = User::find($id);

        }

        if ($User == null) {
            return response()->json(['error' => true, 'message' => 'User Not Found']);
        } else {
            if ($User->type_id == 1) {
                $listreturn = DB::table('orders')
                    ->join('users', 'users.id', 'orders.cust_id')
                    ->join('order_status', 'order_status.id', 'orders.status_id')
                    ->join('company_info', 'sid', 'users.id')
                    ->select('users.name as cust_name', 'users.id as cust_id', 'users.mobile', 'orders.agent_reference', 'orders.id as order_id', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname as cust_name')
                    ->where('orders.seller_id', $id)
                    ->whereIn('order_status.status_name', ['Dispatched', 'Rejected'])
                    ->orderby('orders.created_at', 'desc')
                    ->get()->toarray();
                if (!empty($listreturn)) {
                    foreach ($listreturn as $record) {
                        $count = 0;
                        $record->seller_name = $User->name;
                        $sellerName = DB::table('company_info')->select('cname')->where('sid', $User->id)->first();
                        $record->seller_name = $sellerName->cname;

                        $agent = User::where('ref_code', $record->agent_reference)->first();
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            if (!$agent) {
                                $agent['name'] = "";
                                $agent['mobile'] = "";
                            }
                            $record->agent_name = $agent['name'];
                            $record->agent_mobile = $agent['mobile'];
                        } else{
                            $agentName = DB::table('company_info')->select('cname')->where('sid', $agent->id)->first();
                            $record->agent_name = $agentName->cname;
                            $record->agent_mobile = $agent['mobile'];
                        }

                        $record->products = json_decode($record->products);
                        foreach ($record->products as $temp) {
                            $count++;
                        }
                        $record->no_of_products = $count;
                    }
                    return response()->json(['error' => false, 'data' => $listreturn], 200);
                } else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            }
            if ($User->type_id == 3) {
                $listreturn = DB::table('orders')
                    ->join('users', 'users.id', 'orders.seller_id')
                    ->join('order_status', 'order_status.id', 'orders.status_id')
                    ->join('company_info', 'sid', 'users.id')
                    ->select('cname as seller_name', 'users.mobile', 'orders.agent_reference', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname')
                    ->where('orders.cust_id', $id)
                    ->whereIn('order_status.status_name', ['Dispatched', 'Rejected'])
                    ->orderby('orders.created_at', 'desc')
                    ->get()->toarray();

                if (!empty($listreturn)) {
                    foreach ($listreturn as $record) {
                        $count = 0;
                        $agent = User::where('ref_code', $record->agent_reference)->first();
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            if (!$agent) {
                                $agent['name'] = "";
                                $agent['mobile'] = "";
                            }
                            $record->agent_name = $agent['name'];
                            $record->agent_mobile = $agent['mobile'];
                        } else{
                            $agentName = DB::table('company_info')->select('cname')->where('sid', $agent->id)->first();
                            $record->agent_name = $agentName->cname;
                            $record->agent_mobile = $agent['mobile'];
                        }

                        $record->cust_name = $User->name;
                        $sellerName = DB::table('company_info')->select('cname')->where('sid', $User->id)->first();
                        $record->cust_name = $sellerName->cname;

                        $record->products = json_decode($record->products);
                        foreach ($record->products as $temp) {
                            $count++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false, 'data' => $listreturn], 200);
                } else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            }
        }
        return response()->json(['error' => true, 'message' => 'Something went wrong']);
    }

    public function acceptRequest($id)
    {
        $notificationData = [];
        $order_data = [
            'isApproved' => 1,
            'status_id' => 2
        ];
        $order_update = Order::where('id', $id)->update($order_data);
        $ord = Order::find($id);
        $seller = User::join('company_info', 'company_info.sid', 'users.id')->where('sid', $ord->seller_id)->first();
        $cust = User::find($ord->cust_id);
        $cust_comp = User::join('company_info', 'company_info.sid', 'users.id')->where('sid', $ord->cust_id)->first();
        $data['title'] = 'Tap N Deal';
        $data['msg'] = 'Order ' . $ord->order_name . ' has been Accepted';

        $notificationData['type'] = "order";
        $notificationData['id'] = $id;
        $data['data'] = $notificationData;

        \Notification::send($cust, new onesignal($data));

        $n = new Notification;
        $n->receiver = $cust->id;
        $n->noti_for = $id;
        $n->description = $data['msg'];
        $n->type = "Order Accept";
        $n->date_time = date('Y-m-d H:i:s');
        $n->save();

        $salesman = emp_sel_rel::join('users', 'users.id', 'emp_sel_rel.emp_id')->where([['type_id', 6], ['seller_id', $ord->seller_id]])->first();
        if ($salesman) {
            $usr = User::find($salesman->id);
            $data['title'] = 'Tap N Deal';
            $data['msg'] = 'New order ' . $ord->order_name . ' received please get the product ready';

            $notificationData['type'] = "order";
            $notificationData['id'] = $id;
            $data['data'] = $notificationData;
            \Notification::send($usr, new onesignal($data));

            $n = new Notification;
            $n->receiver = $usr->id;
            $n->noti_for = $id;
            $n->description = $data['msg'];
            $n->type = "Order Accept";
            $n->date_time = date('Y-m-d H:i:s');
            $n->save();
        }
        if ($ord->agent_reference) {
            $agent = User::where('ref_code', $ord->agent_reference)->first();
            if ($agent) {
                $data['title'] = 'Tap N Deal';
                $data['msg'] = 'Order has been created by ' . $seller->cname . ' of your client ' . $cust_comp->cname;

                $notificationData['type'] = "order";
                $notificationData['id'] = $id;
                $data['data'] = $notificationData;

                \Notification::send($usr, new onesignal($data));

                $n = new Notification;
                $n->receiver = $agent->id;
                $n->noti_for = $id;
                $n->description = $data['msg'];
                $n->type = "Order Accept";
                $n->date_time = date('Y-m-d H:i:s');
                $n->save();
            }
        }
        if ($order_update == 1) {
            return response()->json(['error' => false, 'message' => ' Order Accepted Successfully'], 200);
        }
        return response()->json(['error' => true, 'message' => 'Record not found'], 500);

    }

    public function rejectRequest($id)
    {
        {
            $order_data = [
                'isApproved' => 2,
                'status_id' => 5
            ];
            $order_update = Order::where('id', $id)->update($order_data);
            $ord = Order::find($id);
            $usr = User::find($ord->cust_id);
            $arr = ['name' => $ord->order_name, 'status' => 'Rejected'];
            // \Notification::send($usr, new statusChange($arr));
            if ($order_update == 1) {
                return response()->json(['error' => false, 'message' => ' Order Rejected Successfully'], 200);
            }
            return response()->json(['error' => true, 'message' => 'Record not found'], 500);
        }
    }

    public function allStatus()
    {
        $status = \DB::table('order_status')->whereNotIn('status_name', ['Received', 'Rejected'])->get();
        return response()->json(['error' => false, 'status' => $status], 200);
    }

    public function status($type)
    {
        if ($type == 1 || $type == 4) {
            $status = \DB::table('order_status')->whereNotIn('status_name', ['Received', 'Rejected'])->get();
            return response()->json(['error' => false, 'status' => $status], 200);
        }
        if ($type == 6) {
            $status = \DB::table('order_status')->where('status_name', 'Ready')->get();
            return response()->json(['error' => false, 'status' => $status], 200);
        }
        if ($type == 5) {
            $status = \DB::table('order_status')->where('status_name', 'Dispatched')->get();
            return response()->json(['error' => false, 'status' => $status], 200);
        }
        return response()->json(['error' => false, 'status' => []], 200);
    }

    public function orderStatus($id)
    {
        $status = Order::join('order_status', 'order_status.id', 'orders.status_id')->select('status_id', 'status_name')->where('orders.id', $id)->first();
        if ($status != null)
            return response()->json(['error' => false, 'status' => $status], 200);
        return response()->json(['error' => true, 'message' => 'order not found'], 200);
    }

    public function changeStatus(Request $req, $oid)
    {
        $validator = Validator::make($req->all(), [
            'status_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()], 401);
        }
        $ordr = Order::find($oid);
        $notificationData = [];

        if ($ordr) {
            $ordr->status_id = $req->status_id;
            $ordr->save();
            $ostat = \DB::table('order_status')->select('status_name')->where('id', $req->status_id)->first();
            $data['title'] = 'Tap N Deal';
            $data['msg'] = 'Order ' . $ordr->order_name . ' has been ' . $ostat->status_name;

            $notificationData['type'] = "order";
            $notificationData['id'] = $oid;
            $data['data'] = $notificationData;

            $usr = User::find($ordr['cust_id']);
            \Notification::send($usr, new onesignal($data));

            $n = new Notification;
            $n->receiver = $usr->id;
            $n->noti_for = $oid;
            $n->description = $data['msg'];
            $n->type = "Order " . $ostat->status_name;
            $n->date_time = date('Y-m-d H:i:s');
            $n->save();
            if ($req->status_id == 3) {
                $salesman = emp_sel_rel::join('users', 'users.id', 'emp_sel_rel.emp_id')->where([['type_id', 5], ['seller_id', $ordr->seller_id]])->first();
                if ($salesman) {
                    $usr = User::find($salesman->id);
                    $data['title'] = 'Tap N Deal';
                    $data['msg'] = 'Please get bill ready for ' . $ordr->order_name . ' it is ready to dispatch';
                    $notificationData['type'] = "order";
                    $notificationData['id'] = $oid;
                    $data['data'] = $notificationData;
                    \Notification::send($usr, new onesignal($data));

                    $n = new Notification;
                    $n->receiver = $usr->id;
                    $n->noti_for = $oid;
                    $n->description = $data['msg'];
                    $n->type = "Order " . $ostat->status_name;
                    $n->date_time = date('Y-m-d H:i:s');
                    $n->save();
                }

            }
            return response()->json(['error' => false, 'message' => 'Order status change'], 200);
        }
        return response()->json(['error' => true, 'message' => 'Order not found'], 200);
    }

    public function orderList($id)
    {

        $user = User::find($id);
        if ($user->type_id == 4 || $user->type_id == 5 || $user->type_id == 6 || $user->type_id == 8) {
            $seller = emp_sel_rel::where('emp_id', $id)->first();
            $id = $seller->seller_id;
            $user = User::find($id);

        }

        if ($user) {
            if ($user->type_id == 1) {
                $listreturn = DB::table('orders')
                    ->join('users', 'users.id', 'orders.cust_id')
                    ->join('company_info', 'sid', 'users.id')
                    ->join('order_status', 'order_status.id', 'orders.status_id')
                    ->select('users.name as cust_name', 'users.profile_picture', 'users.id as cust_id', 'users.mobile', 'orders.agent_reference', 'orders.id as order_id', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname as cust_cname')
                    ->where('orders.seller_id', $id)
                    ->orderby('orders.created_at', 'desc')
                    ->get()->toarray();
                if (!empty($listreturn)) {
                    foreach ($listreturn as $record) {
                        $count = 0;
                        $cmp = \DB::table('company_info')->where('sid', $id)->first();
                        $record->seller_name = $user->name;
                        $record->seller_cname = $cmp->cname;

                        $agent = User::where('ref_code', $record->agent_reference)->join('company_info', 'users.id', 'company_info.sid')->first();
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            if (!$agent) {
                                $agent['name'] = "";
                                $agent['mobile'] = "";
                            }
                            $record->agent_name = $agent['name'];
                            $record->agent_mobile = $agent['mobile'];
                            $record->agent_cname = $agent['name'];
                        } else{

                            $record->agent_name = $agent->cname;
                            $record->agent_cname = $agent->cname;
                            $record->agent_mobile = $agent->mobile;
                        }

                        $record->products = json_decode($record->products);

                        foreach ($record->products as $temp) {
                            $count++;
                        }
                        $record->no_of_products = $count;
                    }
                    return response()->json(['error' => false, 'data' => $listreturn], 200);
                } else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            }
            if ($user->type_id == 3) {
                $listreturn = DB::table('orders')
                    ->join('users', 'users.id', 'orders.seller_id')
                    ->join('company_info', 'users.id', 'company_info.sid')
                    ->join('order_status', 'order_status.id', 'orders.status_id')
                    ->select('users.name as seller_name', 'users.mobile', 'orders.agent_reference', 'orders.order_name', 'orders.total_price as order_price', 'orders.created_at as order_date', 'orders.products', 'order_status.status_name', 'order_status.id as status_id', 'orders.notes', 'cname as seller_cname')
                    ->where('orders.cust_id', $id)
                    ->orderby('orders.created_at', 'desc')
                    ->get()->toarray();

                if (!empty($listreturn)) {
                    foreach ($listreturn as $record) {
                        $count = 0;
                        $record->products = json_decode($record->products);

                        $agent = User::where('ref_code', $record->agent_reference)->join('company_info', 'users.id', 'company_info.sid')->first();
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            if (!$agent) {
                                $agent['name'] = "";
                                $agent['mobile'] = "";
                            }
                            $record->agent_name = $agent['name'];
                            $record->agent_mobile = $agent['mobile'];
                            $record->agent_cname = $agent['name'];
                        } else{

                            $record->agent_name = $agent->cname;
                            $record->agent_cname = $agent->cname;
                            $record->agent_mobile = $agent->mobile;
                        }

                        /*$agent = User::where('ref_code', $record->agent_reference)->join('company_info', 'users.id', 'company_info.sid')->first();
                        $cname = $agent['cname'];
                        if (!$agent) {
                            $agent = custome_agent::where('ref_code', $record->agent_reference)->first();
                            $cname = " ";
                        }
                        if (!$agent) {
                            $agent['name'] = " ";
                            $agent['mobile'] = " ";
                        }
                        $record->agent_name = $agent['name'];
                        $record->agent_mobile = $agent['mobile'];
                        $record->agent_cname = $cname;*/


                        $cmp = \DB::table('company_info')->where('sid', $id)->first();
                        $record->cust_name = $cmp->cname;
                        $record->cust_cname = $cmp->cname;

                        foreach ($record->products as $temp) {
                            $count++;
                        }
                        $record->no_of_products = $count;
                    }

                    return response()->json(['error' => false, 'data' => $listreturn], 200);
                } else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            }
            if ($user->type_id == 2 || $user->type_id == 8) {
                $o_list = Order::where('agent_reference', $user->ref_code)
                    ->join('order_status', 'status_id', 'order_status.id')->select('orders.id', 'seller_id', 'cust_id')->orderby('orders.created_at', 'desc')->get();
                $list = array();
                $order = array();
                foreach ($o_list as $o) {
                    $list = Order::where('orders.id', $o['id'])->select('orders.*', 'order_status.status_name', 'orders.total_price as order_price', 'users.name as cust_name', 'users.profile_picture')
                        ->join('users', 'users.id', 'orders.cust_id')
                        ->join('order_status', 'status_id', 'order_status.id')
                        ->first();
                    $list['agent_name'] = $user->name;
                    $cmp = \DB::table('company_info')->where('sid', $id)->first();
                    $list['agent_cname'] = $cmp->cname;
                    $user1 = User::where('id', $o['seller_id'])->select('id', 'name')->first();
                    $cmp1 = \DB::table('company_info')->where('sid', $user1->id)->first();
                    $list['seller_name'] = $user1->name;
                    $list['seller_cname'] = $cmp1->cname;

                    $user2 = User::where('id', $o['cust_id'])->select('id', 'name')->first();
                    $cmp2 = \DB::table('company_info')->where('sid', $user2->id)->first();
                    $list['cust_cname'] = $cmp2->cname;
                    $order[] = $list;
                }
                if (count($order) > 0)
                    return response()->json(['error' => false, 'data' => $order], 200);
                else {
                    return response()->json(['error' => false, 'data' => null], 200);
                }
            } else {
                return response()->json(['error' => true, 'message' => 'Invalid user id..'], 400);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Invalid user id..'], 400);
        }
    }
}

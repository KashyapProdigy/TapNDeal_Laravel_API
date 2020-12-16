<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Order;
use App\User;
class PDFController extends Controller
{
    public function generatePDF($oid)
    {
        // dd(Order::find($oid));
        $order['order']=Order::join('order_status','order_status.id','status_id')->where('orders.id',$oid)->first();
        $order['seller']=User::join('company_info','users.id','company_info.sid')->where('sid',$order['order']->seller_id)->select('cname as name')->first();
        $order['cust']=User::join('company_info','users.id','company_info.sid')->where('sid',$order['order']->cust_id)->select('cname as name')->first();
        $order['agent']=User::join('company_info','users.id','company_info.sid')->where('ref_code',$order['order']->agent_reference)->select('cname as name')->first();
        // dd($order);
        $pdf = PDF::loadView('invoice',['order'=> $order]);
        // return $pdf->download('invoice.pdf');
        $pdf->save(public_path().'/invoices/Order'.$order['order']->order_name.'.pdf');
        // return view('invoice',['order'=>$order]);
        return response()->json(['error' => false, 'data' => "http://tapntrade.com/Tapndeal/public/pdf/Order".$order['order']->order_name.".pdf"], 200);
    }
}

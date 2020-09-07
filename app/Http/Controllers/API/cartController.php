<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Cart;
use App\Product;
use Carbon\Carbon;
use Validator;

class cartController extends Controller
{
    public function show($id)
    {
        $cart_price = 0 ;
        $cart = DB::table('carts')
            ->join('products','products.id','carts.product_id')
            ->join('users','users.id','carts.seller_id')
            ->select('carts.seller_id','carts.id as cart_id','users.name as seller_name','products.id as product_id','products.name as product_name','products.image as product_image','products.category','carts.qty','products.price as product_price','carts.created_at as cart_add_time')
            ->where('carts.cust_id',$id)
            ->get()->toarray();
            if($cart != null)
            {

                foreach ($cart as $record) {
                    $record->total_price = $record->product_price * $record->qty;
                    $cart_price = $cart_price + $record->total_price;
                }

                return response()->json(['error' => false,'cart_price'=>$cart_price ,'data'=>$cart],200);
            }
            else{
                return response()->json(['error' => false ,'data'=>null],200);
            }
            return response()->json(['error' => true ,'message'=>'something went wrong'],500);

    }

    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'product_id' => 'required',
            'cust_id' => 'required',
            'qty'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        $product = Product::find($req->product_id);
        $otherseller = Cart::where('cust_id',$req->cust_id)->first();
        $cartrecord = Cart::where('product_id',$req->product_id)->where('cust_id',$req->cust_id)->first();

        if($cartrecord != null )
        {
            return response()->json(['error' => true ,'message'=>'Product Already In Cart'],500);
        }

        if( $product != null )
        {
            if($otherseller != null)
            {
                if($otherseller->seller_id != $product->seller_id)
                {
                    return response()->json(['error' => true ,'seller'=>true,'message'=>'Cannot Add Product Of Other Sellers To Cart'],500);
                }
            }

            $cart=new Cart;
            $cart->product_id=$req->product_id;
            $cart->cust_id=$req->cust_id;
            $cart->seller_id=$product->seller_id;
            $cart->qty=$req->qty;

            if($cart->save())
            {
                return response()->json(['error' => false ,'message'=>' Cart Record Inserted Successfully'],200);
            }
            else
            {
                return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
            }
        }
        else
        {
            return response()->json(['error' => true ,'message'=>'Record Not Found'],500);
        }
    }

    public function delete($id,Request $req)
    {
        $validator = Validator::make($req->all(), [
            'cust_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        $cart_del=Cart::where('product_id',$id)->where('cust_id',$req->cust_id)->first();
        if($cart_del != null)
        {
            $cart_del->delete();
            return response()->json(['error' => false ,'message'=>'Product Removed From Cart'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);
    }

    public function update(Request $req,$cid)
    {
        $validator = Validator::make($req->all(), [
            'qty'=>'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $c=Cart::find($cid);
        if($c)
        {
            $c->qty=$req->qty;
            $c->save();
            return response()->json(['error' => false ,'message'=>'Cart Updated successfully..'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);
    }

    public function deleteByUserid($id)
    {
        $cart_del=Cart::where('cust_id',$id)->get();
        if($cart_del != null)
        {
            Cart::where('cust_id',$id)->delete();
            return response()->json(['error' => false ,'message'=>'Cart Records Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
    }

    public function check($id,Request $req)
    {
        $validator = Validator::make($req->all(), [
            'cust_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        $cart_check=Cart::where('product_id',$id)->where('cust_id',$req->cust_id)->first();
        if($cart_check != null)
        {
            return response()->json(['error' => false ,'exist' => true ,'message'=>'Already In Cart'],200);
        }
            return response()->json(['error' => true ,'message'=>'Record not found'],500);
    }
    public function count($id)
    {
        $cart_count=Cart::where('cust_id',$id)->count();
        if($cart_count != null)
        {
            return response()->json(['error' => false ,'data'=>$cart_count],200);
        }
        else{
            return response()->json(['error' => false ,'data'=>0],200);
        }
    }
}

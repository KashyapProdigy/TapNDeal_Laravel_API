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
<<<<<<< HEAD
        $cart_price = 0 ;
        $cart = DB::table('carts')
                            ->join('products','products.id','carts.product_id')
                            ->join('users','users.id','carts.seller_id')
                            ->select('carts.seller_id','users.name as seller_name','products.id as product_id','products.name as product_name','products.image as product_image','products.category','carts.qty','products.price as product_price','carts.created_at as cart_add_time')
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

=======
        $cart=Cart::where('user_id',$id)->get()->toarray();
        if(!empty($cart))
        {
            return response()->json(['error' => false ,'data'=>$cart],200);
        }
        return response()->json(['error' => true ,'message'=>'Invalid Id']);
>>>>>>> 7b515ff04d194ea0628dd723570e9c4838bcd3fe
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
<<<<<<< HEAD
            return response()->json(['error' => true ,'message'=>'Product Already In Cart'],500);
        }
=======
            return response()->json(['error' => false ,'message'=>' Cart Record Inserted Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Something went wrong'],500);
>>>>>>> 7b515ff04d194ea0628dd723570e9c4838bcd3fe

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
<<<<<<< HEAD
            $cart_del->delete();
            return response()->json(['error' => false ,'message'=>'Product Removed From Cart'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);
=======
            return response()->json(['error' => false ,'message'=>' Cart updated Successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found'],500);

>>>>>>> 7b515ff04d194ea0628dd723570e9c4838bcd3fe
    }

    public function deleteByUserid($id)
    {
        $cart_del=Cart::where('cust_id',$id)->get();
        if($cart_del != null)
        {
<<<<<<< HEAD
            Cart::where('cust_id',$id)->delete();
            return response()->json(['error' => false ,'message'=>'Cart Records Deleted'],200);
=======
            $cart_del->delete();
            return response()->json(['error' => false ,'message'=>'Cart Record Deleted'],200);
>>>>>>> 7b515ff04d194ea0628dd723570e9c4838bcd3fe
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
<<<<<<< HEAD
            return response()->json(['error' => false ,'data'=>$cart_count],200);
        }
        else{
            return response()->json(['error' => false ,'data'=>0],200);
        }
=======
            Cart::where('user_id',$id)->delete();
            return response()->json(['error' => false ,'message'=>'Cart Records Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Record not found']);
>>>>>>> 7b515ff04d194ea0628dd723570e9c4838bcd3fe
    }
}

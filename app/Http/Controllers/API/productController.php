<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use Carbon\Carbon;
use Validator;

class productController extends Controller
{
    public function show($id)
    {
        $product=Product::where('seller_id',$id)->get()->toarray()  ;
        if(!empty($product))
        {
            return response()->json(['product_list'=>$product],200);
        }
        else{
            return response()->json(['Error'=>'Product not available']);
        }
    }
    public function create(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'name' => 'required',
            'price' => 'required',
            'description' => 'required',
            'photos'=>'required',
            'category'=>'required',
            'tags'=>'required',
            'seller_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $product=new Product;
        $product->seller_id=$req->seller_id;
        $product->name=$req->name;
        $product->price=$req->price;
        $product->description=$req->description;
        $product->photos=$req->photos;
        $product->category=$req->category;
        $product->tags=$req->tags;
        $product->color_variant=$req->color_variant;
        $product->watermark=$req->watermark;
        $product->agents_id=$req->agents_id;
        $product->date_time=Carbon::now();
        if($product->save())
        {
            return response()->json(['success'=>'insert Successfully'],200);
        }
        else{
            return response()->json(['error'=>'something went wrong'],500);
        }
    }
        public function update(Request $req,$id)
        {

            $validator = Validator::make($req->all(), [
                'name' => 'required',
                'price' => 'required',
                'description' => 'required',
                'photos'=>'required',
                'category'=>'required',
                'tags'=>'required',
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $date_time=Carbon::now();
            $product_data=[
                'seller_id'=>$req->seller_id,
                'name'=>$req->name,
                'price'=>$req->price,
                'description'=>$req->description,
                'photos'=>$req->photos,
                'category'=>$req->category,
                'tags'=>$req->tags,
                'watermark'=>$req->watermark,
                'color_variant'=>$req->color_variant,
                'agents_id'=>$req->agents_id,
                'date_time'=>$date_time,
            ];
            $product_update=Product::where('id',$id)->update($product_data);
            if($product_update==1)
            {
                return response()->json(['success'=>'Product Updated Successfully']);
            }
            else{
                return response()->json(['error'=>'Record not found']);
            }
        }

        public function delete($id)
        {
            $product_delete=Product::find($id);
            if($product_delete)
            {
                $product_delete->delete();
                return response()->json(['success'=>'Product Deleted']);
            }
            return response()->json(['error'=>'Product not found']);
        }
}

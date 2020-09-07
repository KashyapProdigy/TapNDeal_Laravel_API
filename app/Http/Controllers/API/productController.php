<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Product;
use Carbon\Carbon;
use Validator;

class productController extends Controller
{
    public function show($id)
    {
        $product_list=Product::where('seller_id',$id)->get();
        if(!empty($product_list))
        {
            return response()->json(['error' => false ,'data'=>$product_list],200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'Product not available']);
        }
    }
    public function create(Request $req)
    {
        $names="";
        $watermark_name="";

        $validator = Validator::make($req->all(), [
            'name' => 'required',
            'price' => 'required',
            'description' => 'required',
            'image'=>'required',
            'category'=>'required',
            'tags'=>'required',
            'colors'=>'required',
            'seller_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

            $color_list = json_decode($req->colors);

            $image_list = json_decode($req->image);
            if( is_object($image_list) )
            {
                $uploadsCount=0;
                foreach ($image_list->array as $value) {
                    if(Storage::disk('temp')->exists($value)){
                        $file = Storage::disk('temp')->get($value);
                        $filename= time().$uploadsCount.'-prod.png';
                        Storage::disk('product')->put($filename, $file);
                        $file = Storage::disk('temp')->delete($value);
                        if($uploadsCount == 0){
                        $names = $names.$filename;
                        }
                        else if($uploadsCount > 0){
                        $names = $names.",".$filename;
                        }
                    }
                    else{
                        return response()->json(['error' => true ,'message'=>'Image does not exist'],500);
                    }
                $uploadsCount++;
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'Image File ERROR']);
            }

        // $image_list = json_decode($req->image);
        // if( is_array($image_list) || is_object($image_list) )
        // {
        //     $uploadsCount=0;
        //     foreach ($image_list as $key => $value) {
        //         $file = base64_decode($value);
        //         $filename= time().$uploadsCount.'-prod.png';
        //         Storage::disk('product')->put($filename, $file);
        //         if($uploadsCount == 0)
        //         {
        //         $names = $names.$filename;
        //         }
        //         else
        //         {
        //         $names = $names.",".$filename;
        //         }
        //         $uploadsCount++;
        //     }
        // }

        if($req->watermark != null)
        {
            $file2 = base64_decode($req->watermark);
            $watermark_name = time().'-wm.png';
            Storage::disk('watermark')->put($watermark_name, $file2);
        }

        //Multipart Images --> pass image[] in postman
        // if($req->hasfile('image'))
        // {
        //     $uploadsCount=0;
        //     foreach ($req->file('image') as $image){
        //         $filename=time().$uploadsCount.'-prod.'.$image->getClientOriginalExtension();
        //         $image->move(public_path().'/productPhotos/', $filename);
        //         $names = $names.$filename.",";
        //         $uploadsCount++;
        //     }
        // }
        // if($req->hasfile('watermark'))
        // {
        //         $file=$req->file('watermark');
        //         $watermark_name=time().$uploadsCount.'-wm.'.$file->getClientOriginalExtension();
        //         $file->move(public_path().'/watermarkPhotos/', $watermark_name);
        // }

        $product=new Product;
        $product->seller_id=$req->seller_id;
        $product->name=$req->name;
        $product->price=$req->price;
        $product->description=$req->description;
        $product->image=$names;
        $product->category=$req->category;
        $product->tags=$req->tags;
        $product->colors=$req->colors;
        $product->watermark=$watermark_name;
        $product->agents_id=$req->agents_id;
        // $product->date_time=Carbon::now();
        if($product->save())
        {
            return response()->json(['error' => false ,'message'=>'insert Successfully'],200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'something went wrong'],500);
        }
    }

    public function update(Request $req,$id)
        {

            $validator = Validator::make($req->all(), [
                'name' => 'required',
                'price' => 'required',
                'description' => 'required',
                // 'image'=>'required',
                'category'=>'required',
                'tags'=>'required',
                'colors'=>'required',
                'seller_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
            }
            $names="";
            if($req->oldImages)
            {
                $names=$req->oldImages;
            }
            if($req->image)
            {
            $image_list = json_decode($req->image);
            if( is_object($image_list) )
            {
                $uploadsCount=0;
                foreach ($image_list->array as $value) {
                    if(Storage::disk('temp')->exists($value)){
                        $file = Storage::disk('temp')->get($value);
                        $filename= time().$uploadsCount.'-prod.png';
                        Storage::disk('product')->put($filename, $file);
                        $file = Storage::disk('temp')->delete($value);
                        if($uploadsCount == 0){
                            if($names!="")
                            {
                                $names = $names.",".$filename;
                            }
                            else{
                            $names = $names.$filename;
                            }
                        }
                        else if($uploadsCount > 0){
                        $names = $names.",".$filename;
                        }
                    }
                    else{
                        return response()->json(['error' => true ,'message'=>'Image does not exist'],500);
                    }
                $uploadsCount++;
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'Image File ERROR']);
            }
        }

            // $image_list = json_decode($req->image);
            // if( is_array($image_list) || is_object($image_list) )
            // {
            //     $uploadsCount=0;
            //     foreach ($image_list as $key => $value) {
            //         $file = base64_decode($value);
            //         $filename= time().$uploadsCount.'-prod.png';
            //         Storage::disk('product')->put($filename, $file);
            //         $names = $names.$filename.",";
            //         $uploadsCount++;
            //     }
            // }

            if($req->watermark != null)
            {
                $file2 = base64_decode($req->watermark);
                $watermark_name = time().'-wm.png';
                Storage::disk('watermark')->put($watermark_name, $file2);
            }
            else{
                $watermark_name="";
            }
            $date_time=Carbon::now();
            $product_data=[
                'seller_id'=>$req->seller_id,
                'name'=>$req->name,
                'price'=>$req->price,
                'description'=>$req->description,
                'image'=>$names,
                'category'=>$req->category,
                'tags'=>$req->tags,
                'watermark'=>$watermark_name,
                'colors'=>$req->colors,
                'agents_id'=>$req->agents_id,
                // 'date_time'=>$date_time,
            ];
            $product_update=Product::where('id',$id)->update($product_data);
            if($product_update==1)
            {
                return response()->json(['error' => false ,'message'=>'Product Updated Successfully'],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'Record not found']);
            }
        }

    public function upload(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'name' => 'required',
            'image'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }

        if($req->image != null && $req->name != null)
        {
                $file = base64_decode($req->image);
                Storage::disk('temp')->put($req->name, $file);
                if(Storage::disk('temp')->exists($req->name))
                {
                    return response()->json(['error' => false ,'message'=>'Image Uploaded Successfully'],200);
                }
        }
        else
        {
            return response()->json(['error' => true,'message'=>'Something Went Wrong'],500);
        }

    }

    public function delete($id)
    {
        $product_delete=Product::find($id);
        if($product_delete)
        {
            $product_delete->delete();
            return response()->json(['error' => false ,'message'=>'Product Deleted'],200);
        }
        return response()->json(['error' => true ,'message'=>'Product not found']);
    }
}

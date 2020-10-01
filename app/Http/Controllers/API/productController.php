<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Product;
use Carbon\Carbon;
use Validator;
use File;
use App\Notifications\ProductAdd;
use App\AgentCategoryRelationship;
use App\CustomerCategoryRelationship;
use App\User;
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
    public function showPro($id)
    {
        $product_list=Product::find($id);
        if(!empty($product_list))
        {
            return response()->json(['error' => false ,'data'=>$product_list],200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'Product not available'],400);
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
            $image_list = $req->image['array'];
            if( $image_list != null)
            {
                $uploadsCount=0;
                foreach ($image_list as $value) {
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
        $product->fid=$req->fid;
        $product->isCatalog=$req->isCatalog;
        // $product->date_time=Carbon::now();

        if($product->save())
        {
            $sel=User::select('name')->where('id',$req->seller_id)->first();
            $prdct=['seller'=>$sel->name];
            $buyer=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$req->seller_id)->where('isBlocked',0)->get()->toarray();
            foreach($buyer as $b)
            {   
                
                $usr=User::find($b['cust_id']);
                \Notification::send($usr, new ProductAdd($prdct));
            }
            $agent=AgentCategoryRelationship::select('agent_id')->where('seller_id',$req->seller_id)->where('isBlocked',0)->get()->toarray();
            foreach($agent as $b)
            {
                $usr=User::find($b['agent_id']);
                \Notification::send($usr, new ProductAdd($prdct));
            }
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
            $prdct=Product::where('id',$id)->first();
            if($prdct)
            {
                $names="";
                if($prdct['image'])
                {
                    $names=$prdct['image'];
                }
                
                if($req->image)
                {
                    $image_list = $req->image['array'];
                    if( $image_list != null)
                    {
                        $uploadsCount=0;
                        foreach ($image_list as $value) {
                            if(Storage::disk('temp')->exists($value)){
                                $file = Storage::disk('temp')->get($value);
                                $filename= time().$uploadsCount.'-prod.png';
                                Storage::disk('product')->put($filename, $file);
                                // $file = Storage::disk('temp')->delete($value);
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

                if($req->watermark != null)
                {
                    $file2 = base64_decode($req->watermark);
                    $watermark_name = time().'-wm.png';
                    Storage::disk('watermark')->put($watermark_name, $file2);
                }
                else{
                    $watermark_name="";
                }
                
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
    public function disable($pid)
    {
        $product=Product::find($pid);
        
        if($product)
        {
            $product->isDisabled=1;
            $product->save();
            return response()->json(['error' => false ,'message'=>'Product Disabled successfully..'],200);
        }
        return response()->json(['error' => true ,'message'=>'Product not found'],400);
    }
    public function enable($pid)
    {
        $product=Product::find($pid);
        
        if($product)
        {
            $product->isDisabled=0;
            $product->save();
            return response()->json(['error' => false ,'message'=>'Product Enabled successfully..'],200);
        }
        return response()->json(['error' => true ,'message'=>'Product not found'],400);
    }
    public function search(Request $req)
    {
        $srch=$req->search;
        $products=Product::where('name','like','%'.$srch.'%')->orwhere('tags','like','%'.$srch.'%')->orwhere('colors','like','%'.$srch.'%')->get();
        return response()->json(['error' => false ,'data'=>$products],200);
        
    }
    public function searchPro(Request $req,$sid)
    {
        $srch=$req->search;
        $product=Product::where('seller_id',$sid)->Where(function ($query) use($srch) {
            
            $query->where('name','like','%'.$srch.'%')
                ->orwhere('tags','like','%'.$srch.'%');
        })->get();
        return response()->json(['error' => false ,'data'=>$product],200);
        
    }
    public function delImg($pid,$img)
    {
        $prdct=Product::find($pid);
        if($prdct)
        {
            $images=explode(',',$prdct->image);
            if (($key = array_search($img, $images)) !== false) {
                unset($images[$key]);
                Storage::disk('temp')->delete($images);
                Storage::disk('product')->delete($images);
                $prdct->image=implode(',',$images);
                $prdct->save();
                return response()->json(['error' => false ,'message'=>'Product Deleted successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'image not found'],400);
        }
        return response()->json(['error' => true ,'message'=>'Product not found'],400);
    }
}

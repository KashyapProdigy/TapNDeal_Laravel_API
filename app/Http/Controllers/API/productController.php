<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Product;
use Carbon\Carbon;
use Validator;
use File;
use App\Notifications\onesignal;
use App\AgentCategoryRelationship;
use App\CustomerCategoryRelationship;
use App\User;
use App\emp_sel_rel;
use App\folderModel;
use App\sharedProducts;
use App\Notification;
class productController extends Controller
{
    public function show($id)
    {
        $product_list=Product::where('seller_id',$id)->orderBy('created_at','desc')->get();
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
            $folder=folderModel::find($product_list->fid);
            if($folder)
            {
                $product_list->fname=$folder->fname;
            }
            else{
                $product_list->fname=null;
            }
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
            // 'image'=>'required',
            'category'=>'required',
            'tags'=>'required',
            'colors'=>'required',
            'seller_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $User=User::find($req->seller_id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$req->seller_id)->first();
            $req->seller_id=$seller->seller_id;
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
            // return response()->json(['error' => true ,'message'=>'Image File ERROR']);
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
        $notificationData = [];
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
        $product->stock=$req->stock;
        if($req->isCatalog)
        {
            $product->isCatalog=$req->isCatalog;
        }

        // $product->date_time=Carbon::now();

        if($product->save())
        {
            $sel=User::join('company_info','company_info.sid','users.id')->where('sid',$req->seller_id)->first();
            $prdct=['seller'=>$sel->name];
            $cat=array();
            if($req->category=="A+")
            {
                $cat=["A+"];
            }
            else if($req->category=="A")
            {
                $cat=["A+","A"];
            }
            else if($req->category=="B+")
            {
                $cat=["A+","A","B+"];
            }
            else
            {
                $cat=["A+","A","B+","B"];
            }
            $buyer=CustomerCategoryRelationship::select('cust_id')->where('seller_id',$req->seller_id)->whereIn('category',$cat)->where('isBlocked',0)->get()->toarray();
            $data['title']='Tap N Deal';
            $data['msg']="New product has been added by ".$sel->cname;
            $notificationData['type'] = "product";
            $notificationData['id'] = $product->id;
            $data['data'] = $notificationData;
            $usr=User::whereIn('id',$buyer)->get();
            \Notification::send($usr, new onesignal($data));
            foreach($buyer as $b)
            {
                $n=new Notification;
                $n->receiver=$b['cust_id'];
                $n->noti_for=$product->id;
                $n->description=$data['msg'];
                $n->type="Product Add";
                $n->date_time=date('Y-m-d H:i:s');
                $n->save();
            }
            $agent=AgentCategoryRelationship::select('agent_id')->where('seller_id',$req->seller_id)->where('isBlocked',0)->get()->toarray();

            $usr=User::whereIn('id',$agent)->get();
            \Notification::send($usr, new onesignal($data));
            foreach($agent as $b)
            {
                $n=new Notification;
                $n->receiver=$b['agent_id'];
                $n->noti_for=$product->id;
                $n->description=$data['msg'];
                $n->type="Product Add";
                $n->date_time=date('Y-m-d H:i:s');
                $n->save();
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
        $User=User::find($req->seller_id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$req->seller_id)->first();
            $req->seller_id=$seller->seller_id;
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
                    // return response()->json(['error' => true ,'message'=>'Image File ERROR']);
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
                'fid'=>$req->fid,
                'stock'=>$req->stock
            ];
            $product_update=Product::where('id',$id)->update($product_data);

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
            // $file = base64_decode($req->image);
            // $img = imagecreatefromstring($file);
            // //header('Content-Type: image/jpeg');
            // header('Content-Type: bitmap; charset=utf-8');
            // imagesavealpha($img, true);
            // imagejpeg($img,public_path('tempPhotos')."/".$req->name,60);
            // imagedestroy($img);
            // Storage::disk('temp')->put($req->name, $file);
            $percent = 1;
            header('Content-Type: image/jpeg');

            $data = base64_decode($req->image);
            $im = imagecreatefromstring($data);
            $width = imagesx($im);
            $height = imagesy($im);
            $newwidth = $width * $percent;
            $newheight = $height * $percent;

            $size=strlen($data)/1000;
            if($size <= 200)
            {
                $qu=90;
            }
            else if($size <=400)
            {
                $qu=60;
            }
            else if($size <=1000)
            {
                $qu=30;
            }
            else{
                $qu=20;
            }
            $thumb = imagecreatetruecolor($newwidth, $newheight);

            // Resize
            imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

            // Output
            imagejpeg($thumb,public_path('tempPhotos')."/".$req->name,$qu);
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
        $products=Product::where('name','like','%'.$srch.'%')->orwhere('tags','like','%'.$srch.'%')->orwhere('colors','like','%'.$srch.'%')->orderBy('created_at','desc')->get();
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

                Storage::disk('product')->delete($img);
                $prdct->image=implode(',',$images);
                $prdct->save();
                return response()->json(['error' => false ,'message'=>'Product Deleted successfully'],200);
            }
            if(Storage::disk('temp')->delete($img))
            {
                return response()->json(['error' => false ,'message'=>'Product Deleted successfully'],200);
            }
            return response()->json(['error' => true ,'message'=>'image not found'],400);
        }
        if(Storage::disk('temp')->delete($img))
        {
            return response()->json(['error' => false ,'message'=>'Product Deleted successfully'],200);
        }
        return response()->json(['error' => true ,'message'=>'Product not found'],400);
    }
    public function sellerPro($id)
    {
        $User=User::find($id);
        if($User->type_id==4 || $User->type_id==5 || $User->type_id==6 || $User->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$id)->first();
            $id=$seller->seller_id;

        }
        $products=Product::where('seller_id',$id)->orderBy('created_at','desc')->get()->toarray();
        if($products)
        {
            return response()->json(['error' => false ,'data'=>$products],200);
        }
        return response()->json(['error' => true ,'products'=>'Products not found'],400);
    }
    public function changePrice(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'pid' => 'required',
            'price'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $prdct=Product::find($req->pid);
        if($prdct)
        {
            $prdct->price=$req->price;
            $prdct->save();
            return response()->json(['error' => false ,'message'=>'Price Changed..'],200);
        }
    }
    public function addShareProduct(Request $req)
    {
        $pro=new sharedProducts;
        $pro->products=$req->products;
        $pro->save();
        return response()->json(['error' => false ,'id'=>$pro->id],200);
    }
    public function ViewShareProduct($sid)
    {
        $products=sharedProducts::find($sid);
        if($products)
        {
            $pid=explode(',',$products->products);
            $pro=Product::whereIn('id',$pid)->get();
            return response()->json(['error' => false ,'products'=>$pro],200);
        }
    }
}

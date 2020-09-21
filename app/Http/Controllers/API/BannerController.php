<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Banners;
use Validator;
use File;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function show($mid)
    {
        $banners=Banners::where('manu_id',$mid)->get();
        if(count($banners)>0)
        {
            return response()->json(['error' => false ,'data'=>$banners], 200);
        }
        return response()->json(['error' => false,'data'=>null ,'message'=>"first time banner show!"], 401);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'manufacturer_id' => 'required|numeric',
            'image'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $image_list = json_decode($req->image);
        if(count($image_list->array)>4)
            return response()->json(['error' => true ,'message'=>"You can't upload more then 4 banner..!"], 401);
        if( is_object($image_list) )
        {
            $uploadsCount=0;
            foreach ($image_list->array as $value) {
                if(Storage::disk('temp')->exists($value)){
                    $file = Storage::disk('temp')->get($value);
                    $filename= time().$uploadsCount.'-prod.png';
                    Storage::disk('banner')->put($filename, $file);
                    // $file = Storage::disk('temp')->delete($value);
                    if($uploadsCount == 0){
                    $names = $filename;
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
        $ban=new Banners;
        $ban->manu_id=$req->manufacturer_id;
        $ban->img_name=$names;
        if($ban->save())
        { 
            return response()->json(['error' => false ,'message'=>'Banner added Successfully'],200);
        }
        else{
            return response()->json(['error' => true ,'message'=>'something went wrong'],500);
        }
    }
    public function update(Request $req,$bid)
    {
        
        $image_list = json_decode($req->image);
        $names="";
        if($req->oldImages)
        {
            $names=$req->oldImages;
            $img=count(explode(',',$req->oldImages))+count($image_list->array);
            if($img>4)
            {
                return response()->json(['error' => true ,'message'=>"You can't upload more then 4 banner..!"],500);
            }
        }
        
        
        if( is_object($image_list) )
        {
            if(count($image_list->array)>4)
                return response()->json(['error' => true ,'message'=>"You can't upload more then 4 banner..!"], 500);
                
            $uploadsCount=0;
            foreach ($image_list->array as $value) {
                if(Storage::disk('temp')->exists($value)){
                    $file = Storage::disk('temp')->get($value);
                    $filename= time().$uploadsCount.'-prod.png';
                    Storage::disk('banner')->put($filename, $file);
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
        // return response()->json(['error' => true ,'message'=>$names]);
        if($req->deleted_img)
        {
            
            $images = explode(",", $req->deleted_img);

            foreach ($images as $image) {
                $image_path = public_path().'/BannerImages/'.$image;
                if(File::exists($image_path)) {
                    File::delete($image_path);
                }
                
            }
        }
        $ban=Banners::find($bid);
        if($ban)
        {
            $ban->img_name=$names;
            if($ban->save())
            { 
                return response()->json(['error' => false ,'message'=>'Banner updated Successfully'],200);
            }
            else{
                return response()->json(['error' => true ,'message'=>'something went wrong'],500);
            }
        }
        else{
            return response()->json(['error' => true ,'message'=>'Banner not found'],500);
        }
    }
    public function destroy(Request $req,$bid)
    {
        $validator = Validator::make($req->all(), [
            'del_img' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 500);
        }
        $banners=Banners::find($bid);
        if($banners)
        {
            $img=explode(',',$banners['img_name']);
            $del=explode(',',$req->del_img);
            foreach($del as $d){
                if (($key = array_search($d, $img)) !== false) {
                    unset($img[$key]);
                }
            }
            $img=implode(',',$img);
            $banners->img_name=$img;
            if($banners->save())
            {   
                if($req->del_img)
                {
                    
                    $images = explode(",", $req->del_img);

                    foreach ($images as $image) {
                        $image_path = public_path().'/BannerImages/'.$image;
                        if(File::exists($image_path)) {
                            File::delete($image_path);
                        }
                        
                    }
                }
                return response()->json(['error' => false ,'message'=>"Banner(s) deleted successfully"],200); 
            }
            return response()->json(['error' => true ,'message'=>"Somethings went wrong..!"],200);  
        }
        return response()->json(['error' => true ,'message'=>"Banner not found"],200);  
        
    }
}

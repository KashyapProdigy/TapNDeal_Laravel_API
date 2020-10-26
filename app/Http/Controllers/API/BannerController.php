<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Banners;
use Validator;
use File;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\emp_sel_rel;
class BannerController extends Controller
{
    public function show($mid)
    {
        $user=User::find($mid);
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$mid)->first();
            $mid=$seller->seller_id;
            
        }
        $banners=Banners::where('manu_id',$mid)->get();
        if(count($banners)>0)
        {
            return response()->json(['error' => false ,'data'=>$banners], 200);
        }
        return response()->json(['error' => false,'data'=>null ,'message'=>"first time banner show!"], 200);
    }
    public function create(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'manufacturer_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $user=User::find($req->manufacturer_id);
        if($user->type_id==4 || $user->type_id==5 || $user->type_id==6 || $user->type_id==8)
        {
            $seller=emp_sel_rel::where('emp_id',$req->manufacturer_id)->first();
            $req->manufacturer_id=$seller->seller_id;   
        } 
        $ban=new Banners;
        $ban->manu_id=$req->manufacturer_id;
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
        $validator = Validator::make($req->all(), [
            'oldImage' => 'required',
            'image'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $image = $req->image;
        $banners=Banners::find($bid);
        if($banners)
        {
            $img=explode(',',$banners['img_name']);
                $oi=$req->oldImage;
                if($oi=="d1.png" || $oi=="d2.png" || $oi=="d3.png" || $oi=="d4.png")
                {
                    if(Storage::disk('temp')->exists($image)){
                        $file = Storage::disk('temp')->get($image);
                        $filename= time().'-prod.png';
                        Storage::disk('banner')->put($filename, $file);
                        // $file = Storage::disk('temp')->delete($image);
                        $banners->img_name=$filename;
                        if($banners->save())
                        { 
                            return response()->json(['error' => false ,'message'=>'Banner added Successfully'],200);
                        }
                        else{
                            return response()->json(['error' => true ,'message'=>'something went wrong'],500);
                        }
                    }
                }
                if (($key = array_search($req->oldImage, $img)) !== false) {
                    unset($img[$key]);
                    $img[$key]=$image;
                    if(Storage::disk('temp')->exists($image)){
                        $file = Storage::disk('temp')->get($image);
                        $filename= time().'-prod.png';
                        Storage::disk('banner')->put($filename, $file);
                        $file = Storage::disk('temp')->delete($image);
                        $img[$key]=$filename;

                        $image_path = public_path().'/BannerImages/'.$req->oldImage;
                        if(File::exists($image_path)) {
                            File::delete($image_path);
                        }
                        $names=implode(',',$img);
                        $banners->img_name=$names;
                        if($banners->save())
                        { 
                            return response()->json(['error' => false ,'message'=>'Banner added Successfully'],200);
                        }
                        else{
                            return response()->json(['error' => true ,'message'=>'something went wrong'],500);
                        }
                    }
                    else{
                        return response()->json(['error' => true ,'message'=>'Image does not exist'],500);
                    }
                }
                else{
                    return response()->json(['error' => true ,'message'=>"Old image not found"],400);
                }
            
        }
        else{
            return response()->json(['error' => true ,'message'=>"Banner not found"],400);
        }
    
    }
    public function AddBanner(Request $req,$bid)
    {
        $validator = Validator::make($req->all(), [
            'image'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => true ,'message'=>$validator->errors()], 401);
        }
        $image=$req->image;
        $banners=Banners::find($bid);
        if($banners)
        {
            $img=$banners['img_name'];
            $image=$req->image;
            if(Storage::disk('temp')->exists($image)){
                $file = Storage::disk('temp')->get($image);
                $filename= time().'-prod.png';
                Storage::disk('banner')->put($filename, $file);
                // $file = Storage::disk('temp')->delete($image);
                if($img!="")
                {
                    $names=$img.",".$filename;
                }
                else{
                    $names=$filename;
                }
                $banners->img_name=$names;
                if($banners->save())
                { 
                    return response()->json(['error' => false ,'message'=>'Banner added Successfully'],200);
                }
                else{
                    return response()->json(['error' => true ,'message'=>'something went wrong'],500);
                }
            }
            else{
                return response()->json(['error' => true ,'message'=>'Image does not exist'],500);
            }
            
        }
        else{
            return response()->json(['error' => true ,'message'=>"Banner not found"],400);
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
            $del=$req->del_img;
            if (($key = array_search($del, $img)) !== false) {
                unset($img[$key]);
            }
    
            $img=implode(',',$img);
            $banners->img_name=$img;
            if($banners->save())
            {   
                if($req->del_img)
                {
                    $image_path = public_path().'/BannerImages/'.$req->del_img;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                return response()->json(['error' => false ,'message'=>"Banner(s) deleted successfully"],200); 
            }
            return response()->json(['error' => true ,'message'=>"Somethings went wrong..!"],200);  
        }
        return response()->json(['error' => true ,'message'=>"Banner not found"],200);  
        
    }
}

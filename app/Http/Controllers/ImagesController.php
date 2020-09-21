<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImagesController extends Controller
{
    public function productPicture($name)
    {
        $storagePath = public_path().'/productPhotos/'.$name;

        if(file_exists($storagePath))
        {
            return response()->file($storagePath);
        }
        else {
            return view('welcome');
        }


    }
    public function bannerPicture($name)
    {
        $storagePath = public_path().'/BannerImages/'.$name;

        if(file_exists($storagePath))
        {
            return response()->file($storagePath);
        }
        else {
            return view('welcome');
        }


    }
    public function watermarkPicture($name)
    {
        $storagePath = public_path().'/watermarkPhotos/'.$name;

        if(file_exists($storagePath))
        {
            return response()->file($storagePath);
        }
        else {
            return view('welcome');
        }
    }
}

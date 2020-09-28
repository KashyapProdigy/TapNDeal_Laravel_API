<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table="products";
    public $timestamps=false;
    protected $fillable = [
        'name','price','description','seller_id','image','tags','category','stoke','colors','watermark','agents_id'
    ];
}

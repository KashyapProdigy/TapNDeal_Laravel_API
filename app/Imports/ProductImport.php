<?php

namespace App\Imports;

use App\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

// HeadingRowFormatter::default('none');

class ProductImport implements ToModel,WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd(@$row[0]);
        return new Product([
           'name'       =>@$row[0],
           'description'=>@$row[1],
           'price'      =>@$row[2],
           'category'   =>@$row[3],
           'seller_id'  =>session()->get('uid'),
           'tags'       =>@$row[4],
           'colors'     =>@$row[5],
            'image'     =>@$row[6],  
        ]);
    } 
    public function rules(): array
    {
        // $class=@$row[4];
        return [
            // '*' =>'Required',
            '0'=>'required',
            '1'=>'required',
            '2'=>'required|numeric',
            '3'=>'required',
            '4'=>'required',
            '5'=>'required',
            '6'=>'required',
        ];
    }
}

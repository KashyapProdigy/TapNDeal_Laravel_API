<?php

namespace App\Imports;

use App\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class ProductImport implements ToModel,WithHeadingRow,WithValidation
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
           'name'       =>@$row['Name'],
           'description'=>@$row['Description'],
           'price'      =>@$row['Price'],
           'category'   =>@$row['Category'],
           'seller_id'  =>session()->get('uid'),
           'tags'       =>@$row['Tags'],
           'colors'     =>@$row['Colors'],
            'image'     =>@$row['Image'],  
            ''
        ]);
    } 
    public function rules(): array
    {
        // $class=@$row[4];
        return [
            'Name'=>'required',
            'Description'=>'required',
            'Price'=>'required|numeric',
            'Category'=>'required|in:A,A+,B',
            'Tags'=>'required',
            'Colors'=>'required',
            'Image'=>'required',
        ];
    }
}

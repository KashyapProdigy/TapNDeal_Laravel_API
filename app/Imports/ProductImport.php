<?php

namespace App\Imports;

use App\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Product([
           'name'       =>@$row['Product_name'],
           'description'=>@$row['Description'],
           'price'      =>@$row['Price'],
           'category'   =>@$row['Category'],
           'seller_id'  =>session()->get('uid'),
           'tags'       =>@$row['Tags'],
           'colors'     =>@$row['Colors'],
            'image'=>@$row['Image'],  
        ]);
    }
    public function rules(): array
    {
        return [
        '0'=>'required',
        ];
    }
}

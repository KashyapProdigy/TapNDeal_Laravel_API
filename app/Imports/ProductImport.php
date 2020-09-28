<?php

namespace App\Imports;

use App\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Product([
           'name'       =>@$row[0],
           'description'=>@$row[1],
           'price'      =>@$row[2],
           'category'   =>@$row[3],
           'seller_id'  =>session()->get('uid'),
           'tags'       =>@$row[4],
           'colors'     =>@$row[5],
           'agents_id'  =>@$row[6],
            
        ]);
    }
    public function rules(): array
    {
        
    }
}

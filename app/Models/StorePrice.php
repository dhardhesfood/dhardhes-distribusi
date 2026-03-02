<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePrice extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'price',
    ];
}

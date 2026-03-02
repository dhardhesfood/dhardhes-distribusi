<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSaleItem extends Model
{
    protected $fillable = [
        'cash_sale_id',
        'product_id',
        'qty',
        'bonus_qty',
        'price',
        'subtotal',
        'fee_nominal',
        'hpp_snapshot',
    ];

    protected $casts = [
        'qty' => 'integer',
        'bonus_qty' => 'integer',
        'price' => 'integer',
        'subtotal' => 'integer',
        'fee_nominal' => 'integer',
        'hpp_snapshot' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function cashSale()
    {
        return $this->belongsTo(CashSale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
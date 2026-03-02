<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitItem extends Model
{
    protected $fillable = [
        'visit_id',
        'product_id',
        'initial_stock',
        'remaining_stock',
        'sold_qty',
        'return_qty',
        'new_delivery_qty',
        'bonus_qty',
        'stock_reduction_qty', // 🔥 WAJIB ADA
        'price_snapshot',
        'fee_snapshot',
        'cost_snapshot',
    ];

    protected $casts = [
        'initial_stock'        => 'integer',
        'remaining_stock'      => 'integer',
        'sold_qty'             => 'integer',
        'return_qty'           => 'integer',
        'new_delivery_qty'     => 'integer',
        'bonus_qty'            => 'integer',
        'stock_reduction_qty'  => 'integer',
        'price_snapshot'       => 'decimal:2',
        'fee_snapshot'         => 'decimal:2',
        'cost_snapshot'        => 'decimal:2',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
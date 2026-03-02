<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesStockSessionItem extends Model
{
    protected $fillable = [
        'session_id',
        'product_id',
        'opening_qty',
        'system_remaining_qty',
        'physical_remaining_qty',
        'difference_qty',
    ];

    protected $casts = [
        'opening_qty'            => 'integer',
        'system_remaining_qty'   => 'integer',
        'physical_remaining_qty' => 'integer',
        'difference_qty'         => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function session()
    {
        return $this->belongsTo(SalesStockSession::class, 'session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
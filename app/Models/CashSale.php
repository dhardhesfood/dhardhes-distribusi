<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSale extends Model
{
    protected $fillable = [
        'user_id',
        'sale_date',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'paid_amount',
        'kasbon_amount',
        'fee_total',
        'status',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'integer',
        'discount' => 'integer',
        'total' => 'integer',
        'paid_amount' => 'integer',
        'kasbon_amount' => 'integer',
        'fee_total' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CashSaleItem::class);
    }

    public function kasbon()
    {
        return $this->morphOne(Kasbon::class, 'reference');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    protected $fillable = [
        'visit_id',
        'store_id',
        'user_id',
        'transaction_date',
        'total_amount',
        'cash_paid',
        'total_fee',
        'total_hpp',
    ];

    public function items()
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
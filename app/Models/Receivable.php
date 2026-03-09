<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $fillable = [
        'sales_transaction_id',
        'store_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'due_date',
    ];

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTransactionItem extends Model
{
    protected $fillable = [
        'sales_transaction_id',
        'product_id',
        'quantity_sold',
        'price_snapshot',
        'fee_snapshot',
        'cost_snapshot',
        'subtotal_amount',
        'subtotal_fee',
        'subtotal_hpp', // ✅ TAMBAHAN WAJIB
    ];

    protected $casts = [
        'price_snapshot'  => 'decimal:2',
        'fee_snapshot'    => 'decimal:2',
        'cost_snapshot'   => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'subtotal_fee'    => 'decimal:2',
        'subtotal_hpp'    => 'decimal:2', // ✅ TAMBAHAN WAJIB
    ];

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

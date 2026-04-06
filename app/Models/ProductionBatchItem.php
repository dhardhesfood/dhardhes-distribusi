<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionBatchItem extends Model
{
    protected $fillable = [
        'production_batch_id',
        'product_variant_id',
        'quantity',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
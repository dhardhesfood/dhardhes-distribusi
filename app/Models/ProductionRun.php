<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionRun extends Model
{
    protected $fillable = [
        'product_id',
        'output_gram',
        'total_material_cost',
        'labor_rate_per_gram',
        'labor_percentage',
        'total_labor_cost',
        'hpp_per_gram',
        'notes',
        'created_by',
        'photo',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
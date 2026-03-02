<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitBonus extends Model
{
    protected $fillable = [
        'visit_id',
        'product_id',
        'qty',
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

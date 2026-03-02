<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProductCostHistory extends Model
{
    protected $fillable = [
        'product_id',
        'cost',
        'effective_date',
        'created_by',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'effective_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil cost yang berlaku pada tanggal tertentu
     */
    public function scopeEffectiveAt(Builder $query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        return $query->where('effective_date', '<=', $date)
                     ->orderByDesc('effective_date');
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil cost aktif produk berdasarkan tanggal
     */
    public static function getCostForDate($productId, $date)
    {
        $record = self::where('product_id', $productId)
            ->effectiveAt($date)
            ->first();

        return $record ? $record->cost : 0;
    }
}

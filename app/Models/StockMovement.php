<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'reference_id',
        'reference_type',
        'session_id',   // <<< TAMBAHKAN INI
        'notes',
    ];

    protected $casts = [
        'product_id'   => 'integer',
        'quantity'     => 'integer',
        'reference_id' => 'integer',
        'session_id'   => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function visit()
    {
    return $this->belongsTo(\App\Models\Visit::class, 'reference_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: GET SALES STOCK (STOK MOBIL SALES)
    |--------------------------------------------------------------------------
    */

    public static function getSalesProductStock(int $productId): int
    {
        return (int) self::where('product_id', $productId)
            ->sum('quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL: SAFETY CHECK (VALID TYPES)
    |--------------------------------------------------------------------------
    */

    public static function validTypes(): array
    {
        return [
            'warehouse_out',
            'warehouse_in',
            'send_to_store',
            'return_from_store',
            'adjustment',
            'conversion_sale',
            'bonus',
        ];
    }
}
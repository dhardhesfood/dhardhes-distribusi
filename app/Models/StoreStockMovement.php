<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreStockMovement extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'quantity',
        'type',
        'reference_id',
        'reference_type',
        'notes',
    ];

    /**
     * Ambil saldo stok toko per produk (realtime dari ledger)
     */
    public static function getStoreProductStock($storeId, $productId)
    {
        return self::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Relasi ke Store
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relasi ke Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

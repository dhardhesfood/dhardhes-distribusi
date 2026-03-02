<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'default_selling_price',
        'default_fee_nominal',
        'warehouse_price',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'default_selling_price' => 'decimal:2',
        'default_fee_nominal'   => 'decimal:2',
        'warehouse_price'       => 'decimal:2',
        'is_active'             => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Histori HPP produk
     */
    public function costHistories()
    {
        return $this->hasMany(ProductCostHistory::class);
    }

    /**
     * Harga khusus per toko (override harga default)
     */
    public function storePrices()
    {
        return $this->hasMany(StorePrice::class);
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil HPP aktif berdasarkan tanggal tertentu
     */
    public function getCostAt($date)
    {
        return ProductCostHistory::getCostForDate($this->id, $date);
    }
}

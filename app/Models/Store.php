<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'area_id',
        'name',
        'owner_name',
        'phone',
        'address',
        'city',
        'visit_interval_days',
        'last_visit_date',
        'is_active',
    ];

    protected $casts = [
        'area_id' => 'integer',
        'visit_interval_days' => 'integer',
        'last_visit_date' => 'date',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Store belongs to Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Store has many Visits
     */
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Store has many custom prices
     */
    public function storePrices()
    {
        return $this->hasMany(StorePrice::class);
    }

    /**
     * Store stock movements (ledger toko)
     */
    public function stockMovements()
    {
        return $this->hasMany(StoreStockMovement::class);
    }
}

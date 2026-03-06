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

/*
|--------------------------------------------------------------------------
| STATUS KUNJUNGAN TOKO
|--------------------------------------------------------------------------
*/

public function getVisitStatusAttribute()
{
    if (!$this->last_visit_date) {
        return null;
    }

    $today = \Carbon\Carbon::today();

    $nextVisit = \Carbon\Carbon::parse($this->last_visit_date)
        ->addDays($this->visit_interval_days);

    if ($today->lt($nextVisit)) {
        return 'safe';
    }

    if ($today->eq($nextVisit)) {
        return 'today';
    }

    $lateDays = $nextVisit->diffInDays($today);

    if ($lateDays > 135) {
        return 'withdraw';
    }

    if ($lateDays > 100) {
        return 'heavy';
    }

    return 'late';
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Store;
use App\Models\User;
use App\Models\VisitItem;
use App\Models\SalesTransaction;
use App\Models\StoreStockMovement;
use App\Models\StockMovement;
use App\Models\VisitBonus;
use App\Models\SalesSettlement;
use Carbon\Carbon;

class Visit extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'visit_date',
        'next_visit_date',
        'photo_path',
        'status',
        'notes',
        'admin_fee',
    ];

    protected $casts = [
        'visit_date'      => 'date',
        'next_visit_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(VisitItem::class);
    }

    public function bonuses()
    {
        return $this->hasMany(VisitBonus::class);
    }

    public function salesTransaction()
    {
        return $this->hasOne(SalesTransaction::class, 'visit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HISTORY MOVEMENTS (AUDIT TRAIL)
    |--------------------------------------------------------------------------
    */

    public function storeMovements()
    {
        return $this->hasMany(StoreStockMovement::class, 'reference_id')
            ->where('reference_type', 'visit');
    }

    public function salesMovements()
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', 'visit');
    }

    /*
    |--------------------------------------------------------------------------
    | HARD LOCK PROTECTION (SETTLEMENT CLOSED)
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        // PROTECT UPDATE
        static::updating(function ($model) {

            $date = Carbon::parse($model->getOriginal('visit_date'))->format('Y-m-d');

            $settlement = SalesSettlement::where('user_id', $model->user_id)
                ->whereDate('settlement_date', $date)
                ->where('status', 'closed')
                ->first();

            if ($settlement) {
                throw new \Exception('Visit tidak dapat diubah. Settlement tanggal tersebut sudah ditutup.');
            }
        });

        // PROTECT DELETE
        static::deleting(function ($model) {

            $date = Carbon::parse($model->visit_date)->format('Y-m-d');

            $settlement = SalesSettlement::where('user_id', $model->user_id)
                ->whereDate('settlement_date', $date)
                ->where('status', 'closed')
                ->first();

            if ($settlement) {
                throw new \Exception('Visit tidak dapat dihapus. Settlement tanggal tersebut sudah ditutup.');
            }
        });
    }
}
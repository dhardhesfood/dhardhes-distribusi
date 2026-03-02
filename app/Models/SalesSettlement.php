<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SalesSettlementCostDetail;

class SalesSettlement extends Model
{
    protected $fillable = [
        'user_id',
        'created_by',
        'settlement_date',
        'total_sales_amount',
        'total_receivable_payment',
        'total_cost',
        'expected_amount',
        'actual_amount',
        'shortage_amount',
        'status',
    ];

    protected $casts = [
        'total_sales_amount'       => 'decimal:2',
        'total_receivable_payment' => 'decimal:2',
        'total_cost'               => 'decimal:2',
        'expected_amount'          => 'decimal:2',
        'actual_amount'            => 'decimal:2',
        'shortage_amount'          => 'decimal:2',
        'settlement_date'          => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function costDetails()
    {
        return $this->hasMany(
            SalesSettlementCostDetail::class,
            'sales_settlement_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HARD LOCK CHECK HELPER
    |--------------------------------------------------------------------------
    */

    public static function isClosed($userId, $date)
    {
        return self::where('user_id', $userId)
            ->whereDate('settlement_date', $date)
            ->where('status', 'closed')
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | HARD LOCK PROTECTION
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {

            if ($model->getOriginal('status') === 'closed') {

                if (!auth()->check() || auth()->user()->role !== 'admin') {
                    throw new \Exception('Settlement sudah closed dan tidak dapat diubah.');
                }

            }
        });

        static::deleting(function ($model) {

            if ($model->status === 'closed') {

                if (!auth()->check() || auth()->user()->role !== 'admin') {
                    throw new \Exception('Settlement sudah closed dan tidak dapat dihapus.');
                }

            }
        });
    }
}
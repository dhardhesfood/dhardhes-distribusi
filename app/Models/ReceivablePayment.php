<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesSettlement;
use Carbon\Carbon;

class ReceivablePayment extends Model
{
    protected $fillable = [
        'receivable_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HARD LOCK PROTECTION (SETTLEMENT CLOSED)
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        // PROTECT CREATE
        static::creating(function ($model) {

            $date = Carbon::parse($model->payment_date)->format('Y-m-d');

            $settlement = SalesSettlement::where('user_id', $model->user_id)
                ->whereDate('settlement_date', $date)
                ->where('status', 'closed')
                ->first();

            if ($settlement) {
                throw new \Exception('Settlement tanggal tersebut sudah ditutup.');
            }
        });

        // PROTECT UPDATE
        static::updating(function ($model) {

            $originalDate = Carbon::parse($model->getOriginal('payment_date'))->format('Y-m-d');

            $settlement = SalesSettlement::where('user_id', $model->user_id)
                ->whereDate('settlement_date', $originalDate)
                ->where('status', 'closed')
                ->first();

            if ($settlement) {
                throw new \Exception('Settlement tanggal tersebut sudah ditutup dan tidak dapat diubah.');
            }
        });

        // PROTECT DELETE
        static::deleting(function ($model) {

            $date = Carbon::parse($model->payment_date)->format('Y-m-d');

            $settlement = SalesSettlement::where('user_id', $model->user_id)
                ->whereDate('settlement_date', $date)
                ->where('status', 'closed')
                ->first();

            if ($settlement) {
                throw new \Exception('Settlement tanggal tersebut sudah ditutup dan tidak dapat dihapus.');
            }
        });
    }
}
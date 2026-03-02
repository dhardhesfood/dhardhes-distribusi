<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    protected $fillable = [
        'user_id',
        'created_by',
        'amount_total',
        'amount_paid',
        'type',
        'reference_id',
        'reference_type',
        'description',
        'status',
    ];

    protected $casts = [
        'amount_total' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC
    |--------------------------------------------------------------------------
    */

    public function getRemainingAttribute()
    {
        return $this->amount_total - $this->amount_paid;
    }

    public function markAsSettledIfPaid()
    {
        if ($this->remaining <= 0) {
            $this->status = 'settled';
            $this->save();
        }
    }
}

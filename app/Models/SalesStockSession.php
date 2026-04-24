<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SalesStockSession extends Model
{
    protected $fillable = [
        'user_id',
        'created_by',
        'start_date',
        'end_date',
        'status',
        'notes',
        'photo',
    ];

    protected $casts = [
    'start_date' => 'datetime',
    'end_date'   => 'datetime',
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

    public function items()
    {
        return $this->hasMany(SalesStockSessionItem::class, 'session_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOpen(Builder $query)
    {
        return $query->where('status', 'open');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public static function hasOpenSession(int $userId): bool
    {
        return self::where('user_id', $userId)
            ->where('status', 'open')
            ->exists();
    }
}
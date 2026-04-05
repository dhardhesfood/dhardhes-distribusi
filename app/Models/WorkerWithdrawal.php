<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerWithdrawal extends Model
{
    protected $fillable = [
    'requested_amount',
    'approved_amount',
    'status',
    'withdraw_date',
    'created_by',
];
}
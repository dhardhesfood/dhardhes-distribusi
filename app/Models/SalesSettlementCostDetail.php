<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesSettlement;

class SalesSettlementCostDetail extends Model
{
    protected $fillable = [
        'sales_settlement_id',
        'jenis_biaya',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function settlement()
    {
        return $this->belongsTo(SalesSettlement::class, 'sales_settlement_id');
    }
}

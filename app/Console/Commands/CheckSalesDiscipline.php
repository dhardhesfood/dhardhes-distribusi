<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class CheckSalesDiscipline extends Command
{
    protected $signature = 'app:check-sales-discipline';

    protected $description = 'Check daily sales discipline (request stok H-3)';

    public function handle()
    {
        $today = Carbon::today();
        $start = $today->copy()->startOfMonth();

        $salesUsers = User::where('role', 'sales')->get();

        foreach ($salesUsers as $user) {

/*
|--------------------------------------------------------------------------
| 1. HITUNG DAILY (PER HARI, CEK KE DEPAN)
|--------------------------------------------------------------------------
*/

for ($date = $start->copy(); $date->lte($today); $date->addDay()) {

    $coverage = DB::table('sales_stock_requests')
        ->where('user_id', $user->id)
        ->whereDate('request_date', '>=', $date->toDateString())
        ->whereMonth('request_date', $today->month)
        ->whereYear('request_date', $today->year)
        ->distinct()
        ->count('request_date');

    $isLate = $coverage < 3 ? 1 : 0;

    DB::table('sales_discipline_daily')->updateOrInsert(
        [
            'user_id' => $user->id,
            'date' => $date->toDateString()
        ],
        [
            'coverage_days' => $coverage,
            'is_late' => $isLate,
            'updated_at' => now(),
            'created_at' => now()
        ]
    );
}

/*
|--------------------------------------------------------------------------
| 2. HITUNG TOTAL TELAT DARI DAILY
|--------------------------------------------------------------------------
*/

$lateCount = DB::table('sales_discipline_daily')
    ->where('user_id', $user->id)
    ->whereMonth('date', $today->month)
    ->whereYear('date', $today->year)
    ->where('is_late', 1)
    ->count();


            

            /*
            |--------------------------------------------------------------------------
            | 3. HITUNG PENALTY RATE
            |--------------------------------------------------------------------------
            */

            $penaltyRate = 0;

            if ($lateCount >= 9) {
                $penaltyRate = 30;
            } elseif ($lateCount >= 7) {
                $penaltyRate = 20;
            } elseif ($lateCount >= 5) {
                $penaltyRate = 10;
            } elseif ($lateCount >= 3) {
                $penaltyRate = 5;
            }

            /*
            |--------------------------------------------------------------------------
            | 4. SIMPAN MONTHLY (FINAL)
            |--------------------------------------------------------------------------
            */

            DB::table('sales_discipline_monthly')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'month' => $today->month,
                    'year' => $today->year
                ],
                [
                    'late_count' => $lateCount,
                    'penalty_rate' => $penaltyRate,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }

        $this->info('RECOMPUTE FULL SUCCESS');
    }
}
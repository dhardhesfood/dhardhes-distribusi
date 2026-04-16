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

        // 🔥 reset monthly dulu
        DB::table('sales_discipline_monthly')
            ->where('user_id', $user->id)
            ->where('month', $today->month)
            ->where('year', $today->year)
            ->update([
                'late_count' => 0
            ]);

        for ($date = $start->copy(); $date->lte($today); $date->addDay()) {

            $coverage = DB::table('sales_stock_requests')
                ->where('user_id', $user->id)
                ->whereDate('request_date', '>=', $date)
                ->distinct()
                ->count('request_date');

            $isLate = $coverage < 3 ? 1 : 0;

            // 🔥 update daily
            DB::table('sales_discipline_daily')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'date' => $date
                ],
                [
                    'coverage_days' => $coverage,
                    'is_late' => $isLate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // 🔥 hitung monthly
            if ($isLate) {
                DB::table('sales_discipline_monthly')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'month' => $today->month,
                        'year' => $today->year
                    ],
                    [
                        'late_count' => DB::raw('late_count + 1'),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    $this->info('RECOMPUTE FULL SUCCESS');
}
}
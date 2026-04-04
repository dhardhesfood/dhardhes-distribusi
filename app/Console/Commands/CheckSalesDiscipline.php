<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class CheckSalesDiscipline extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:check-sales-discipline';

    /**
     * The console command description.
     */
    protected $description = 'Check daily sales discipline (request stok H-3)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $month = $today->month;
        $year  = $today->year;

        // 🔹 Ambil semua sales
        $salesUsers = User::where('role', 'sales')->get();

        foreach ($salesUsers as $user) {

            // 🔹 Hitung coverage (jumlah hari unik ke depan)
            $coverage = DB::table('sales_stock_requests')
                ->where('user_id', $user->id)
                ->whereDate('request_date', '>=', Carbon::today())
                ->distinct()
                ->count('request_date');

            // 🔹 Tentukan telat / tidak
            $isLate = $coverage < 3 ? 1 : 0;

            // 🔹 Cek apakah sudah ada record hari ini
            $existing = DB::table('sales_discipline_daily')
                ->where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            // 🔹 Insert / Update daily
            DB::table('sales_discipline_daily')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'date' => $today
                ],
                [
                    'coverage_days' => $coverage,
                    'is_late' => $isLate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // 🔹 Update monthly (hanya jika belum pernah dihitung hari ini)
            if (!$existing && $isLate) {

                DB::table('sales_discipline_monthly')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'month' => $month,
                        'year' => $year
                    ],
                    [
                        'late_count' => DB::raw('late_count + 1'),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        $this->info('Sales discipline checked successfully.');
    }
}
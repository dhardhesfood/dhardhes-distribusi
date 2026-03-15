<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RewardService
{
    public static function rebuildMonthlyReward($userId, $month, $year)
    {
        $startDate = Carbon::create($year,$month,1)->startOfMonth();
        $endDate   = Carbon::create($year,$month,1)->endOfMonth();

        $konsinyasi = DB::table('sales_transactions')
            ->where('user_id',$userId)
            ->whereBetween('transaction_date',[$startDate,$endDate])
            ->sum('total_fee');

        $tunai = DB::table('cash_sales')
            ->where('user_id',$userId)
            ->where('status','locked')
            ->whereBetween('sale_date',[$startDate,$endDate])
            ->sum('fee_total');

        $totalGenerated = $konsinyasi + $tunai;

        $percent = 0;

        if ($totalGenerated >= 5000000) {
            $percent = 12;
        } elseif ($totalGenerated >= 3000000) {
            $percent = 10;
        } elseif ($totalGenerated >= 1500000) {
            $percent = 7;
        } elseif ($totalGenerated >= 500000) {
            $percent = 5;
        }

        $kpiReward = $totalGenerated * $percent / 100;

        $missionReward = DB::table('mission_rewards')
            ->where('user_id',$userId)
            ->whereMonth('reward_date',$month)
            ->whereYear('reward_date',$year)
            ->sum('reward_amount');

        $totalReward = $kpiReward + $missionReward;

        DB::table('sales_total_rewards')->updateOrInsert(
            [
                'user_id' => $userId,
                'month' => $month,
                'year' => $year
            ],
            [
                'kpi_reward' => $kpiReward,
                'mission_reward' => $missionReward,
                'total_reward' => $totalReward,
                'calculated_at' => now(),
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
    }
}
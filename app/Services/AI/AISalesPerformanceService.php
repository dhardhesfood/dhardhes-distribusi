<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AISalesPerformanceService
{
    public static function getPerformanceSummary($userId = null, $month = null, $year = null)
    {
        $userId = $userId ?? auth()->id();

        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;

        $startDate = Carbon::create($year,$month,1)->startOfMonth()->toDateString();
        $endDate   = Carbon::create($year,$month,1)->endOfMonth()->toDateString();

        // ======================
        // TOTAL FEE (REAL)
        // ======================

        $feeKonsinyasi = DB::table('sales_transactions')
            ->where('user_id',$userId)
            ->whereBetween('transaction_date',[$startDate,$endDate])
            ->sum('total_fee');

        $feeTunai = DB::table('cash_sales')
            ->where('user_id',$userId)
            ->where('status','locked')
            ->whereBetween('sale_date',[$startDate,$endDate])
            ->sum('fee_total');

        $totalFee = $feeKonsinyasi + $feeTunai;

        // ======================
        // TOTAL HARI BERJALAN
        // ======================

        $today = now();
        $daysPassed = $today->day;
        $daysInMonth = $today->daysInMonth;
        $remainingDays = $daysInMonth - $daysPassed;

        // ======================
        // AVG PER HARI
        // ======================

        $avgPerDay = $daysPassed > 0
            ? $totalFee / $daysPassed
            : 0;

        // ======================
        // ESTIMASI AKHIR BULAN
        // ======================

        $estimatedFinal = $totalFee + ($avgPerDay * $remainingDays);

        // ======================
        // TARGET REWARD (GLOBAL)
        // ======================

        $target = 3000000; // fokus utama ke reward 10%

        $gap = $target - $estimatedFinal;

        // ======================
        // STATUS TARGET
        // ======================

        if ($estimatedFinal >= $target) {
            $status = 'AMAN';
        } elseif ($estimatedFinal >= ($target * 0.85)) {
            $status = 'KEJAR SEDIKIT';
        } else {
            $status = 'BERAT';
        }

        // ======================
        // SIMULASI KEBUTUHAN
        // ======================

        $needFee = $gap > 0 ? $gap : 0;

        // asumsi rata-rata fee per pcs (nanti bisa kita presisikan)
        $avgFeePerPcs = 1500;

        $needQty = $avgFeePerPcs > 0
            ? ceil($needFee / $avgFeePerPcs)
            : 0;

        return [

            'total_fee' => (int)$totalFee,
            'avg_per_day' => (int)$avgPerDay,
            'estimated_final' => (int)$estimatedFinal,

            'target' => $target,
            'gap' => (int)$gap,
            'status' => $status,

            'remaining_days' => $remainingDays,

            'need_fee' => (int)$needFee,
            'need_qty' => (int)$needQty,
        ];
    }

    public static function generateInsights(array $data)
{
    $insights = [];

    $totalFee = $data['total_fee'];
    $avg = $data['avg_per_day'];
    $target = $data['target'];
    $remainingDays = $data['remaining_days'];
    $gap = $data['gap'];

    // ======================
    // HITUNG KEBUTUHAN REAL
    // ======================

    $dailyNeeded = $remainingDays > 0
        ? ceil($gap / $remainingDays)
        : 0;

    // ======================
    // TARGET ANALYSIS
    // ======================

    if ($gap > 0) {

        $insights[] = [
            'type' => 'warning',
            'title' => 'Target Belum Aman',
            'desc' => "Masih kurang Rp " . number_format($gap,0,',','.') .
                      ". Butuh ± Rp " . number_format($dailyNeeded,0,',','.') . "/hari."
        ];

    } else {

        $insights[] = [
            'type' => 'success',
            'title' => 'Target Aman',
            'desc' => "Target sudah tercapai. Tinggal jaga konsistensi."
        ];
    }

    // ======================
    // PERFORMANCE BERBASIS KEBUTUHAN
    // ======================

    if ($gap <= 0) {

        $insights[] = [
            'type' => 'success',
            'title' => 'Performa Sangat Aman',
            'desc' => "Kamu sudah melewati target. Fokus jaga stabilitas."
        ];

    } elseif ($avg >= $dailyNeeded) {

        $insights[] = [
            'type' => 'success',
            'title' => 'Performa Sudah Cukup',
            'desc' => "Rata-rata Rp " . number_format($avg,0,',','.') .
                      "/hari sudah cukup untuk kejar target."
        ];

    } elseif ($avg >= ($dailyNeeded * 0.7)) {

        $insights[] = [
            'type' => 'warning',
            'title' => 'Performa Hampir Cukup',
            'desc' => "Butuh sedikit peningkatan. Target masih sangat mungkin dikejar."
        ];

    } else {

        $insights[] = [
            'type' => 'danger',
            'title' => 'Performa Kurang',
            'desc' => "Rata-rata terlalu rendah. Perlu peningkatan signifikan."
        ];
    }

    // ======================
    // SIMULASI REALISTIS
    // ======================

    $increase = 20000;
    $simulasi = $avg + $increase;

    $futureEstimate = $totalFee + ($simulasi * $remainingDays);

    if ($futureEstimate >= $target) {

        $insights[] = [
            'type' => 'info',
            'title' => 'Simulasi Aman',
            'desc' => "Jika naik ±20rb/hari, target bisa tercapai."
        ];

    } else {

        $insights[] = [
            'type' => 'danger',
            'title' => 'Simulasi Belum Aman',
            'desc' => "Tambahan 20rb/hari belum cukup. Perlu strategi tambahan."
        ];
    }

    return $insights;
}

}
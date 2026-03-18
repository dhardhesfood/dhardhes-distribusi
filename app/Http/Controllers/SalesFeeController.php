<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\RewardService;
use App\Services\AI\AISalesPerformanceService;
use App\Services\AI\AIInsightService;

class SalesFeeController extends Controller
{
    public function index()
    {

        $month = request('month') ?? Carbon::now()->month;
$year  = request('year') ?? Carbon::now()->year;

$startDate = Carbon::create($year,$month,1)->startOfMonth();
$endDate   = Carbon::create($year,$month,1)->endOfMonth();

        $query = DB::table('users as u')
            ->leftJoin('sales_transactions as st', 'st.user_id', '=', 'u.id')
            ->where('u.role', 'sales');

        // Jika yang login adalah sales, hanya tampilkan dirinya sendiri
        if (auth()->user()->role === 'sales') {
            $query->where('u.id', auth()->id());
        }

        $salesData = $query
            ->select(
                'u.id',
                'u.name',

                DB::raw("
                     COALESCE(SUM(
                     CASE
                     WHEN st.transaction_date BETWEEN '{$startDate}' AND '{$endDate}'
                     THEN st.total_fee
                     ELSE 0
                     END
                     ),0) as total_konsinyasi
                     "),

                     DB::raw("(
                    SELECT COALESCE(SUM(cs.fee_total),0)
                    FROM cash_sales cs
                    WHERE cs.user_id = u.id
                    AND cs.status = 'locked'
                    AND cs.sale_date BETWEEN '{$startDate}' AND '{$endDate}'
                    ) as total_tunai"),

                DB::raw('(
                    SELECT COALESCE(SUM(sfp.amount_paid),0)
                    FROM sales_fee_payments sfp
                    WHERE sfp.user_id = u.id
                ) as total_fee_paid'),

                DB::raw("(
                    SELECT COALESCE(SUM(rp.amount_paid),0)
                    FROM sales_reward_payments rp
                    WHERE rp.user_id = u.id
                    AND rp.month = {$month}
                    AND rp.year = {$year}
                    ) as total_reward_paid"),

                DB::raw('(
                    SELECT COALESCE(SUM(k.amount_total - k.amount_paid),0)
                    FROM kasbons k
                    WHERE k.user_id = u.id
                      AND k.status = "open"
                ) as kasbon_remaining')
            )
            ->groupBy('u.id', 'u.name')
            ->get();

        $finalData = [];

        $storeStats = DB::table('stores')
    ->select(

        DB::raw('COUNT(*) as total_stores'),

        DB::raw("
        SUM(
            CASE
            WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) > 135
            THEN 1 ELSE 0 END
        ) as withdraw_count
        "),

        DB::raw("
        SUM(
            CASE
            WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) > 100
            AND DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) <= 135
            THEN 1 ELSE 0 END
        ) as heavy_count
        "),

        DB::raw("
        SUM(
            CASE
            WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) > 0
            AND DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) <= 100
            THEN 1 ELSE 0 END
        ) as late_count
        ")

    )
    ->first();

    $storeStatusStats = DB::table('stores')
    ->select(

        DB::raw('COUNT(*) as total'),

        DB::raw("
        SUM(
        CASE
        WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) < 0
        THEN 1 ELSE 0 END
        ) as safe
        "),

        DB::raw("
        SUM(
        CASE
        WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) = 0
        THEN 1 ELSE 0 END
        ) as today
        "),

        DB::raw("
        SUM(
        CASE
        WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) > 0
        AND DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) <= 100
        THEN 1 ELSE 0 END
        ) as late
        "),

        DB::raw("
        SUM(
        CASE
        WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) > 100
        AND DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) <= 135
        THEN 1 ELSE 0 END
        ) as heavy
        "),

        DB::raw("
        SUM(
        CASE
        WHEN DATEDIFF(CURDATE(), DATE_ADD(last_visit_date, INTERVAL visit_interval_days DAY)) > 135
        THEN 1 ELSE 0 END
        ) as withdraw
        ")

    )
    ->first();

    $totalStores = $storeStats->total_stores ?: 1;

$withdrawRate = ($storeStats->withdraw_count / $totalStores) * 100;
$heavyRate = ($storeStats->heavy_count / $totalStores) * 100;
$lateRate = ($storeStats->late_count / $totalStores) * 100;

$riskStatus = 'AMAN';
$riskColor  = 'green';

if ($withdrawRate > 5 || $heavyRate > 10) {
    $riskStatus = 'TERANCAM';
    $riskColor  = 'red';
} elseif ($lateRate > 20) {
    $riskStatus = 'WASPADA';
    $riskColor  = 'yellow';
}

        foreach ($salesData as $row) {

            $totalGenerated = (float)$row->total_konsinyasi + (float)$row->total_tunai;
            $totalPaid      = (float)$row->total_fee_paid;
            $kasbon         = (float)$row->kasbon_remaining;
            $rewardPaid     = (float)$row->total_reward_paid;
            $rewardPercent = 0;

if ($totalGenerated >= 5000000) {
    $rewardPercent = 12;
} elseif ($totalGenerated >= 3000000) {
    $rewardPercent = 10;
} elseif ($totalGenerated >= 1500000) {
    $rewardPercent = 7;
} elseif ($totalGenerated >= 500000) {
    $rewardPercent = 5;
}

$rewardStatus = 'valid';
$rewardNote = '';

if ($withdrawRate > 5) {
    $rewardStatus = 'gugur';
    $rewardNote = 'Toko withdraw > 5%';
}

if ($heavyRate > 10) {
    $rewardStatus = 'gugur';
    $rewardNote = 'Toko terlambat berat > 10%';
}

if ($lateRate > 20) {
    $rewardPercent *= 0.7;
    $rewardNote = 'Terlambat tinggi';
}

$rewardAmount = $rewardStatus === 'gugur'
    ? 0
    : ($totalGenerated * $rewardPercent / 100);

/*
|--------------------------------------------------------------------------
| CEK APAKAH REWARD BULAN INI SUDAH DI LOCK
|--------------------------------------------------------------------------
*/

$lockedReward = DB::table('sales_reward_months')
    ->where('user_id', $row->id)
    ->where('month', $month)
    ->where('year', $year)
    ->first();

if ($lockedReward) {

    // jika sudah di lock gunakan nilai dari tabel
    $rewardAmount = $lockedReward->reward_amount;

}

$rewardRemaining = $rewardAmount - $rewardPaid;

if ($rewardRemaining < 0) {
    $rewardRemaining = 0;
}

            $netFee = $totalGenerated - $totalPaid - $kasbon;

            $finalData[] = [
                'user_id' => $row->id,
                'name' => $row->name,
                'total_generated' => $totalGenerated,
                'total_paid' => $totalPaid,
                'kasbon_remaining' => $kasbon,
                'net_fee' => $netFee,
                'is_minus' => $netFee < 0 ? true : false,
                'reward_percent' => $rewardPercent,
                'reward_amount' => $rewardAmount,
                'reward_paid' => $rewardPaid,
                'reward_remaining' => $rewardRemaining,
                'reward_status' => $rewardStatus,
                'reward_note' => $rewardNote,
            ];
        }
        $dailyFee = DB::table(DB::raw("

(
    SELECT
        st.user_id,
        st.transaction_date as tanggal,
        st.total_fee as fee_konsinyasi,
        0 as fee_tunai
    FROM sales_transactions st
    WHERE st.transaction_date BETWEEN '{$startDate}' AND '{$endDate}'

    UNION ALL

    SELECT
        cs.user_id,
        cs.sale_date as tanggal,
        0 as fee_konsinyasi,
        cs.fee_total as fee_tunai
    FROM cash_sales cs
    WHERE cs.sale_date BETWEEN '{$startDate}' AND '{$endDate}'
    AND cs.status = 'locked'
)

as fees
"))

->join('users as u','u.id','=','fees.user_id')

->select(
    'u.name',
    'fees.tanggal',

    DB::raw('SUM(fees.fee_konsinyasi) as fee_konsinyasi'),
    DB::raw('SUM(fees.fee_tunai) as fee_tunai'),
    DB::raw('SUM(fees.fee_konsinyasi + fees.fee_tunai) as total_fee')
)

->groupBy('u.name','fees.tanggal')

->orderBy('fees.tanggal','desc')

->get()

->map(function($row){

    $row->total_fee = $row->fee_konsinyasi + $row->fee_tunai;

    return $row;
});

        /*
        |--------------------------------------------------------------------------
        | RINCIAN SETTLEMENT BULAN INI (STATUS CLOSED)
        |--------------------------------------------------------------------------
        | - Otomatis reset tiap bulan
        | - Sales hanya melihat miliknya
        | - Admin melihat semua
        */

        $settlementQuery = DB::table('sales_settlements')
            ->where('status', 'closed')
            ->whereMonth('settlement_date', $month)
            ->whereYear('settlement_date', $year)
            ->orderByDesc('settlement_date');

        if (auth()->user()->role === 'sales') {
            $settlementQuery->where('user_id', auth()->id());
        }

        $monthlySettlements = $settlementQuery->get()
            ->groupBy('user_id');

            $rewardLocked = DB::table('sales_reward_months')
            ->where('month',$month)
            ->where('year',$year)
            ->exists();

            $missionRewards = DB::table('mission_rewards as mr')
    ->join('users as u','u.id','=','mr.user_id')
    ->select(
        'u.name',
        'mr.mission_id',
        'mr.reward_amount',
        'mr.reward_date'
    )
    ->whereMonth('mr.reward_date',$month)
    ->whereYear('mr.reward_date',$year)
    ->orderByDesc('mr.reward_date')
    ->get();

    $totalRewards = collect($finalData)->map(function ($row) use ($month,$year) {

    // hitung reward dari misi
    $missionReward = DB::table('mission_rewards')
        ->where('user_id', $row['user_id'])
        ->whereMonth('reward_date', $month)
        ->whereYear('reward_date', $year)
        ->sum('reward_amount');

    // total reward KPI + misi
    $totalReward = $row['reward_amount'] + $missionReward;

    // hitung reward yang sudah dibayar
    $rewardPaid = DB::table('sales_reward_payments')
        ->where('user_id', $row['user_id'])
        ->where('month', $month)
        ->where('year', $year)
        ->sum('amount_paid');

    // hitung sisa reward
    $rewardRemaining = $totalReward - $rewardPaid;

    if ($rewardRemaining < 0) {
        $rewardRemaining = 0;
    }

    return [
        'user_id' => $row['user_id'],
        'name' => $row['name'],
        'kpi_reward' => $row['reward_amount'],
        'mission_reward' => $missionReward,
        'total_reward' => $totalReward,
        'reward_paid' => $rewardPaid,
        'reward_remaining' => $rewardRemaining
    ];

})->values();

$performance = AISalesPerformanceService::getPerformanceSummary(    
    1, // paksa ambil data Heri adi
    $month,
    $year

);

$insights = AISalesPerformanceService::generateInsights($performance);
// ==============================
// PRIORITY STORE BERDASARKAN KUNJUNGAN + POTENSI
// ==============================

$storesData = \DB::table('stores as s')
    ->leftJoin('sales_transactions as st','st.store_id','=','s.id')
    ->select(
        's.id',
        's.name',
        's.last_visit_date',
        \DB::raw('COALESCE(SUM(st.total_fee),0) as total_fee')
    )
    ->where('s.is_active',1)
    ->groupBy('s.id','s.name','s.last_visit_date')
    ->get();

$priorityHigh = [];
$priorityMedium = [];

foreach ($storesData as $store) {

    // hitung hari sejak kunjungan terakhir
    if ($store->last_visit_date) {
        $days = \Carbon\Carbon::parse($store->last_visit_date)->diffInDays(now());
    } else {
        $days = 999; // belum pernah dikunjungi
    }

    // hanya ambil toko overdue (>= 27 hari)
    if ($days >= 27 && $days <= 60) {

        // klasifikasi potensi
        if ($store->total_fee >= 300000) {
            $priorityHigh[] = $store->name;
        } else {
            $priorityMedium[] = $store->name;
        }

    }
}

// batasi jumlah
$priorityHigh = array_slice($priorityHigh,0,5);
$priorityMedium = array_slice($priorityMedium,0,5);

// kirim ke AI
$performance['priority_high'] = $priorityHigh;
$performance['priority_medium'] = $priorityMedium;

$aiSalesInsight = AIInsightService::generateSalesInsight($performance);

        return view('sales-fees.index', [
    'sales' => $finalData,
    'monthlySettlements' => $monthlySettlements,
    'dailyFee' => $dailyFee,
    'storeStatusStats' => $storeStatusStats,
    'riskStatus' => $riskStatus,
    'riskColor' => $riskColor,
    'month' => $month,
    'year'  => $year,
    'rewardLocked' => $rewardLocked,
    'missionRewards' => $missionRewards,
    'totalRewards' => $totalRewards,
    'performance' => $performance,
    'insights' => $insights,
    'aiSalesInsight' => $aiSalesInsight,
    ]);
    }

    public function pay(Request $request)
    {
        // Proteksi backend: hanya admin boleh bayar
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:1',
        ]);

        DB::table('sales_fee_payments')->insert([
            'user_id'    => $request->user_id,
            'amount_paid'=> $request->amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('sales-fees.index')
            ->with('success', 'Pembayaran fee berhasil disimpan.');
            
}

public function payReward(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'amount'  => 'required|numeric|min:1',
    ]);

    $month = request('month') ?? now()->month;
    $year  = request('year') ?? now()->year;

    // ambil reward yang sudah di lock
    $lockedReward = DB::table('sales_reward_months')
        ->where('user_id',$request->user_id)
        ->where('month',$month)
        ->where('year',$year)
        ->first();

    if (!$lockedReward) {
        return back()->with('error','Reward bulan ini belum di LOCK.');
    }

    $rewardAmount = $lockedReward->reward_amount;

    $rewardPaid = DB::table('sales_reward_payments')
    ->where('user_id',$request->user_id)
    ->where('month',$month)
    ->where('year',$year)
    ->sum('amount_paid');

    $rewardRemaining = $rewardAmount - $rewardPaid;

    if ($request->amount > $rewardRemaining) {
        return back()->with('error','Nominal melebihi sisa reward.');
    }

    DB::table('sales_reward_payments')->insert([
    'user_id' => $request->user_id,
    'month' => $month,
    'year' => $year,
    'amount_paid' => $request->amount,
    'created_at' => now(),
    'updated_at' => now(),
]);

    return redirect()
        ->route('sales-fees.index')
        ->with('success','Pembayaran reward berhasil disimpan.');
}

public function lockReward(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    $month = $request->month ?? Carbon::now()->month;
    $year  = $request->year ?? Carbon::now()->year;

    $startDate = Carbon::create($year,$month,1)->startOfMonth();
    $endDate   = Carbon::create($year,$month,1)->endOfMonth();

    $sales = DB::table('users')
        ->where('role','sales')
        ->get();

    foreach ($sales as $s) {

        $konsinyasi = DB::table('sales_transactions')
            ->where('user_id',$s->id)
            ->whereBetween('transaction_date',[$startDate,$endDate])
            ->sum('total_fee');

        $tunai = DB::table('cash_sales')
            ->where('user_id',$s->id)
            ->where('status','locked')
            ->whereBetween('sale_date',[$startDate,$endDate])
            ->sum('fee_total');

        $totalGenerated = $konsinyasi + $tunai;

        $rewardPercent = 0;

        if ($totalGenerated >= 5000000) {
            $rewardPercent = 12;
        } elseif ($totalGenerated >= 3000000) {
            $rewardPercent = 10;
        } elseif ($totalGenerated >= 1500000) {
            $rewardPercent = 7;
        } elseif ($totalGenerated >= 500000) {
            $rewardPercent = 5;
        }

        $rewardAmount = $totalGenerated * $rewardPercent / 100;

        DB::table('sales_reward_months')->updateOrInsert(
            [
                'user_id' => $s->id,
                'month'   => $month,
                'year'    => $year
            ],
            [
                'reward_amount' => $rewardAmount,
                'locked_at'     => now(),
                'updated_at'    => now(),
                'created_at'    => now()
            ]
        );
    }

    return redirect()
        ->route('sales-fees.index')
        ->with('success','Reward bulan ini berhasil di LOCK.');
}

}
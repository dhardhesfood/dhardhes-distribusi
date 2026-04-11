<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ProductionService;
use App\Models\WorkerWithdrawal;
use Illuminate\Support\Facades\DB;

class ProductionRunController extends Controller
{
    protected $service;

    public function __construct(ProductionService $service)
    {
        $this->service = $service;
    }

    public function index()
{
    $products = Product::where('is_active', true)->get();

    $selectedMonth = request('month', now()->month);
$selectedYear  = request('year', now()->year);

$runs = \App\Models\ProductionRun::with('product')
    ->whereMonth('created_at', $selectedMonth)
    ->whereYear('created_at', $selectedYear)
    ->latest()
    ->get();

$totalUpah = $runs->sum('total_labor_cost');

$totalPencairan = WorkerWithdrawal::where('status', 'approved')
    ->whereMonth('withdraw_date', $selectedMonth)
    ->whereYear('withdraw_date', $selectedYear)
    ->sum('approved_amount');

$sisa = $totalUpah - $totalPencairan;

// 🔥 filter history juga ikut bulan
$withdrawals = WorkerWithdrawal::whereMonth('withdraw_date', $selectedMonth)
    ->whereYear('withdraw_date', $selectedYear)
    ->latest()
    ->get();

    // =========================
// 🔥 REWARD LOGIC
// =========================
$totalGram = $runs->sum('output_gram');

// cek apakah sudah di-lock
$rewardData = DB::table('production_rewards')
    ->where('month', $selectedMonth)
    ->where('year', $selectedYear)
    ->first();

if ($rewardData && $rewardData->is_locked) {

    // 🔒 pakai data yang sudah dikunci
    $rewardAmount = $rewardData->reward_amount;
    $isLocked = true;
    $isPaid = $rewardData->is_paid;

} else {

    // 🔥 hitung realtime (REAL MODE)
    if ($totalGram >= 1600000) {
        $rewardAmount = 250000;
    } elseif ($totalGram >= 1300000) {
        $rewardAmount = 150000;
    } elseif ($totalGram >= 1000000) {
        $rewardAmount = 100000;
    } elseif ($totalGram >= 800000) {
        $rewardAmount = 50000;
    } else {
        $rewardAmount = 0;
    }

    $isLocked = false;
    $isPaid = false;
}

    return view('production_run.index', compact(
        'products',
        'runs',
        'totalUpah',
        'totalPencairan',
        'sisa',
        'withdrawals',
        'selectedMonth',
        'selectedYear',
        'totalGram',
        'rewardAmount',
        'isLocked',
        'isPaid'
    ));
}

    public function preview(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required',
            'output_gram' => 'required|numeric|min:1',
            'labor_percentage' => 'required|numeric|min:0',
        ]);

        $outputGram = $data['output_gram'];

        $result = $this->service->calculate(
            $data['product_id'],
            $outputGram,
            $data['labor_percentage']
        );

        // 🔥 hanya kirim UPAH
        $preview = [
            'total_labor_cost' => $result['total_labor_cost']
        ];

        return back()->with('preview', $preview)->withInput();
    }

    public function store(Request $request)
    {
 
        $data = $request->validate([
    'product_id' => 'required',
    'output_gram' => 'required|numeric|min:1',
    'labor_percentage' => 'required|numeric|min:0',
    'photo' => 'required|file|max:5120',
]);

$outputGram = $data['output_gram'];

// 🔥 upload foto
$photoPath = null;

if ($request->hasFile('photo')) {
    $photoPath = $request->file('photo')->store('production_photos', 'public');
}

$this->service->store(
    $data['product_id'],
    $outputGram,
    $data['labor_percentage'],
    auth()->id() ?? 1,
    $photoPath
);

        return redirect()->route('production-run.index')
            ->with('success', 'Data produksi berhasil disimpan');
    }

    public function withdraw(Request $request)
{
    $data = $request->validate([
        'amount' => 'required|numeric|min:1'
    ]);

    WorkerWithdrawal::create([
        'requested_amount' => $data['amount'],
        'withdraw_date' => now(),
        'created_by' => auth()->id(),
        'status' => 'pending'
    ]);

    return back()->with('success', 'Pengajuan pencairan dikirim, menunggu approval admin');
}

public function approve(Request $request, $id)
{
    $data = $request->validate([
        'approved_amount' => 'required|numeric|min:1'
    ]);

    $withdraw = WorkerWithdrawal::findOrFail($id);

    $totalUpah = \App\Models\ProductionRun::sum('total_labor_cost');

    $totalPencairan = WorkerWithdrawal::where('status', 'approved')
        ->sum('approved_amount');

    $sisa = $totalUpah - $totalPencairan;

    if ($data['approved_amount'] > $sisa) {
        return back()->with('error', 'Melebihi sisa upah!');
    }

    $withdraw->update([
        'approved_amount' => $data['approved_amount'],
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => auth()->id()
    ]);

    return back()->with('success', 'Pencairan disetujui');
}
public function edit($id)
{
    $run = \App\Models\ProductionRun::findOrFail($id);
    $products = Product::where('is_active', true)->get();

    return view('production_run.edit', compact('run','products'));
}

public function update(Request $request, $id)
{
    $data = $request->validate([
        'product_id' => 'required',
        'output_gram' => 'required|numeric|min:1',
        'labor_percentage' => 'required|numeric|min:0',
    ]);

    $run = \App\Models\ProductionRun::findOrFail($id);

    // 🔥 FIX DI SINI
    $percentage = $data['labor_percentage'] / 100;

    $result = $this->service->calculate(
        $data['product_id'],
        $data['output_gram'],
        $percentage
    );

    $run->update([
        'product_id' => $data['product_id'],
        'output_gram' => $data['output_gram'],
        'labor_percentage' => $percentage,
        'total_labor_cost' => $result['total_labor_cost'],
    ]);

    return redirect()->route('production-run.index')
        ->with('success','Data berhasil diupdate');
}

public function destroy($id)
{
    $run = \App\Models\ProductionRun::findOrFail($id);
    $run->delete();

    return back()->with('success','Data berhasil dihapus');
}

public function lockReward(Request $request)
{
    $month = (int) $request->month;
    $year  = (int) $request->year;

    // 🔒 cek sudah ada & sudah lock
    $existing = DB::table('production_rewards')
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    if ($existing && $existing->is_locked) {
        return back()->with('error', 'Reward bulan ini sudah dikunci.');
    }

    // 🔥 hitung total produksi (pakai created_at sesuai keputusan)
    $totalGram = \App\Models\ProductionRun::whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->sum('output_gram');

    // 🔥 hitung reward (REAL MODE)
    if ($totalGram >= 1600000) {
        $rewardAmount = 250000;
    } elseif ($totalGram >= 1300000) {
        $rewardAmount = 150000;
    } elseif ($totalGram >= 1000000) {
        $rewardAmount = 100000;
    } elseif ($totalGram >= 800000) {
        $rewardAmount = 50000;
    } else {
        $rewardAmount = 0;
    }

    // 🔥 simpan / update
    DB::table('production_rewards')->updateOrInsert(
        [
            'month' => $month,
            'year'  => $year,
        ],
        [
            'total_gram'    => $totalGram,
            'reward_amount' => $rewardAmount,
            'is_locked'     => true,
            'locked_at'     => now(),
            'updated_at'    => now(),
            'created_at'    => now(),
        ]
    );

    return back()->with('success', 'Reward berhasil dikunci.');
}

public function payReward(Request $request)
{
    $month = (int) $request->month;
    $year  = (int) $request->year;

    $reward = DB::table('production_rewards')
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    if (!$reward || !$reward->is_locked) {
        return back()->with('error', 'Reward belum dikunci.');
    }

    if ($reward->is_paid) {
        return back()->with('error', 'Reward sudah dibayar.');
    }

    DB::table('production_rewards')
        ->where('month', $month)
        ->where('year', $year)
        ->update([
            'is_paid' => true,
            'paid_at' => now(),
            'updated_at' => now()
        ]);

    return back()->with('success', 'Reward berhasil ditandai sudah dibayar.');
}

public function unlockReward(Request $request)
{
    $month = (int) $request->month;
    $year  = (int) $request->year;

    $reward = DB::table('production_rewards')
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    if (!$reward) {
        return back()->with('error', 'Data reward tidak ditemukan.');
    }

    DB::table('production_rewards')
        ->where('month', $month)
        ->where('year', $year)
        ->update([
    'is_locked' => false,
    'locked_at' => null,

    // 🔥 RESET PEMBAYARAN
    'is_paid' => false,
    'paid_at' => null,

    'updated_at' => now()
]);

    return back()->with('success', 'Reward berhasil dibuka kembali.');
}

}
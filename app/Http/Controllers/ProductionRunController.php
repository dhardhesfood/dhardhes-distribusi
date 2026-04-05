<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ProductionService;
use App\Models\WorkerWithdrawal;

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

    return view('production_run.index', compact(
        'products',
        'runs',
        'totalUpah',
        'totalPencairan',
        'sisa',
        'withdrawals',
        'selectedMonth',
        'selectedYear'
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

}
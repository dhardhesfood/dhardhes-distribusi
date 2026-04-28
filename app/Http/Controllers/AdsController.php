<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdsController extends Controller
{
    public function create()
    {
        return view('ads.create');
    }

    public function store(Request $request)
{
    // ======================
    // VALIDASI
    // ======================
    $request->validate([
        'report_date' => 'required|date',
        'budget' => 'required|numeric',
        'tayangan_konten' => 'required|numeric',
        'klik_tautan' => 'required|numeric',
        'hasil' => 'required|numeric',
    ]);

    $budget = str_replace('.', '', $request->budget);
    $budgetWithTax = round($budget * 1.11);

    // ======================
    // CEK SUDAH ADA DATA HARI INI?
    // ======================
    $existing = DB::table('ads_reports')
        ->whereDate('report_date', $request->report_date)
        ->first();

    if ($existing) {

        // ======================
        // UPDATE (JANGAN SENTUH real_chat)
        // ======================
        DB::table('ads_reports')
            ->where('id', $existing->id)
            ->update([
                'budget' => $budgetWithTax,
                'tayangan_konten' => $request->tayangan_konten,
                'klik_tautan' => $request->klik_tautan,
                'hasil' => $request->hasil,
                'platform' => 'facebook',
                'updated_at' => now(),
            ]);

    } else {

        // ======================
        // INSERT BARU
        // ======================
        DB::table('ads_reports')->insert([
            'report_date' => $request->report_date,
            'budget' => $budgetWithTax,
            'tayangan_konten' => $request->tayangan_konten,
            'klik_tautan' => $request->klik_tautan,
            'hasil' => $request->hasil,
            'real_chat' => 0,
            'closing' => 0,
            'platform' => 'facebook',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }

    return back()->with('success', 'Data iklan berhasil disimpan');
}

public function index()
{
    $query = DB::table('ads_reports');

    // ======================
    // FILTER
    // ======================
    if(request('filter') == 'today'){
        $query->whereDate('report_date', now());
    }
    elseif(request('filter') == '7days'){
        $query->where('report_date', '>=', now()->subDays(7));
    }
    elseif(request('filter') == '30days'){
        $query->where('report_date', '>=', now()->subDays(30));
    }

    // ======================
    // GET DATA
    // ======================
    $ads = $query->orderByDesc('report_date')->get();

    // ======================
    // SUMMARY AGREGAT
    // ======================
    $summary = [
        'budget' => $ads->sum('budget'),
        'klik' => $ads->sum('klik_tautan'),
        'landing' => $ads->sum('tayangan_konten'),
        'wa' => $ads->sum('hasil'),
        'chat' => $ads->sum('real_chat'),
        'closing' => $ads->sum('closing'),
    ];

    // ======================
// HITUNG RATIO AGREGAT
// ======================

$summary['rate_lp'] = $summary['klik'] > 0 
    ? ($summary['landing'] / $summary['klik']) * 100 
    : 0;

$summary['rate_wa'] = $summary['landing'] > 0 
    ? ($summary['wa'] / $summary['landing']) * 100 
    : 0;

$summary['rate_chat'] = $summary['wa'] > 0 
    ? ($summary['chat'] / $summary['wa']) * 100 
    : 0;

$summary['rate_closing'] = $summary['chat'] > 0 
    ? ($summary['closing'] / $summary['chat']) * 100 
    : 0;

// ======================
// ANALISA AGREGAT (PAKAI RATIO)
// ======================

$analysis = [];

// ======================
// STATUS PER KPI (UNTUK UI)
// ======================

$status = [];

// Klik → LP
if ($summary['rate_lp'] < 60) {
    $status['lp'] = "⚠️ Perlu perbaikan (LP lambat / link error)";
} else {
    $status['lp'] = "✅ Aman";
}

// LP → WA
if ($summary['rate_wa'] < 30) {
    $status['wa'] = "⚠️ Perlu perbaikan (CTA / copy lemah)";
} else {
    $status['wa'] = "✅ Aman";
}

// WA → Chat
if ($summary['rate_chat'] < 70) {
    $status['chat'] = "⚠️ Perlu perbaikan (trust / respon lambat)";
} else {
    $status['chat'] = "✅ Aman";
}

// Chat → Closing
if ($summary['rate_closing'] < 10) {
    $status['closing'] = "⚠️ Perlu perbaikan (skill closing)";
} else {
    $status['closing'] = "✅ Aman";
}

// LP Rate
if ($summary['rate_lp'] < 60) {
    $analysis[] = "⚠️ Klik → LP rendah (" . round($summary['rate_lp'],1) . "%) → kemungkinan link lambat / LP berat / tracking error";
}

// WA Rate
if ($summary['rate_wa'] < 30) {
    $analysis[] = "⚠️ LP → WA rendah (" . round($summary['rate_wa'],1) . "%) → CTA lemah / copy tidak meyakinkan";
}

// Chat Rate
if ($summary['rate_chat'] < 70) {
    $analysis[] = "⚠️ WA → Chat rendah (" . round($summary['rate_chat'],1) . "%) → trust issue / auto text jelek / respon lambat";
}

if ($summary['rate_chat'] > 110) {
    $analysis[] = "⚠️ Chat > WA klik → indikasi double tracking (data tidak valid)";
}

// Closing Rate
if ($summary['rate_closing'] < 10) {
    $analysis[] = "⚠️ Chat → Closing rendah (" . round($summary['rate_closing'],1) . "%) → problem di sales / closing skill";
}

// Cost
$costPerClosing = $summary['closing'] > 0 
    ? $summary['budget'] / $summary['closing'] 
    : 0;

if ($costPerClosing > 50000) {
    $analysis[] = "⚠️ Cost per closing tinggi (Rp " . number_format($costPerClosing,0,',','.') . ") → iklan belum efisien";
}

// ======================
// CEK SEMUA KPI AMAN
// ======================

$allGood =
    $summary['rate_lp'] >= 60 &&
    $summary['rate_wa'] >= 30 &&
    $summary['rate_chat'] >= 70 &&
    $summary['rate_closing'] >= 10 &&
    $costPerClosing <= 50000;

if ($allGood) {
    $analysis = ["✅ Funnel sehat, siap scaling budget"];
}

    // ======================
    // KPI AGREGAT
    // ======================
    $summary['closing_rate'] = $summary['chat'] > 0 
        ? ($summary['closing'] / $summary['chat']) * 100 
        : 0;

    $summary['cost_per_closing'] = $summary['closing'] > 0 
        ? $summary['budget'] / $summary['closing'] 
        : 0;

    return view('ads.index', compact('ads','summary','analysis','status'));
}

public function updateReal(Request $request, $id)
{
    DB::table('ads_reports')->where('id', $id)->update([
        'closing' => $request->closing ?? 0,
        'updated_at' => now()
    ]);

    return back()->with('success', 'Closing berhasil diupdate');
}

public function updateDaily(Request $request, $id)
{
    // ======================
    // VALIDASI
    // ======================
    $request->validate([
        'budget' => 'required|numeric',
        'tayangan_konten' => 'required|numeric',
        'klik_tautan' => 'required|numeric',
        'hasil' => 'required|numeric',
    ]);

    $budget = str_replace('.', '', $request->budget);
    $budgetWithTax = round($budget * 1.11);

    // ======================
    // UPDATE TANPA SENTUH real_chat
    // ======================
    DB::table('ads_reports')
        ->where('id', $id)
        ->update([
            'budget' => $budgetWithTax,
            'tayangan_konten' => $request->tayangan_konten,
            'klik_tautan' => $request->klik_tautan,
            'hasil' => $request->hasil,
            'updated_at' => now(),
        ]);

    return back()->with('success', 'Data iklan berhasil diupdate');
}

}
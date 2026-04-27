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

    DB::table('ads_reports')->insert([
        'report_date' => $request->report_date,
        'budget' => $budgetWithTax,
        'tayangan_konten' => $request->tayangan_konten,
        'klik_tautan' => $request->klik_tautan,
        'hasil' => $request->hasil,

        // manual (belum diisi)
        'real_chat' => 0,
        'closing' => 0,

        'platform' => 'facebook',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

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
    // KPI AGREGAT
    // ======================
    $summary['closing_rate'] = $summary['chat'] > 0 
        ? ($summary['closing'] / $summary['chat']) * 100 
        : 0;

    $summary['cost_per_closing'] = $summary['closing'] > 0 
        ? $summary['budget'] / $summary['closing'] 
        : 0;

    return view('ads.index', compact('ads','summary'));
}

public function updateReal(Request $request, $id)
{
    DB::table('ads_reports')->where('id', $id)->update([
        'real_chat' => $request->real_chat ?? 0,
        'closing' => $request->closing ?? 0,
        'updated_at' => now()
    ]);

    return back()->with('success', 'Data real berhasil diupdate');
}


}
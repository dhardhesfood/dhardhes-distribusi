<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MissionService;

class MissionController extends Controller
{
    /**
     * Menampilkan daftar misi
     */
    public function index()
    {
        $missions = DB::table('missions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('missions.index', compact('missions'));
    }

    /**
     * Form tambah misi
     */
    public function create()
    {
        $products = DB::table('products')
            ->orderBy('name')
            ->get();

        return view('missions.create', compact('products'));
    }

    /**
     * Simpan misi baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'target' => 'required|integer|min:1',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        DB::table('missions')->insert([
            'title' => $request->title,
            'type' => $request->type,
            'product_id' => $request->product_id,
            'target' => $request->target,
            'reward_amount' => $request->reward_amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'active' => $request->has('active') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // rebuild mission progress untuk semua sales
        $salesUsers = DB::table('users')
            ->where('role','sales')
            ->pluck('id');

       foreach ($salesUsers as $salesId) {
        MissionService::handleSalesOmzet($salesId);
}

       return redirect()->route('missions.index')
           ->with('success', 'Misi berhasil dibuat.');
    }

/**
 * Form edit misi
 */
public function edit($id)
{
    $mission = DB::table('missions')
        ->where('id', $id)
        ->first();

    $products = DB::table('products')
        ->orderBy('name')
        ->get();

    return view('missions.edit', compact('mission','products'));
}

/**
 * Update misi
 */
public function update(Request $request, $id)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'type' => 'required|string',
        'target' => 'required|integer|min:1',
        'reward_amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date'
    ]);

    DB::table('missions')
        ->where('id', $id)
        ->update([
            'title' => $request->title,
            'type' => $request->type,
            'product_id' => $request->product_id,
            'target' => $request->target,
            'reward_amount' => $request->reward_amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'active' => $request->has('active') ? 1 : 0,
            'updated_at' => now()
        ]);

    // rebuild mission progress untuk semua sales
$salesUsers = DB::table('users')
    ->where('role','sales')
    ->pluck('id');

foreach ($salesUsers as $salesId) {
    MissionService::handleSalesOmzet($salesId);
}

return redirect()->route('missions.index')
    ->with('success', 'Misi berhasil diupdate.');
}

/**
 * Hapus misi
 */
public function destroy($id)
{
    // hapus progress mission
    DB::table('mission_progress')
        ->where('mission_id', $id)
        ->delete();

    // hapus reward mission
    DB::table('mission_rewards')
        ->where('mission_id', $id)
        ->delete();

    // hapus mission
    DB::table('missions')
        ->where('id', $id)
        ->delete();

    return redirect()->route('missions.index')
        ->with('success', 'Misi berhasil dihapus.');
}

}
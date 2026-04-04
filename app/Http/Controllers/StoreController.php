<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Area;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        // 🔥 WAJIB pilih sales dulu untuk admin (berdasarkan session, bukan query)
        if (auth()->user()->role === 'admin' && !session()->has('active_sales_id')) {
            return redirect()->route('visits.choose_sales');
        }

        $areas = Area::withCount('stores')->get();

        $stores = Store::with('area')
            ->when($request->area_id, function ($query) use ($request) {
                $query->where('area_id', $request->area_id);
            })
            ->get();

        $stores = $stores->sortBy(function ($store) {
            return match($store->visit_status) {
                'withdraw' => 1,
                'heavy' => 2,
                'late' => 3,
                'today' => 4,
                'safe' => 5,
                default => 6
            };
        })->values();

        return view('stores.index', compact('stores', 'areas'));
    }

    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        // Validasi
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'phone' => ['nullable','regex:/^62[0-9]{8,15}$/'],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'visit_interval_days' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        // Update data
        $store->update([
            'area_id' => $validated['area_id'],
            'name' => $validated['name'],
            'owner_name' => $validated['owner_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'visit_interval_days' => $validated['visit_interval_days'],
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('stores.index')
            ->with('success', 'Data toko berhasil diperbarui');
    }
}
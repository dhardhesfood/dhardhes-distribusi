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

    });

        return view('stores.index', compact('stores', 'areas'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Area;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        // 🔥 WAJIB pilih sales dulu untuk admin
        if (auth()->user()->role === 'admin' && !$request->has('sales_id')) {
            return redirect()->route('visits.choose_sales');
        }

        $areas = Area::withCount('stores')->get();

        $stores = Store::with('area')
            ->when($request->area_id, function ($query) use ($request) {
                $query->where('area_id', $request->area_id);
            })
            ->get();

        return view('stores.index', compact('stores', 'areas'));
    }
}
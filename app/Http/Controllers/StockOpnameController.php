<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOpname;
use App\Models\Store;
use App\Models\Product;
use App\Services\StockOpnameService;

class StockOpnameController extends Controller
{
    protected $service;

    public function __construct(StockOpnameService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | FORM OPNAME
    |--------------------------------------------------------------------------
    */

    public function create(Store $store)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $products = Product::orderBy('name')->get();

        return view('stock_opnames.create', compact('store','products'));
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN OPNAME
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, Store $store)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'actual_stock' => 'required|array',
            'actual_stock.*' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $opname = $this->service->process(
            $store->id,
            auth()->id(),
            $request->notes,
            $request->actual_stock
        );

        return redirect()
            ->route('stock-opnames.show', $opname->id)
            ->with('success','Stock opname berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL OPNAME (TIDAK BISA EDIT / DELETE)
    |--------------------------------------------------------------------------
    */

    public function show(StockOpname $stockOpname)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $stockOpname->load('store','items.product','creator');

        return view('stock_opnames.show', compact('stockOpname'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOpname;
use App\Models\Store;
use App\Models\Product;
use App\Models\StoreStockMovement;
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

    // ambil semua produk
    $products = Product::orderBy('name')->get();

    // cek stok toko per produk
    $products = $products->map(function ($product) use ($store) {

        $stock = StoreStockMovement::getStoreProductStock(
            $store->id,
            $product->id
        );

        $product->store_stock = $stock;

        return $product;
    });

    // urutkan: stok terbesar di atas
    $products = $products->sortByDesc('store_stock');

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
        'visit_date' => 'required|date',
        'actual_stock' => 'required|array',
        'actual_stock.*' => 'required|integer|min:0',
        'notes' => 'nullable|string'
       ]);

        $visitDate = $request->visit_date;

        $opname = $this->service->process(
        $store->id,
        auth()->id(),
        $request->notes,
        $request->actual_stock,
        $request->visit_date
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
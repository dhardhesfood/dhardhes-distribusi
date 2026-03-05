<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\StockMovement;

class ProductionController extends Controller
{
    /**
     * Form input produksi
     */
    public function create()
    {
        $products = Product::where('is_active', true)->get();

        return view('productions.create', compact('products'));
    }

    /**
     * Simpan produksi
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'production_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {

            // 1. Simpan data produksi
            $production = ProductionBatch::create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'production_date' => $request->production_date,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // 2. Tambah stok gudang (ledger)
            StockMovement::create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'type' => 'warehouse_in',
                'reference_id' => $production->id,
                'reference_type' => 'production_batch',
                'session_id' => null,
                'notes' => 'Produksi batch #' . $production->id,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Produksi berhasil disimpan dan stok gudang bertambah.');
    }
}
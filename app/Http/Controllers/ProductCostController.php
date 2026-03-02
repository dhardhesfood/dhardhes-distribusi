<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCostHistory;

class ProductCostController extends Controller
{
    public function index(Product $product)
    {
        $costs = ProductCostHistory::where('product_id', $product->id)
            ->orderByDesc('effective_date')
            ->get();

        return view('products.costs', [
            'product' => $product,
            'costs'   => $costs
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'cost'           => 'required|numeric|min:0',
            'effective_date' => 'required|date'
        ]);

        // Tidak boleh ada duplicate effective_date untuk produk yang sama
        $exists = ProductCostHistory::where('product_id', $product->id)
            ->where('effective_date', $request->effective_date)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'effective_date' => 'Tanggal ini sudah memiliki HPP.'
            ]);
        }

        ProductCostHistory::create([
            'product_id'     => $product->id,
            'cost'           => $request->cost,
            'effective_date' => $request->effective_date,
            'created_by'     => auth()->id(),
        ]);

        return redirect()
            ->route('products.costs.index', $product->id)
            ->with('success', 'HPP berhasil ditambahkan.');
    }
}

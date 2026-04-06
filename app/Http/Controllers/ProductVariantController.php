<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{


    public function index(Request $request)
    
{
    $products = Product::where('is_active', true)->get();

    $selectedProductId = $request->product_id;

    $variants = [];

    if ($selectedProductId) {
        $variants = ProductVariant::where('product_id', $selectedProductId)
            ->orderBy('name')
            ->get();
    }

    return view('product-variants.index', compact(
        'products',
        'variants',
        'selectedProductId'
    ));
}

public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'name' => 'required|string|max:255',
    ]);

    $exists = ProductVariant::where('product_id', $request->product_id)
    ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
    ->exists();

    if ($exists) {
    return back()->with('error', 'Varian sudah ada');
}

    ProductVariant::create([
        'product_id' => $request->product_id,
        'name' => $request->name,
        'is_active' => true,
    ]);

    return back()->with('success', 'Varian berhasil ditambahkan');
}

public function destroy($id)
{
    $variant = ProductVariant::findOrFail($id);

    $variant->update([
        'is_active' => false
    ]);

    return back()->with('success', 'Varian dinonaktifkan');
}
}

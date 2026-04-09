<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'sku'                     => 'required|string|max:255|unique:products,sku',
            'default_selling_price'   => 'required|numeric|min:0',
            'default_fee_nominal'     => 'required|numeric|min:0',
            'warehouse_price'         => 'required|numeric|min:0',
            'channel_type' => 'required|in:offline,online',
        ]);

        Product::create([
            'name'                    => $validated['name'],
            'sku'                     => $validated['sku'],
            'default_selling_price'   => $validated['default_selling_price'],
            'default_fee_nominal'     => $validated['default_fee_nominal'],
            'warehouse_price'         => $validated['warehouse_price'],
            'unit'                    => 'Pcs',
            'is_active'               => 1,
            'channel_type' => $validated['channel_type'],
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'sku'                     => 'required|string|max:255|unique:products,sku,' . $product->id,
            'default_selling_price'   => 'required|numeric|min:0',
            'default_fee_nominal'     => 'required|numeric|min:0',
            'warehouse_price'         => 'required|numeric|min:0',
            'is_active'               => 'nullable|boolean',
            'channel_type' => 'required|in:offline,online',
        ]);

        $product->update([
            'name'                    => $validated['name'],
            'sku'                     => $validated['sku'],
            'default_selling_price'   => $validated['default_selling_price'],
            'default_fee_nominal'     => $validated['default_fee_nominal'],
            'warehouse_price'         => $validated['warehouse_price'],
            'is_active'               => $request->boolean('is_active'),
            'channel_type' => 'required|in:offline,online',
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
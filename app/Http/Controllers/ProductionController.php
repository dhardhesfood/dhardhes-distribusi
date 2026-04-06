<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\StockMovement;
use App\Models\ProductionBatchItem;
use App\Models\ProductVariant;

class ProductionController extends Controller
{
    /**
     * Form input produksi
     */
    public function create(Request $request)
    {
        $products = Product::where('is_active', true)->get();

        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $productions = ProductionBatch::with([
                'product',
                'items.variant'
            ])
            ->whereMonth('production_date', $month)
            ->whereYear('production_date', $year)
            ->orderBy('production_date', 'desc')
            ->get();

        return view('productions.create', compact('products', 'productions', 'month', 'year'));
    }

    /**
     * Simpan produksi
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'production_date' => 'required|date',
            'notes' => 'nullable|string',

            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.qty' => 'nullable|integer|min:1',
        ]);

try {

        DB::transaction(function () use ($request) {

            $filteredVariants = collect($request->variants)
                ->filter(function ($v) {
                    return isset($v['qty']) && $v['qty'] > 0;
                })
                ->values();

            if ($filteredVariants->isEmpty()) {
                throw new \Exception('Minimal pilih 1 varian dan isi jumlah');
            }

            // 🔥 VALIDASI KEMASAN
foreach ($filteredVariants as $variant) {

    $stock = DB::table('packaging_stocks')
        ->where('product_id', $request->product_id)
        ->where('product_variant_id', $variant['id'])
        ->value('stock_qty');

    $available = $stock ?? 0;
    $needed = $variant['qty'];

    if ($available < $needed) {

        $variantName = ProductVariant::find($variant['id'])->name;

        throw new \Exception(
            "Kemasan {$variantName} tidak cukup (butuh {$needed}, tersedia {$available})"
        );
    }
}

            $totalQty = $filteredVariants->sum('qty');

            // 1. Simpan data produksi
            $production = ProductionBatch::create([
                'product_id' => $request->product_id,
                'quantity' => $totalQty,
                'production_date' => $request->production_date,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            foreach ($filteredVariants as $variant) {
                ProductionBatchItem::create([
                    'production_batch_id' => $production->id,
                    'product_variant_id' => $variant['id'],
                    'quantity' => $variant['qty'],
                ]);
            }

            // 2. Tambah stok gudang (ledger)
            StockMovement::create([
                'product_id' => $request->product_id,
                'quantity' => $totalQty,
                'type' => 'warehouse_in',
                'reference_id' => $production->id,
                'reference_type' => 'production_batch',
                'session_id' => null,
                'notes' => 'Produksi batch #' . $production->id,
                'created_by' => auth()->id(),
            ]);

            // 🔥 KURANGI KEMASAN
foreach ($filteredVariants as $variant) {

    DB::table('packaging_movements')->insert([
        'product_id' => $request->product_id,
        'product_variant_id' => $variant['id'],
        'type' => 'out',
        'quantity' => $variant['qty'],
        'reference_id' => $production->id,
        'reference_type' => 'production_batch',
        'created_by' => auth()->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('packaging_stocks')
        ->where('product_id', $request->product_id)
        ->where('product_variant_id', $variant['id'])
        ->decrement('stock_qty', $variant['qty']);
}
        });

        } catch (\Exception $e) {

    return back()->with('error', $e->getMessage());
}

        return redirect()->back()->with('success', 'Produksi berhasil disimpan dan stok gudang bertambah.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        DB::transaction(function () use ($id) {

            $production = ProductionBatch::findOrFail($id);

            $items = ProductionBatchItem::where('production_batch_id', $production->id)->get();

            // 1. BALIKKAN STOK (WAJIB)
            StockMovement::create([
                'product_id' => $production->product_id,
                'quantity' => $production->quantity,
                'type' => 'warehouse_out',
                'reference_id' => $production->id,
                'reference_type' => 'production_delete',
                'session_id' => null,
                'notes' => 'Hapus produksi #' . $production->id,
                'created_by' => auth()->id(),
            ]);

            // 🔥 KEMBALIKAN KEMASAN
foreach ($items as $item) {

    DB::table('packaging_movements')->insert([
        'product_id' => $production->product_id,
        'product_variant_id' => $item->product_variant_id,
        'type' => 'return',
        'quantity' => $item->quantity,
        'reference_id' => $production->id,
        'reference_type' => 'production_delete',
        'created_by' => auth()->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('packaging_stocks')
        ->where('product_id', $production->product_id)
        ->where('product_variant_id', $item->product_variant_id)
        ->increment('stock_qty', $item->quantity);
}

            // 2. HAPUS DETAIL VARIAN
            ProductionBatchItem::where('production_batch_id', $production->id)->delete();

            // 3. HAPUS HEADER
            $production->delete();
        });

        return back()->with('success', 'Produksi berhasil dihapus & stok dikembalikan');
    }
}
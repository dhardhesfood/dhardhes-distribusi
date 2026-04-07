<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductVariant;

class PackagingController extends Controller
{
    public function index()
{
    $products = Product::where('is_active', true)->get();
    $month = request('month');
    $year = request('year') ?? now()->year;

    // 🔥 AMBIL STOK KEMASAN
    $stocks = DB::table('packaging_stocks')
        ->join('products', 'products.id', '=', 'packaging_stocks.product_id')
        ->join('product_variants', 'product_variants.id', '=', 'packaging_stocks.product_variant_id')
        ->select(
    'products.name as product_name',
    'product_variants.name as variant_name',
    'packaging_stocks.stock_qty',
    'packaging_stocks.product_id',
    'packaging_stocks.product_variant_id'
)
        ->orderBy('products.name')
        ->get();

        $histories = DB::table('packaging_movements')
    ->join('products', 'products.id', '=', 'packaging_movements.product_id')
    ->join('product_variants', 'product_variants.id', '=', 'packaging_movements.product_variant_id')
    ->select(
        'packaging_movements.created_at',
        'products.name as product_name',
        'product_variants.name as variant_name',
        'packaging_movements.type',
        'packaging_movements.quantity',
        'packaging_movements.reference_type'
    )
    ->when($month, function ($q) use ($month, $year) {
    $q->whereMonth('packaging_movements.created_at', $month)
      ->whereYear('packaging_movements.created_at', $year);
    })
    ->orderBy('packaging_movements.created_at', 'desc')
    ->limit(50)
    ->get();

    $daily = DB::table('packaging_movements')
    ->join('products', 'products.id', '=', 'packaging_movements.product_id')
    ->join('product_variants', 'product_variants.id', '=', 'packaging_movements.product_variant_id')
    ->where('packaging_movements.type', 'in')
    ->where('packaging_movements.reference_type', 'packaging_input')

    ->select(
        DB::raw('DATE(packaging_movements.created_at) as tanggal'),
        'products.name as product_name',
        'product_variants.name as variant_name',
        DB::raw("SUM(quantity) as total_qty")
    )
     ->when($month, function ($q) use ($month, $year) {
    $q->whereMonth('packaging_movements.created_at', $month)
      ->whereYear('packaging_movements.created_at', $year);
     })
    ->groupBy('tanggal','products.name','product_variants.name')
    ->orderBy('tanggal','desc')
    ->get();

    return view('packaging.index', compact('products', 'stocks', 'histories', 'daily'));
}

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'tanggal' => 'required|date',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.qty' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $filtered = collect($request->variants)
                ->filter(fn($v) => isset($v['qty']) && $v['qty'] > 0)
                ->values();

            if ($filtered->isEmpty()) {
                throw new \Exception('Minimal 1 varian diisi');
            }

            foreach ($filtered as $variant) {

                // 🔥 INSERT MOVEMENT
                DB::table('packaging_movements')->insert([
                    'product_id' => $request->product_id,
                    'product_variant_id' => $variant['id'],
                    'type' => 'in',
                    'quantity' => $variant['qty'],
                    'reference_type' => 'packaging_input',
                    'reference_id' => null,
                    'created_by' => auth()->id(),
                    'created_at' => $request->tanggal . ' ' . now()->format('H:i:s'),
                    'updated_at' => now(),
                ]);

                // 🔥 UPSERT STOCK
                $exists = DB::table('packaging_stocks')
                    ->where('product_id', $request->product_id)
                    ->where('product_variant_id', $variant['id'])
                    ->first();

                if ($exists) {
                    DB::table('packaging_stocks')
                        ->where('id', $exists->id)
                        ->increment('stock_qty', $variant['qty']);
                } else {
                    DB::table('packaging_stocks')->insert([
                        'product_id' => $request->product_id,
                        'product_variant_id' => $variant['id'],
                        'stock_qty' => $variant['qty'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

        });

        return back()->with('success', 'Stok kemasan berhasil ditambahkan');
    }

    public function update(Request $request)
{

if(auth()->user()->role !== 'admin'){
    abort(403);
}
    $request->validate([
        'product_id' => 'required',
        'variant_id' => 'required',
        'qty' => 'required|integer|min:0'
    ]);

    DB::transaction(function () use ($request) {

        $current = DB::table('packaging_stocks')
            ->where('product_id', $request->product_id)
            ->where('product_variant_id', $request->variant_id)
            ->value('stock_qty') ?? 0;

        $diff = $request->qty - $current;

        DB::table('packaging_stocks')
            ->updateOrInsert(
                [
                    'product_id' => $request->product_id,
                    'product_variant_id' => $request->variant_id
                ],
                [
                    'stock_qty' => $request->qty,
                    'updated_at' => now()
                ]
            );

        DB::table('packaging_movements')->insert([
            'product_id' => $request->product_id,
            'product_variant_id' => $request->variant_id,
            'type' => 'adjustment',
            'quantity' => $diff,
            'reference_type' => 'manual_edit',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    return back()->with('success', 'Stok berhasil diupdate');
}

public function damage(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'variants' => 'required|array|min:1',
        'variants.*.id' => 'required|exists:product_variants,id',
        'variants.*.qty' => 'nullable|integer|min:1',
    ]);

    DB::transaction(function () use ($request) {

        $filtered = collect($request->variants)
            ->filter(fn($v) => isset($v['qty']) && $v['qty'] > 0)
            ->values();

        if ($filtered->isEmpty()) {
            throw new \Exception('Minimal 1 varian diisi');
        }

        foreach ($filtered as $variant) {

            $stock = DB::table('packaging_stocks')
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $variant['id'])
                ->value('stock_qty') ?? 0;

            if ($stock < $variant['qty']) {
                throw new \Exception('Stok tidak cukup untuk pengurangan');
            }

            // 🔻 KURANGI STOK
            DB::table('packaging_stocks')
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $variant['id'])
                ->decrement('stock_qty', $variant['qty']);

            // 🔻 CATAT MOVEMENT
            DB::table('packaging_movements')->insert([
                'product_id' => $request->product_id,
                'product_variant_id' => $variant['id'],
                'type' => 'out',
                'quantity' => $variant['qty'],
                'reference_type' => 'damage',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    });

    return back()->with('success', 'Kemasan rusak berhasil dikurangi');
}

}
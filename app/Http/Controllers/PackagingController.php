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
    $month = request('month', now()->month); // 🔥 default bulan sekarang
    $year = request('year', now()->year);

    // 🔥 AMBIL STOK KEMASAN
    $stocks = DB::table('product_variants')
    ->join('products', 'products.id', '=', 'product_variants.product_id')
    ->leftJoin('packaging_stocks', function ($join) {
        $join->on('packaging_stocks.product_variant_id', '=', 'product_variants.id');
    })
    ->where('product_variants.is_active', 1)
    ->select(
        'products.id as product_id',
        'products.name as product_name',
        'product_variants.id as variant_id',
        'product_variants.name as variant_name',
        DB::raw('COALESCE(packaging_stocks.stock_qty, 0) as stock_qty')
    )
    ->orderBy('products.name')
    ->get();

    $groupedStocks = [];

      foreach ($stocks as $row) {
    $groupedStocks[$row->product_name][] = $row;
}

/*
=========================
🔥 AMBIL DATA REQUEST (UNTUK FIFO)
=========================
*/

$raw = DB::table('sales_stock_requests')
    ->join('sales_stock_request_items','sales_stock_requests.id','=','sales_stock_request_items.request_id')
    ->join('products','products.id','=','sales_stock_request_items.product_id')
    ->select(
        'sales_stock_requests.request_date',
        'products.id as product_id',
        'products.name as product_name',
        DB::raw('SUM(sales_stock_request_items.qty_pack) as qty')
    )
    ->whereDate('sales_stock_requests.request_date', '>=', today())
    ->groupBy(
        'sales_stock_requests.request_date',
        'products.id',
        'products.name'
    )
    ->orderBy('products.id','asc')
    ->orderBy('sales_stock_requests.request_date','asc')
    ->get();

/*
=========================
READY PACK
=========================
*/

$ready = DB::table('warehouse_ready_packs')
    ->pluck('ready_pack','product_id')
    ->toArray();

/*
=========================
FIFO ENGINE
=========================
*/

$fifo = [];
$currentProduct = null;
$stock = 0;

foreach($raw as $row){

    if($currentProduct !== $row->product_id){
        $currentProduct = $row->product_id;
        $stock = $ready[$row->product_id] ?? 0;
    }

    $allocated = min($stock,$row->qty);
    $short = $row->qty - $allocated;
    $stock -= $allocated;

    $fifo[] = [
        'date'=>$row->request_date,
        'product'=>$row->product_name,
        'product_id'=>$row->product_id,
        'short'=>$short
    ];
}

/*
=========================
🔥 ANALISA KEMASAN
=========================
*/

$packagingAnalysis = [];

foreach($fifo as $row){

    $date = $row['date'];
    $productId = $row['product_id'];
    $productName = $row['product'];
    $shortPack = $row['short'];

    $recipe = DB::table('product_pack_recipes')
        ->where('product_id',$productId)
        ->where('is_active',1)
        ->first();

    if(!$recipe) continue;

    $items = DB::table('product_pack_recipe_items')
        ->where('recipe_id',$recipe->id)
        ->get();

    foreach($items as $item){

        $variant = DB::table('product_variants')
            ->where('id',$item->product_variant_id)
            ->first();

        if(!$variant) continue;

        $needed = $shortPack * $item->qty_per_pack;

        $stockQty = DB::table('packaging_stocks')
            ->where('product_variant_id',$variant->id)
            ->value('stock_qty') ?? 0;

        $short = $needed - $stockQty;

        $packagingAnalysis[$date][$productName][] = [
         'variant' => $variant->name,
         'needed' => $needed,
         'stock' => $stockQty,
         'short' => $short
      ];
    }
}

ksort($packagingAnalysis);



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

    return view('packaging.index', compact(
    'products',
    'stocks',
    'histories',
    'daily',
    'groupedStocks',
    'packagingAnalysis'
));
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

public function history(Request $request)
{
    $histories = DB::table('packaging_movements')
        ->join('products', 'products.id', '=', 'packaging_movements.product_id')
        ->join('product_variants', 'product_variants.id', '=', 'packaging_movements.product_variant_id')
        ->select(
            'packaging_movements.*',
            'products.name as product_name',
            'product_variants.name as variant_name'
        )
        ->orderBy('packaging_movements.created_at', 'desc')
        ->get();

    return view('packaging.history', compact('histories'));
}

public function analysisOnline()
{
    /*
    =========================
    1. AMBIL FIFO PRODUK
    =========================
    */

    $checks = DB::table('online_order_item_checks')
        ->orderBy('online_order_id','asc')
        ->get();

    /*
    =========================
    2. AMBIL STOK AWAL KEMASAN
    =========================
    */

    // 🔥 AMBIL NAMA PRODUK ORDER (PAKET)
   $orderProducts = DB::table('online_order_items as oi')
    ->join('products as p', 'p.id', '=', 'oi.product_id')
    ->select('oi.online_order_id', 'p.name as order_product_name')
    ->get()
    ->groupBy('online_order_id');

    $packagingStocks = DB::table('packaging_stocks')
    ->pluck('stock_qty','product_variant_id')
    ->toArray();


    
    /*
    =========================
    🔥 3. GROUP PER VARIANT
    =========================
    */

    $grouped = [];

    foreach ($checks as $check) {
        $grouped[$check->product_variant_id][] = $check;
    }

    /*
    =========================
    🔥 4. FIFO PER VARIANT
    =========================
    */

    $result = [];

    foreach ($grouped as $variantId => $rows) {

        $stock = $packagingStocks[$variantId] ?? 0;

        $productStockBefore = DB::table('warehouse_variant_stocks')
    ->where('product_variant_id',$variantId)
    ->value('stock_qty') ?? 0;

    $packagingStockBefore = DB::table('packaging_stocks')
    ->where('product_variant_id',$variantId)
    ->value('stock_qty') ?? 0;

        foreach ($rows as $check) {

           $orderData = DB::table('online_orders as o')
    ->join('package_templates as t', 't.id', '=', 'o.package_template_id')
    ->where('o.id', $check->online_order_id)
    ->select(
        't.name as package_name',
        'o.order_date'
    )
    ->first();

$packageName = $orderData->package_name ?? '-';
$orderDate = $orderData->order_date ?? null;

// 🔥 HITUNG DEADLINE (H+1)
$deadline = null;

if ($orderDate) {
    $deadline = \Carbon\Carbon::parse($orderDate)
        ->startOfDay()
        ->addDays(1)
        ->toDateString();
}

            $variantName = DB::table('product_variants')
                ->where('id',$variantId)
                ->value('name');

                $productRealName = DB::table('product_variants as pv')
    ->join('products as p', 'p.id', '=', 'pv.product_id')
    ->where('pv.id', $variantId)
    ->value('p.name');

            // 🔥 CEK: PRODUK CUKUP ATAU TIDAK
if ($check->available_qty >= $check->required_qty) {

    // ✅ PRODUK CUKUP → KEMASAN TIDAK DIPAKAI
    $needed = 0;
    $allocated = 0;
    $shortage = 0;

    $stockBefore = $stock;
    $stockAfter = $stock; // tidak berubah

} else {

    // ❌ PRODUK KURANG → BARU PAKAI KEMASAN
    $needed = $check->required_qty - $check->available_qty;

    $allocated = min($stock, $needed);
    $shortage = $needed - $allocated;

    $stockBefore = $stock;
    $stockAfter = $stock - $allocated;

    $stock = $stockAfter; // update FIFO
}

            // update stock FIFO
            $stock = $stockAfter;

            $result[$check->online_order_id][] = (object)[
    'package_name' => $packageName,
    'order_date' => $orderDate,
    'deadline' => $deadline,
    'product_name' => $productRealName,
    'variant_name' => $variantName,

    'required_qty' => $needed,
    'available_qty' => $allocated,

    'product_stock_before' => $check->stock_before,
    'product_stock_after' => $check->stock_after,

    'packaging_stock_before' => $stockBefore,
    'packaging_stock_after' => $stockAfter,

    'status' => $shortage > 0 ? 'kurang' : 'cukup',
    'shortage_qty' => $shortage
];
        }
    }

    return view('packaging.analysis-online', [
        'data' => collect($result)
    ]);
}

public function analysisOffline()
{
    /*
    =========================
    🔥 AMBIL DATA REQUEST (FIFO)
    =========================
    */

    $raw = DB::table('sales_stock_requests')
        ->join('sales_stock_request_items','sales_stock_requests.id','=','sales_stock_request_items.request_id')
        ->join('products','products.id','=','sales_stock_request_items.product_id')
        ->select(
            'sales_stock_requests.request_date',
            'products.id as product_id',
            'products.name as product_name',
            DB::raw('SUM(sales_stock_request_items.qty_pack) as qty')
        )
        ->whereDate('sales_stock_requests.request_date', '>=', today())
        ->groupBy(
            'sales_stock_requests.request_date',
            'products.id',
            'products.name'
        )
        ->orderBy('products.id','asc')
        ->orderBy('sales_stock_requests.request_date','asc')
        ->get();

    /*
    =========================
    READY PACK
    =========================
    */

    $ready = DB::table('warehouse_ready_packs')
        ->pluck('ready_pack','product_id')
        ->toArray();

    /*
    =========================
    FIFO ENGINE
    =========================
    */

    $fifo = [];
    $currentProduct = null;
    $stock = 0;

    foreach($raw as $row){

        if($currentProduct !== $row->product_id){
            $currentProduct = $row->product_id;
            $stock = $ready[$row->product_id] ?? 0;
        }

        $allocated = min($stock,$row->qty);
        $short = $row->qty - $allocated;
        $stock -= $allocated;

        $fifo[] = [
            'date'=>$row->request_date,
            'product'=>$row->product_name,
            'product_id'=>$row->product_id,
            'short'=>$short
        ];
    }

    /*
    =========================
    🔥 ANALISA KEMASAN
    =========================
    */

    $packagingAnalysis = [];

    foreach($fifo as $row){

        $date = $row['date'];
        $productId = $row['product_id'];
        $productName = $row['product'];
        $shortPack = $row['short'];

        $recipe = DB::table('product_pack_recipes')
            ->where('product_id',$productId)
            ->where('is_active',1)
            ->first();

        if(!$recipe) continue;

        $items = DB::table('product_pack_recipe_items')
            ->where('recipe_id',$recipe->id)
            ->get();

        foreach($items as $item){

            $variant = DB::table('product_variants')
                ->where('id',$item->product_variant_id)
                ->first();

            if(!$variant) continue;

            $needed = $shortPack * $item->qty_per_pack;

            $stockQty = DB::table('packaging_stocks')
                ->where('product_variant_id',$variant->id)
                ->value('stock_qty') ?? 0;

            $short = $needed - $stockQty;

            $packagingAnalysis[$date][$productName][] = [
                'variant' => $variant->name,
                'needed' => $needed,
                'stock' => $stockQty,
                'short' => $short
            ];
        }
    }

    ksort($packagingAnalysis);

    return view('packaging.analysis-offline', compact('packagingAnalysis'));
}

}
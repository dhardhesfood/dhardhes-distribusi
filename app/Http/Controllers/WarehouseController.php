<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\WarehouseNote;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OnlineOrderController;

class WarehouseController extends Controller
{
    /**
     * Halaman stok gudang
     */
    public function index()
    {
        Notification::where('user_id', auth()->id())
        ->where('type', 'warehouse_note')
        ->update(['is_read' => true]);

        $stocks = DB::table('products')
       ->where('products.channel_type', 'offline')
       ->leftJoin('stock_movements', 'products.id', '=', 'stock_movements.product_id')
       ->leftJoin('warehouse_ready_packs', 'products.id', '=', 'warehouse_ready_packs.product_id')
            ->select(
                     'products.id',
                     'products.name',
                     'products.channel_type',
                  DB::raw('COALESCE(warehouse_ready_packs.ready_pack, 0) as ready_pack'),
                  DB::raw("
                    COALESCE(
                    SUM(
                    CASE
                    WHEN stock_movements.type = 'warehouse_in' THEN stock_movements.quantity
                    WHEN stock_movements.type = 'warehouse_out' THEN -stock_movements.quantity
                    WHEN stock_movements.type = 'adjustment' THEN stock_movements.quantity
                    ELSE 0
                END
            ), 0
        ) as stock
    ")
)

            ->groupBy(
    'products.id',
    'products.name',
    'products.channel_type',
    'warehouse_ready_packs.ready_pack'
)
            ->orderBy('products.name')
            ->get();

        $notes = \App\Models\WarehouseNote::with('user')
           ->where('created_at', '>=', now()->subDays(7))
           ->orderBy('created_at', 'desc')
           ->get();

           // 🔥 STOK ONLINE (VARIANT LEVEL)
    $onlineStocks = DB::table('warehouse_variant_stocks')
    ->join('products', 'products.id', '=', 'warehouse_variant_stocks.product_id')
    ->join('product_variants', 'product_variants.id', '=', 'warehouse_variant_stocks.product_variant_id')
    ->where('products.channel_type', 'online')
    ->select(
        'products.id as product_id',
        'products.name as product_name',
        'product_variants.name as variant_name',
        'warehouse_variant_stocks.stock_qty'
    )
    ->orderBy('products.name')
    ->get();

       return view('warehouse.index', compact('stocks','notes','onlineStocks'));
    }

    /**
     * Form transfer gudang → sales
     */
    public function createTransfer()
    {
        $products = Product::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $sales = DB::table('users')
            ->where('role', 'sales')
            ->orderBy('name')
            ->get();

        return view('warehouse.transfer', compact('products', 'sales'));
    }

    /**
     * Simpan transfer gudang → sales
     */
    public function storeTransfer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->products as $productId => $qty) {

                if ($qty <= 0) continue;

                $product = Product::find($productId);

                if ($product->channel_type === 'online') {
                continue; // ❌ blok produk online
                }

                StockMovement::create([
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'type' => 'warehouse_out',
                    'reference_type' => 'warehouse_transfer_sales',
                    'notes' => 'Transfer gudang ke sales user_id=' . $request->user_id,
                    'created_by' => auth()->id(),
                ]);
            }

        });

        return redirect()
            ->route('warehouse.index')
            ->with('success', 'Transfer stok ke sales berhasil.');
    }

    public function storeNote(Request $request)
{
    $request->validate([
        'message' => 'required|string|max:1000'
    ]);

    $note = WarehouseNote::create([
        'user_id' => auth()->id(),
        'message' => $request->message
    ]);

    // BUAT NOTIFIKASI
    $users = User::where('id','!=',auth()->id())->select('id')->get();

    foreach ($users as $user) {

        Notification::create([
            'user_id' => $user->id,
            'type' => 'warehouse_note',
            'title' => 'Catatan Gudang',
            'message' => auth()->user()->name.' mengirim catatan gudang',
            'link' => route('warehouse.index')
        ]);

    }

    return redirect()->route('warehouse.index');
}

public function updateReadyPacks(Request $request)
{
    foreach ($request->ready_packs as $productId => $pack) {

        DB::table('warehouse_ready_packs')
            ->where('product_id', $productId)
            ->update([
                'ready_pack' => $pack ?? 0,
                'updated_by' => auth()->id(),
                'updated_at' => now()
            ]);
    }

    return redirect()
        ->route('warehouse.index')
        ->with('success', 'Stok ready pack berhasil diperbarui.');
}

public function convertForm($productId)
{
    $product = Product::findOrFail($productId);

    // ambil total stok offline
    $stock = DB::table('stock_movements')
        ->where('product_id', $productId)
        ->select(DB::raw("
            SUM(
                CASE
                    WHEN type = 'warehouse_in' THEN quantity
                    WHEN type = 'warehouse_out' THEN -quantity
                    WHEN type = 'adjustment' THEN quantity
                    ELSE 0
                END
            ) as total
        "))
        ->value('total') ?? 0;

    $variants = DB::table('product_variants')
        ->where('product_id', $productId)
        ->get();

    return view('warehouse.convert', compact('product','stock','variants'));
}

public function convertProcess(Request $request, $productId)
{
    DB::transaction(function () use ($request, $productId) {

        // ambil total stok lama
        $totalStock = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->select(DB::raw("
                SUM(
                    CASE
                        WHEN type = 'warehouse_in' THEN quantity
                        WHEN type = 'warehouse_out' THEN -quantity
                        WHEN type = 'adjustment' THEN quantity
                        ELSE 0
                    END
                ) as total
            "))
            ->value('total') ?? 0;

        $inputTotal = collect($request->variants)->sum('qty');

        // validasi
        if ($inputTotal != $totalStock) {
            throw new \Exception("Total varian harus sama dengan stok ($totalStock)");
        }

        // 🔥 INSERT KE ONLINE
        foreach ($request->variants as $variantId => $data) {

            if ($data['qty'] <= 0) continue;

            DB::table('warehouse_variant_stocks')->updateOrInsert(
                [
                    'product_id' => $productId,
                    'product_variant_id' => $variantId
                ],
                [
                    'stock_qty' => DB::raw("stock_qty + {$data['qty']}"),
                    'updated_at' => now()
                ]
            );
        }
        
        // 🔥 HAPUS STOK LAMA (ZERO)
        if ($totalStock > 0) {
            DB::table('stock_movements')->insert([
                'product_id' => $productId,
                'quantity' => $totalStock,
                'type' => 'warehouse_out',
                'reference_type' => 'convert_to_online',
                'notes' => 'Convert ke online',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        }

    });

    app(OnlineOrderController::class)->simulateStock();

    // 🔥 AUTO SET PRODUCT JADI ONLINE
DB::table('products')
    ->where('id', $productId)
    ->update([
        'channel_type' => 'online'
    ]);

    return redirect()->route('warehouse.index')
        ->with('success', 'Stok berhasil dipindahkan ke online');
}

public function convertOfflineForm($productId)
{
    $product = Product::findOrFail($productId);

    $variants = DB::table('warehouse_variant_stocks')
        ->join('product_variants', 'product_variants.id', '=', 'warehouse_variant_stocks.product_variant_id')
        ->where('warehouse_variant_stocks.product_id', $productId)
        ->select(
            'product_variants.name',
            'warehouse_variant_stocks.stock_qty'
        )
        ->get();

    $total = $variants->sum('stock_qty');

    return view('warehouse.convert_offline', compact('product','variants','total'));
}

public function convertToOffline($productId)
{
    DB::transaction(function () use ($productId) {

        // ambil total stok online
        $total = DB::table('warehouse_variant_stocks')
            ->where('product_id', $productId)
            ->sum('stock_qty');

        if ($total <= 0) {
            throw new \Exception('Tidak ada stok online');
        }

        // hapus semua variant
        DB::table('warehouse_variant_stocks')
            ->where('product_id', $productId)
            ->delete();

        // masuk ke gudang offline
        DB::table('stock_movements')->insert([
            'product_id' => $productId,
            'quantity' => $total,
            'type' => 'warehouse_in',
            'reference_type' => 'convert_to_offline',
            'notes' => 'Convert ke offline',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ubah channel
        DB::table('products')
            ->where('id', $productId)
            ->update([
                'channel_type' => 'offline'
            ]);
    });

    app(OnlineOrderController::class)->simulateStock();

    return redirect()->route('warehouse.index')
        ->with('success', 'Stok berhasil dikembalikan ke offline');
}

public function history()
{
    $movements = DB::table('stock_movements')
    ->leftJoin('products', 'products.id', '=', 'stock_movements.product_id')
    ->leftJoin('users', 'users.id', '=', 'stock_movements.created_by')

    ->where(function ($q) {

        // ✅ MURNI GUDANG
        $q->where('stock_movements.type', 'warehouse_in')

        // ✅ warehouse_out tapi BUKAN sales
        ->orWhere(function ($q2) {
            $q2->where('stock_movements.type', 'warehouse_out')
               ->whereNotIn('stock_movements.reference_type', [
                   'sales_stock_session'
                   
               ]);
        })

        ->orWhereIn('stock_movements.reference_type', [
        'production_batch',
        'production_delete'
        ])

          // 🔥 TAMBAHAN: STOK KE SALES (SESSION OPEN)
        ->orWhere(function ($q3) {
        $q3->where('stock_movements.type', 'warehouse_out')
           ->where('stock_movements.reference_type', 'sales_stock_session');
    })

        // ✅ adjustment khusus gudang
        ->orWhere(function ($q2) {
            $q2->where('stock_movements.type', 'adjustment')
               ->whereIn('stock_movements.reference_type', [
                   'warehouse_adjustment',
                   'warehouse_reset_test'
               ]);
        })

        // ✅ lainnya yang memang gudang
        ->orWhereIn('stock_movements.type', [
            'damage',
            'return_from_store'
        ]);

    })

    ->select(
        'stock_movements.*',
        'products.name as product_name',
        'users.name as created_by_name'
    )
    ->orderByDesc('stock_movements.created_at')
    ->paginate(20);

    return view('warehouse.history', compact('movements'));
}

public function historyOnline()
{
    $convert = DB::table('stock_movements')
    ->select(
        'product_id',
        'quantity',
        'reference_type',
        'created_at',
        DB::raw("NULL as customer_name") // 🔥 WAJIB ADA
    )
        ->whereIn('reference_type', [
            'convert_to_online',
            'convert_to_offline'
        ]);

    // 🔥 2. DATA ORDER (AMBIL DARI ORDER ITEMS)
    $orders = DB::table('online_order_items')
    ->join('online_orders', 'online_orders.id', '=', 'online_order_items.online_order_id')
    ->leftJoin('customers', 'customers.id', '=', 'online_orders.customer_id') // 🔥 TAMBAH INI
    ->select(
        'online_order_items.product_id',
        DB::raw('-online_order_items.qty as quantity'),
        DB::raw("'online_order_done' as reference_type"),
        'online_orders.updated_at as created_at',
        'customers.name as customer_name' // 🔥 INI KUNCI
    )
    ->where('online_orders.status', 'done');

    // 🔥 UNION
    $movements = $convert
        ->unionAll($orders);

    $result = DB::query()
        ->fromSub($movements, 'm')
        ->leftJoin('products', 'products.id', '=', 'm.product_id')
        ->select('m.*', 'products.name as product_name')
        ->orderByDesc('m.created_at')
        ->paginate(20);

    return view('warehouse.history_online', [
        'movements' => $result
    ]);
}

}
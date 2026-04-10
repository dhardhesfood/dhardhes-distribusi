<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineOrderController extends Controller
{
    public function create()
    {
        $templates = DB::table('package_templates')
            ->orderBy('name')
            ->get();

        return view('online_orders.create', compact('templates'));
    }

    public function getTemplateItems($templateId)
    {
        $items = DB::table('package_template_items')
            ->join('products', 'products.id', '=', 'package_template_items.product_id')
            ->join('product_variants', 'product_variants.id', '=', 'package_template_items.product_variant_id')
            ->where('package_template_items.package_template_id', $templateId)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_variants.id as variant_id',
                'product_variants.name as variant_name',
                'package_template_items.qty'
            )
            ->get();

        return response()->json($items);
    }

    public function index()
{
    $orders = DB::table('online_orders as o')
    ->leftJoin('package_templates as t', 't.id', '=', 'o.package_template_id')
    ->select(
        'o.*',
        't.name as package_name'
    )
    ->orderByDesc('o.id')
    ->get();

    $items = DB::table('online_order_items')
        ->join('products', 'products.id', '=', 'online_order_items.product_id')
        ->join('product_variants', 'product_variants.id', '=', 'online_order_items.product_variant_id')
        ->select(
    'online_order_items.online_order_id',
    'online_order_items.product_id',
    'online_order_items.product_variant_id',
    'products.name as product_name',
    'product_variants.name as variant_name',
    'online_order_items.qty'
)
        ->get()
        ->groupBy('online_order_id');

    $checks = DB::table('online_order_item_checks')
    ->get()
    ->groupBy('online_order_id');

       return view('online_orders.index', compact('orders', 'items', 'checks'));

}

public function edit($id)
{
    $order = DB::table('online_orders')->where('id', $id)->first();

    $items = DB::table('online_order_items')
    ->join('products', 'products.id', '=', 'online_order_items.product_id')
    ->join('product_variants', 'product_variants.id', '=', 'online_order_items.product_variant_id')
    ->where('online_order_items.online_order_id', $id)
    ->select(
        'online_order_items.*',
        'products.name as product_name',
        'product_variants.name as variant_name'
    )
    ->get();

    return view('online_orders.edit', compact('order', 'items'));
}

public function update(Request $request, $id)
{
    DB::transaction(function () use ($request, $id) {

        // update order
        DB::table('online_orders')->where('id', $id)->update([
            'customer_name' => $request->customer_name,
            'order_date' => $request->order_date,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);

        // hapus item lama
        DB::table('online_order_items')
            ->where('online_order_id', $id)
            ->delete();

        // insert ulang item
        foreach ($request->items as $item) {

            if (($item['qty'] ?? 0) <= 0) continue;

            DB::table('online_order_items')->insert([
                'online_order_id' => $id,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['variant_id'],
                'qty' => $item['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        });

        // rerun simulasi
        $this->simulateStock();

    

    return redirect('/online-orders')->with('success', 'Order berhasil diupdate');
}

public function destroy($id)
{
    DB::transaction(function () use ($id) {

        DB::table('online_order_items')->where('online_order_id', $id)->delete();
        DB::table('online_order_item_checks')->where('online_order_id', $id)->delete();

        // 🔥 WAJIB TAMBAH INI
        DB::table('packaging_analysis_online')->where('online_order_id', $id)->delete();

        DB::table('online_orders')->where('id', $id)->delete();

    });

    return redirect('/online-orders')->with('success', 'Order berhasil dihapus');
}

    public function store(Request $request)
{
    $orderId = null;

    DB::transaction(function () use ($request, &$orderId) {

        $orderId = DB::table('online_orders')->insertGetId([
            'customer_name' => $request->customer_name,
            'order_date' => $request->order_date,
            'status' => 'on_process',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'package_template_id' => $request->template_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($request->items as $item) {

            if (($item['qty'] ?? 0) <= 0) continue;

            DB::table('online_order_items')->insert([
                'online_order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['variant_id'],
                'qty' => $item['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    });

    // ✅ JALANKAN DI LUAR TRANSACTION
    $this->simulateStock();

    // ⛔ MATIKAN DULU (biar aman)
    // $this->simulatePackaging($orderId);

    return redirect('/online-orders/create')
        ->with('success', 'Order berhasil disimpan');
}

   private function simulatePackaging($orderId)
{
    $checks = DB::table('online_order_item_checks')
    ->where('status', 'kurang')
    ->get();

    // bersihin dulu
    DB::table('packaging_analysis_online')
        ->where('online_order_id', $orderId)
        ->delete();

    foreach ($checks as $check) {

        // 🔥 hanya produk yang kurang
        if ($check->status !== 'kurang') continue;

        // 🔥 ambil shortage dari hasil FIFO (bukan qty mentah)
        $neededQty = $check->shortage_qty;

        // 🔥 ambil stok KEMASAN dari ledger
        $stock = DB::table('packaging_movements')
            ->where('product_variant_id', $check->product_variant_id)
            ->sum('quantity');

        $stock = $stock ?? 0;

        $status = $stock >= $neededQty ? 'cukup' : 'kurang';

        $shortage = $neededQty > $stock
            ? $neededQty - $stock
            : 0;

        DB::table('packaging_analysis_online')->insert([
            'online_order_id' => $orderId,
            'product_id' => $check->product_id,
            'product_variant_id' => $check->product_variant_id,
            'packaging_id' => $check->product_variant_id,
            'required_qty' => $neededQty,
            'available_qty' => $stock,
            'status' => $status,
            'shortage_qty' => $shortage,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
private function simulateStock()
{
    /*
    =========================
    1. AMBIL SEMUA ORDER AKTIF
    =========================
    */

    $orders = DB::table('online_orders')
        ->where('status', 'on_process')
        ->orderBy('created_at', 'asc')
        ->get();

    /*
    =========================
    2. AMBIL SEMUA ITEM
    =========================
    */

    $items = DB::table('online_order_items')
        ->join('online_orders', 'online_orders.id', '=', 'online_order_items.online_order_id')
        ->where('online_orders.status', 'on_process')
        ->select(
            'online_order_items.*',
            'online_orders.created_at'
        )
        ->orderBy('online_orders.created_at', 'asc')
        ->get();

    /*
    =========================
    3. GROUP BY VARIANT
    =========================
    */

    $grouped = [];

    foreach ($items as $item) {
        $grouped[$item->product_variant_id][] = $item;
    }

    /*
    =========================
    4. RESET TABLE (REBUILD TOTAL)
    =========================
    */

   DB::table('online_order_item_checks')->delete();

    /*
    =========================
    5. FIFO PER VARIANT
    =========================
    */

    foreach ($grouped as $variantId => $rows) {

        // ambil stok awal
        $stock = DB::table('warehouse_variant_stocks')
            ->where('product_variant_id', $variantId)
            ->value('stock_qty') ?? 0;

        foreach ($rows as $row) {

            $required = $row->qty;

            $allocated = min($stock, $required);
            $shortage = $required - $allocated;

            $stock -= $allocated;

            $status = $shortage > 0 ? 'kurang' : 'cukup';

            $stockBefore = $stock + $allocated; // stok sebelum dikurangi

DB::table('online_order_item_checks')->insert([
    'online_order_id' => $row->online_order_id,
    'product_id' => $row->product_id,
    'product_variant_id' => $row->product_variant_id,

    'required_qty' => $required,

    'available_qty' => $allocated,
    'stock_before' => $stockBefore,
    'stock_after' => $stock,

    'status' => $status,
    'shortage_qty' => $shortage,
    'created_at' => now(),
    'updated_at' => now(),
]);

        }
    }
}
public function updateStatus(Request $request, $id)
{
    $newStatus = $request->status;

    // =========================
// VALIDASI: CEK FIFO DULU
// =========================
if ($request->status == 'done') {

    $hasShortage = DB::table('online_order_item_checks')
        ->where('online_order_id', $id)
        ->where('status', 'kurang')
        ->exists();

    if ($hasShortage) {
        return back()->with('error', 'Stok tidak cukup, tidak bisa DONE');
    }
}

    DB::transaction(function () use ($id, $newStatus) {

        $order = DB::table('online_orders')->where('id', $id)->first();

        if (!$order) return;

        /*
        =========================
        DONE → POTONG STOK
        =========================
        */
        if ($newStatus == 'done') {

            if ($order->is_stock_deducted == 0) {

                $items = DB::table('online_order_items')
                    ->where('online_order_id', $id)
                    ->get();

                foreach ($items as $item) {

                    DB::table('warehouse_variant_stocks')
                        ->where('product_variant_id', $item->product_variant_id)
                        ->decrement('stock_qty', $item->qty);
                }

                DB::table('online_orders')->where('id', $id)->update([
                    'is_stock_deducted' => 1,
                    'is_stock_returned' => 0
                ]);
            }
        }

        /*
        =========================
        RETURN → BALIKIN STOK (WAJIB)
        =========================
        */
        if ($newStatus == 'returned') {

            if ($order->is_stock_deducted == 1 && $order->is_stock_returned == 0) {

                $items = DB::table('online_order_items')
                    ->where('online_order_id', $id)
                    ->get();

                foreach ($items as $item) {

                    DB::table('warehouse_variant_stocks')
                        ->where('product_variant_id', $item->product_variant_id)
                        ->increment('stock_qty', $item->qty);
                }

                DB::table('online_orders')->where('id', $id)->update([
                    'is_stock_returned' => 1
                ]);
            }
        }

        /*
        =========================
        CANCEL → TIDAK SENTUH STOK
        =========================
        */
        if ($newStatus == 'cancelled') {
            // kosong (hanya keluar dari FIFO)
        }

        /*
        =========================
        UPDATE STATUS
        =========================
        */
        DB::table('online_orders')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);
    });

    // 🔥 WAJIB: rerun FIFO
    $this->simulateStock();

    return back()->with('success', 'Status berhasil diupdate');
}

}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OnlineOrderController extends Controller
{
    public function create()
    {
        $templates = DB::table('package_templates')
            ->orderBy('name')
            ->get();
        $customers = DB::table('customers')->get();

        return view('online_orders.create', compact('templates', 'customers'));
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
    $this->simulateStock();
    $month = request('month', now()->month);
    $year  = request('year', now()->year);
    $orders = DB::table('online_orders as o')
    ->leftJoin('package_templates as t', 't.id', '=', 'o.package_template_id')
    ->leftJoin('customers as c', 'c.id', '=', 'o.customer_id') // 🔥 tambah JOIN
    ->select(
    'o.*',
    't.name as package_name',
    'c.phone as customer_phone', // 🔥 tambah
    'c.name as customer_real_name' // 🔥 tambah
    )
    ->whereMonth('o.order_date', $month)
    ->whereYear('o.order_date', $year)
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

        $order = DB::table('online_orders')->where('id', $id)->first();

        if (!$order) return;

        // 🔥 ambil dari customer_id
        $customer = DB::table('customers')->where('id', $order->customer_id)->first();

        DB::table('online_orders')->where('id', $id)->update([
            'customer_name' => $customer->name,
            'order_date' => $request->order_date,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);

        // hapus item lama
        DB::table('online_order_items')
            ->where('online_order_id', $id)
            ->delete();

        // insert ulang
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

        // ======================
        // HANDLE CUSTOMER
        // ======================
        if ($request->new_customer_name && $request->new_customer_phone) {

            $customerId = DB::table('customers')->insertGetId([
                'name' => $request->new_customer_name,
                'phone' => $this->normalizePhone($request->new_customer_phone),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } else {
            $customerId = $request->customer_id;
        }

        // 🔥 AMBIL DATA CUSTOMER (INI KUNCI)
        $customer = DB::table('customers')->where('id', $customerId)->first();

        // ======================
        // INSERT ORDER
        // ======================
        $orderId = DB::table('online_orders')->insertGetId([
            'customer_id' => $customerId,
            'customer_name' => $customer->name, // ✅ dari DB, bukan input
            'payment_type' => $request->payment_type,
            'order_date' => $request->order_date,
            'status' => 'on_process',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'package_template_id' => $request->template_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ======================
        // INSERT ITEMS
        // ======================
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

    // ======================
    // AFTER PROCESS
    // ======================
    $this->simulateStock();
    $this->simulatePackaging($orderId);

    return redirect('/online-orders/create')
        ->with('success', 'Order berhasil disimpan');
}

   private function simulatePackaging($orderId)
{
    $checks = DB::table('online_order_item_checks')
    ->where('online_order_id', $orderId) // 🔥 WAJIB
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
public function simulateStock()
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
$this->simulateStock(); // 🔥 pastikan FIFO fresh
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

    // 🔻 KURANGI STOK
    $this->updateWarehouseStock(
        $item->product_variant_id,
        $item->qty,
        'decrement'
    );

    // 🔥 CATAT KE STOCK MOVEMENTS
    DB::table('stock_movements')->insert([
        'product_id' => $item->product_id,
        'quantity' => -$item->qty,
        'type' => 'warehouse_out',
        'reference_id' => $id,
        'reference_type' => 'online_order_done',
        'notes' => 'Order Online',
        'created_by' => auth()->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
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

                    $this->updateWarehouseStock(
                    $item->product_variant_id,
                    $item->qty,
                    'increment'
                    );
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
    //$this->sendWhatsAppNotification($id);


    return back()->with('success', 'Status berhasil diupdate');
}

public function updateWarehouseStock($variantId, $qty, $type = 'set')
{
    DB::transaction(function () use ($variantId, $qty, $type) {

        if ($type == 'set') {
            DB::table('warehouse_variant_stocks')
                ->where('product_variant_id', $variantId)
                ->update([
                    'stock_qty' => $qty
                ]);
        }

        if ($type == 'increment') {
            DB::table('warehouse_variant_stocks')
                ->where('product_variant_id', $variantId)
                ->increment('stock_qty', $qty);
        }

        if ($type == 'decrement') {
            DB::table('warehouse_variant_stocks')
                ->where('product_variant_id', $variantId)
                ->decrement('stock_qty', $qty);
        }
    });

    // 🔥 PENTING: AUTO REBUILD FIFO
    $this->simulateStock();
    
}

private function sendWhatsAppNotification($orderId)
{
    $order = DB::table('online_orders as o')
    ->leftJoin('package_templates as t', 't.id', '=', 'o.package_template_id')
    ->where('o.id', $orderId)
    ->select('o.*', 't.name as package_name')
    ->first();

    if (!$order) return;

    // deadline H+1
    $deadline = Carbon::parse($order->order_date)->addDay()->format('d-m-Y');

    $orderItems = DB::table('online_order_items')
    ->join('products', 'products.id', '=', 'online_order_items.product_id')
    ->join('product_variants', 'product_variants.id', '=', 'online_order_items.product_variant_id')
    ->where('online_order_id', $orderId)
    ->select(
        'products.name as product_name',
        'product_variants.name as variant_name',
        'qty'
    )
    ->get();

    // FIFO PRODUK (GUDANG)
    $checks = DB::table('online_order_item_checks')
        ->join('products', 'products.id', '=', 'online_order_item_checks.product_id')
        ->join('product_variants', 'product_variants.id', '=', 'online_order_item_checks.product_variant_id')
        ->where('online_order_id', $orderId)
        ->select(
            'products.name as product_name',
            'product_variants.name as variant_name',
            'required_qty',
            'available_qty',
            'shortage_qty'
        )
        ->get();

    if ($checks->isEmpty()) return;

    // =========================
    // FORMAT PESAN
    // =========================
$message = "📦 ORDER ONLINE BARU\n\n";
$message .= "Customer: {$order->customer_name}\n";
$message .= "Paket: " . ($order->package_name ?? '-') . "\n";
$message .= "Tanggal: {$order->order_date}\n";
$message .= "Deadline: {$deadline}\n\n";

$message .= "🧾 DETAIL ORDER:\n";

foreach ($orderItems as $item) {
    $message .= "- {$item->product_name} {$item->variant_name} ({$item->qty} pcs)\n";
}

$message .= "\n📊 STATUS PRODUK:\n";

    $hasShortage = false;

    foreach ($checks as $c) {
        if ($c->shortage_qty > 0) {
            $hasShortage = true;
            $message .= "- {$c->product_name} {$c->variant_name} ❌ kurang {$c->shortage_qty}\n";
        } else {
            $message .= "- {$c->product_name} {$c->variant_name} ✔ cukup\n";
        }
    }

    if ($hasShortage) {
    $message .= "\n⚠️ PRODUK KURANG → SEGERA PRODUKSI";
    } else {
    $message .= "\n✅ STOK AMAN → SIAP DIPROSES";
    }

    // =========================
    // KIRIM WA TIM PRODUKSI
    // =========================
    $phones = [
        '6285736167569@c.us', // BU ANI
        '6288989393804@c.us', // BU WATI
        '62895808077030@c.us', // BU INTAN
        '62859176866956@c.us', // BU NITA
        // tambah nomor lain
    ];

    foreach ($phones as $phone) {

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => 'c07522d03e6b4c8e91785b62e4e7676f'
            ])->post('http://localhost:3000/api/sendText', [
                'session' => 'MindhesRara',
                'chatId' => $phone,
                'text' => $message
            ]);

            // log response (biar kalau error kelihatan)
            \Log::info('WA RESPONSE', [
                'phone' => $phone,
                'response' => $response->body()
            ]);

        } catch (\Exception $e) {
            \Log::error('WA ERROR', [
                'message' => $e->getMessage()
            ]);
        }

        // 🔥 TAMBAHAN JEDA AMAN
    sleep(rand(1,2)); // 1–2 detik (random)
    }
}

private function sendPackagingNotification($orderId)
{
    
    $order = DB::table('online_orders')->where('id', $orderId)->first();
    if (!$order) return;

    $deadline = Carbon::parse($order->order_date)->addDay()->format('d-m-Y');
    $orderItems = DB::table('online_order_items')
    ->join('products', 'products.id', '=', 'online_order_items.product_id')
    ->join('product_variants', 'product_variants.id', '=', 'online_order_items.product_variant_id')
    ->where('online_order_id', $orderId)
    ->select(
        'products.name as product_name',
        'product_variants.name as variant_name',
        'qty'
    )
    ->get();

    $items = DB::table('packaging_analysis_online')
        ->join('products', 'products.id', '=', 'packaging_analysis_online.product_id')
        ->join('product_variants', 'product_variants.id', '=', 'packaging_analysis_online.product_variant_id')
        ->where('online_order_id', $orderId)
        ->select(
            'products.name as product_name',
            'product_variants.name as variant_name',
            'required_qty',
            'shortage_qty',
            'status'
        )
        ->get();

    if ($items->isEmpty()) return;

    $message = "📦 KEBUTUHAN KEMASAN\n\n";
$message .= "Customer: {$order->customer_name}\n";
$message .= "Deadline: {$deadline}\n\n";

$message .= "🧾 DETAIL ORDER:\n";

foreach ($orderItems as $item) {
    $message .= "- {$item->product_name} {$item->variant_name} ({$item->qty} pcs)\n";
}

$message .= "\n📦 KEMASAN KURANG:\n";

    $hasShortage = false;

    foreach ($items as $item) {
        if ($item->shortage_qty > 0) {
            $hasShortage = true;
            $message .= "- {$item->product_name} {$item->variant_name} ❌ kurang {$item->shortage_qty}\n";
        }
    }

    if (!$hasShortage) return; // 🔥 kalau semua cukup, gak usah spam WA

    $message .= "\n⚠️ SEGERA SIAPKAN KEMASAN";

    // =========================
    // KIRIM WA TIM KEMASAN
    // =========================

    $phones = [
        '6289632217755@c.us', // Anam
        '6282113101340@c.us', // Hafid
    ];

    foreach ($phones as $phone) {
        Http::withHeaders([
            'X-API-KEY' => 'c07522d03e6b4c8e91785b62e4e7676f'
        ])->post('http://localhost:3000/api/sendText', [
            'session' => 'MindhesRara',
            'chatId' => $phone,
            'text' => $message
        ]);

        // 🔥 TAMBAHAN JEDA AMAN
    sleep(rand(1,2)); // 1–2 detik (random)
    }
}

public function sendManualWA($id)
{
    // pastikan data fresh
    $this->simulateStock();
    $this->simulatePackaging($id);

    // kirim WA
    $this->sendWhatsAppNotification($id);
    $this->sendPackagingNotification($id);

    return back()->with('success', 'WA berhasil dikirim');
}

private function generateWhatsAppLink($order)
{
    // ======================
    // 1. AMBIL NOMOR
    // ======================
    $phone = $order->customer_phone;

    // ======================
    // 2. NORMALISASI NOMOR
    // ======================
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (substr($phone, 0, 1) == '0') {
        $phone = '62' . substr($phone, 1);
    }

    // ======================
    // 3. CEK JENIS PAKET
    // ======================
    $name = strtolower($order->package_name);

    $name = strtolower($order->package_name);

    if (str_contains($name, '15') && str_contains($name, '40')) {
    $type = 'bundling';
    } elseif (str_contains($name, '15')) {
    $type = '15';
    } elseif (str_contains($name, '40')) {
    $type = '40';
    } else {
    $type = 'bundling';
    }

    // ======================
    // 4. CEK PAYMENT
    // ======================
    $payment = $order->payment_type;

    // ======================
    // 5. PILIH TEMPLATE (ORIGINAL)
    // ======================

    if ($payment == 'cod' && $type == '15') {

$message = "Hallo kak, berikut update Resi nya yaa 👏🏻
Dan sekalian Felicia mau reminder yaa 😉
Bahwa kakak menggunakan pembayaran sistem COD / bayar ditempat 

Maka dari itu ketika barang datang mohon kakak sudah menyiapkan Uang COD nya.

Dan apabila kakak bepergian atau tidak dirumah mohon untuk Uang COD nya dititipkan ke orang yang ada di rumah ya kak agar ketika kurir datang, ada yang menerima paket tersebut.

*PENTING JIKA PAKET SUDAH DITERIMA*
- Kirimkan foto paket yang menampakkan No RESI dengan jelas *(Syarat klaim jika ada produk rusak/produk kurang) dan (Syarat GRATIS/SUBSIDI ONGKIR)* dipesanan berikutnya.

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 15gr : https://drive.google.com/drive/folders/1XIvDDy-H3QDSEV8VCGnYyKh16vGChDDd?usp=sharing

Terimakasih banyak kak
Semoga berkah dan laris jualannya, aminn..🤲";

    } elseif ($payment == 'transfer' && $type == '15') {

$message = "Felicia ijin update resinya yaa kak 👏🏻

Pastikan memvideo unboxing saat membuka paket
Jika ada produk rusak / Lolos QC akan kita ganti dengan produk baru atau kita refund dana sesuai dengan produk yang rusak (dengan menyertakan bukti video unboxing)
.

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 15gr : https://drive.google.com/drive/folders/1XIvDDy-H3QDSEV8VCGnYyKh16vGChDDd?usp=sharing

Terimakasih atas kepercayaannya bermitra dengan Dhardhes food 🥰🙏🏻

Semoga kakak sekeluarga selalu diberikan kesehatan dan rejeki yang barokah
.
Dan semoga laris jualannya ya kak dan semoga berkah.. aminn 🤲";

    } elseif ($payment == 'cod' && $type == '40') {

$message = "Hallo kak, berikut update Resi nya yaa 👏🏻
Dan sekalian Felicia mau reminder yaa 😉
Bahwa kakak menggunakan pembayaran sistem COD / bayar ditempat 

Maka dari itu ketika barang datang mohon kakak sudah menyiapkan Uang COD nya.

Dan apabila kakak bepergian atau tidak dirumah mohon untuk Uang COD nya dititipkan ke orang yang ada di rumah ya kak agar ketika kurir datang, ada yang menerima paket tersebut.

*PENTING JIKA PAKET SUDAH DITERIMA*
- Kirimkan foto paket yang menampakkan No RESI dengan jelas 
*(Syarat klaim jika ada produk rusak/produk kurang dan (Syarat GRATIS/SUBSIDI ONGKIR)* dipesanan berikutnya.

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 40gr : https://drive.google.com/drive/folders/1TpnKEIiAGTVx3yjxI_uFx4MH3lNhXL3Q?usp=sharing


Terimakasih banyak kak
Semoga berkah dan laris jualannya, aminn..🤲";

    } elseif ($payment == 'transfer' && $type == '40') {

$message = "Felicia ijin update resinya yaa kak 👏🏻

Pastikan memvideo unboxing saat membuka paket
Jika ada produk rusak / Lolos QC akan kita ganti dengan produk baru atau kita refund dana sesuai dengan produk yang rusak (dengan menyertakan bukti video unboxing)
.

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 40gr : https://drive.google.com/drive/folders/1TpnKEIiAGTVx3yjxI_uFx4MH3lNhXL3Q?usp=sharing

Terimakasih atas kepercayaannya bermitra dengan Dhardhes food 🥰🙏🏻

Semoga kakak sekeluarga selalu diberikan kesehatan dan rejeki yang barokah
.
Dan semoga laris jualannya ya kak dan semoga berkah.. aminn 🤲";

    } elseif ($payment == 'cod' && $type == 'bundling') {

$message = "Hallo kak, berikut update Resi nya yaa 👏🏻
Dan sekalian Felicia mau reminder yaa 😉
Bahwa kakak menggunakan pembayaran sistem COD / bayar ditempat 

Maka dari itu ketika barang datang mohon kakak sudah menyiapkan Uang COD nya.

Dan apabila kakak bepergian atau tidak dirumah mohon untuk Uang COD nya dititipkan ke orang yang ada di rumah ya kak agar ketika kurir datang, ada yang menerima paket tersebut.

*PENTING JIKA PAKET SUDAH DITERIMA*
- Kirimkan foto paket yang menampakkan No RESI dengan jelas 
*(Syarat klaim jika ada produk rusak/produk kurang dan (Syarat GRATIS/SUBSIDI ONGKIR)* dipesanan berikutnya.+B6

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 40gr : https://drive.google.com/drive/folders/1TpnKEIiAGTVx3yjxI_uFx4MH3lNhXL3Q?usp=sharing

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 15gr : https://drive.google.com/drive/folders/1XIvDDy-H3QDSEV8VCGnYyKh16vGChDDd?usp=sharing

Terimakasih banyak kak
Semoga berkah dan laris jualannya, aminn..🤲";

    } else {

$message = "Felicia ijin update resinya yaa kak 👏🏻

Pastikan memvideo unboxing saat membuka paket
Jika ada produk rusak / Lolos QC akan kita ganti dengan produk baru atau kita refund dana sesuai dengan produk yang rusak (dengan menyertakan bukti video unboxing)
.

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 40gr : https://drive.google.com/drive/folders/1TpnKEIiAGTVx3yjxI_uFx4MH3lNhXL3Q?usp=sharing

Berikut Link Untuk Bahan Promosi Produk Extra Ekonomis 15gr : https://drive.google.com/drive/folders/1XIvDDy-H3QDSEV8VCGnYyKh16vGChDDd?usp=sharing


Terimakasih atas kepercayaannya bermitra dengan Dhardhes food 🥰🙏🏻

Semoga kakak sekeluarga selalu diberikan kesehatan dan rejeki yang barokah
.
Dan semoga laris jualannya ya kak dan semoga berkah.. aminn 🤲";

    }

    // ======================
    // 6. GENERATE LINK WA
    // ======================
    return "https://wa.me/" . $phone . "?text=" . rawurlencode($message);

}

public function sendResi($id)
{
    $order = DB::table('online_orders as o')
        ->leftJoin('package_templates as t', 't.id', '=', 'o.package_template_id')
        ->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')
        ->where('o.id', $id)
        ->select(
            'o.*',
            't.name as package_name',
            'c.phone as customer_phone'
        )
        ->first();

    if (!$order) {
        return back()->with('error', 'Order tidak ditemukan');
    }

    $link = $this->generateWhatsAppLink($order);

    return redirect($link);
}

private function normalizePhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (substr($phone, 0, 1) == '0') {
        $phone = '62' . substr($phone, 1);
    }

    if (substr($phone, 0, 2) != '62') {
        $phone = '62' . $phone;
    }

    return $phone;
}

public function customersData()
{
    $customers = DB::table('customers as c')

        // ambil order terakhir tiap customer
        ->leftJoin('online_orders as o', function ($join) {
            $join->on('o.customer_id', '=', 'c.id')
                 ->whereRaw('o.id = (
                     SELECT MAX(id)
                     FROM online_orders
                     WHERE customer_id = c.id
                 )');
        })

        ->leftJoin('package_templates as t', 't.id', '=', 'o.package_template_id')

        ->select(
            'c.name',
            'c.phone',
            't.name as last_package',
            'o.order_date as last_order_date'
        )

        ->orderByDesc('o.order_date')
        ->get();

    return view('customers.index', compact('customers'));
}

}
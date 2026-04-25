<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesStockSession;
use App\Models\SalesStockSessionItem;
use App\Models\Product;
use App\Models\User;
use App\Models\StockMovement;
use App\Models\Kasbon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class SalesStockSessionController extends Controller
{
    public function index()
    {
        $sessions = SalesStockSession::with('user')
            ->orderByDesc('start_date')
            ->get();

        return view('sales_stock_sessions.index', compact('sessions'));
    }

    public function create()
    {
        if (!in_array(auth()->user()->role, ['admin','admin_gudang'])) {
            abort(403);
        }

        $users = User::whereIn('role', ['sales','admin'])->get();
        $products = Product::where('is_active', 1)
            ->select('products.*')
            ->selectRaw("
           (
                    SELECT COALESCE(SUM(
                    CASE
                    WHEN type = 'warehouse_in' THEN quantity
                    WHEN type = 'warehouse_out' THEN -quantity
                    WHEN type = 'adjustment' THEN quantity
                    ELSE 0
                    END
                ),0)
                    FROM stock_movements
                    WHERE stock_movements.product_id = products.id
                 ) as warehouse_stock
        ")
           ->get();

        return view('sales_stock_sessions.create', compact('users','products'));
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin','admin_gudang'])) {
            abort(403);
        }

        $request->validate([
        'user_id'    => 'required|exists:users,id',
        'product_id' => 'required|array',
        'qty'        => 'required|array',
        'start_date' => 'nullable|date',
    ]);

        $totalQty = collect($request->qty)->sum();

        if (count($request->product_id) !== count(array_unique($request->product_id))) {
        return back()
        ->withErrors('Produk tidak boleh duplikat')
        ->withInput();
    }

        if ($totalQty <= 0) {
        return back()
        ->withErrors('Minimal harus ada 1 produk dengan qty > 0')
        ->withInput();
    }

        if (SalesStockSession::hasOpenSession($request->user_id)) {
            return back()->withErrors('Sales masih memiliki session stok yang belum ditutup.');
        }

        try {

            DB::transaction(function () use ($request) {

            $session = SalesStockSession::create([
                'user_id'    => $request->user_id,
                'created_by' => auth()->id(),
                'start_date' => $request->start_date
                ? Carbon::parse($request->start_date)->format('Y-m-d 00:00:00')
                : now()->format('Y-m-d H:i:s'),
                'status'     => 'open',
            ]);

            $productIds = $request->product_id;
            $qtys       = $request->qty;

            for ($i = 0; $i < count($productIds); $i++) {

            $productId = $productIds[$i];
            $qty       = (int) $qtys[$i];

            if ($qty <= 0) continue;

                // CEK STOK GUDANG
            $warehouseStock = DB::table('stock_movements')
                ->select(DB::raw("
                SUM(
                CASE
                WHEN type = 'warehouse_in' THEN quantity
                WHEN type = 'warehouse_out' THEN -quantity
                WHEN type = 'adjustment' THEN quantity
                ELSE 0
                END
                ) as stock
        "))
    ->where('product_id', $productId)
    ->lockForUpdate()
    ->value('stock') ?? 0;

if ($qty > $warehouseStock) {

    $product = Product::find($productId);

    throw new \Exception(
        'Stok gudang tidak cukup untuk produk: '
        .$product->name
        .' (tersedia '.$warehouseStock.')'
    );
}

                SalesStockSessionItem::create([
                    'session_id' => $session->id,
                    'product_id' => $productId,
                    'opening_qty'=> $qty,
                ]);

                StockMovement::create([
                    'product_id'     => $productId,
                    'quantity'       => $qty,
                    'type'           => 'warehouse_out',
                    'reference_id'   => $session->id,
                    'reference_type' => 'sales_stock_session',
                    'session_id'     => $session->id,
                    'notes'          => 'Stok keluar dari gudang (ke sales)'
                ]);
            }

            $link = '/sales-stock-sessions/'.$session->id;

/* NOTIFIKASI KE SEMUA USER */

$users = User::all();

foreach($users as $user){

    Notification::create([
        'user_id' => $user->id,
        'type' => 'stock_session',
        'title' => 'Session Stok Dimulai',
        'message' => 'Session stok sales baru telah dibuat.',
        'link' => $link,
    ]);

}

        });

        } catch (\Exception $e) {

    return back()
        ->withErrors($e->getMessage())
        ->withInput();
}

        return redirect()
            ->route('sales-stock-sessions.index')
            ->with('success','Session stok sales berhasil dimulai.');
    }

    public function show($id)
    {

        Notification::where('link','/sales-stock-sessions/'.$id)
        ->where('user_id',auth()->id())
        ->update([
            'is_read' => 1
        ]);

        $session = SalesStockSession::with('user','items.product')
            ->findOrFail($id);

        $movements = StockMovement::with(['product','visit.store'])
            ->where('session_id', $session->id)
            ->orderBy('created_at')
            ->get();

        $runningBalance = [];

        foreach ($movements as $movement) {

            $productId = $movement->product_id;

            if (!isset($runningBalance[$productId])) {
                $runningBalance[$productId] = 0;
            }

            $runningBalance[$productId] += $movement->quantity;
            $movement->running_balance = $runningBalance[$productId];
        }

        return view('sales_stock_sessions.show', compact('session','movements'));
    }

    public function edit($id)
    {
        if (!in_array(auth()->user()->role, ['admin','admin_gudang'])) {
            abort(403);
        }

        $session = SalesStockSession::with('items.product')
            ->whereIn('status', ['minus','done'])
            ->findOrFail($id);

        return view('sales_stock_sessions.edit', compact('session'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['admin','admin_gudang'])) {
            abort(403);
        }

        $session = SalesStockSession::with('items.product')
            ->whereIn('status', ['minus','done'])
            ->findOrFail($id);

        DB::transaction(function () use ($request, $session) {

            Kasbon::where('reference_id', $session->id)
                ->where('reference_type', 'sales_stock_session')
                ->delete();

            $hasMinus = false;

            foreach ($session->items as $item) {

                $physicalRemaining = (int) ($request->physical_qty[$item->product_id] ?? 0);
                $systemRemaining   = $item->system_remaining_qty ?? 0;
                $difference        = $physicalRemaining - $systemRemaining;

                $item->update([
                    'physical_remaining_qty' => $physicalRemaining,
                    'difference_qty'         => $difference,
                ]);

                if ($difference < 0) {

                    $hasMinus = true;

                    $minusQty       = abs($difference);
                    $warehousePrice = (float) $item->product->warehouse_price;
                    $amountTotal    = $minusQty * $warehousePrice;

                    Kasbon::create([
                        'user_id'       => $session->user_id,
                        'created_by'    => auth()->id(),
                        'amount_total'  => $amountTotal,
                        'type'          => 'shortage',
                        'reference_id'  => $session->id,
                        'reference_type'=> 'sales_stock_session',
                        'description'   => 'Revisi stok minus session ID '.$session->id.' - '.$item->product->name,
                    ]);
                }
            }

            $session->update([
                'status' => $hasMinus ? 'minus' : 'done',
            ]);
        });

        return redirect()
            ->route('sales-stock-sessions.show', $session->id)
            ->with('success','Data fisik berhasil dikoreksi.');
    }

    public function closeForm($id)
    {
        if (!in_array(auth()->user()->role, ['admin','admin_gudang'])) {
            abort(403);
        }

        $session = SalesStockSession::with('items.product')
            ->where('status','open')
            ->findOrFail($id);

        foreach ($session->items as $item) {

            $systemRemaining = StockMovement::where('product_id', $item->product_id)
                ->where('session_id', $session->id)
                ->sum('quantity');

            $item->system_remaining_qty = $systemRemaining;
        }

        return view('sales_stock_sessions.close', compact('session'));
    }

    public function close(Request $request, $id)
{   
    $request->validate([
    'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120'
    ]);
    if (!in_array(auth()->user()->role, ['admin','admin_gudang'])) {
        abort(403);
    }

    $session = SalesStockSession::with('items.product')
        ->where('status','open')
        ->findOrFail($id);

    DB::transaction(function () use ($request, $session) {
        $photoPath = null;

    if ($request->hasFile('photo')) {
    $photoPath = $request->file('photo')->store('session_photos', 'public');
    }
        $hasMinus = false;

        foreach ($session->items as $item) {

            $systemRemaining = StockMovement::where('product_id', $item->product_id)
                ->where('session_id', $session->id)
                ->sum('quantity');

            $physicalRemaining = (int) ($request->physical_qty[$item->product_id] ?? 0);
            $damageQty         = (int) ($request->damage_qty[$item->product_id] ?? 0);

            $goodStock         = $physicalRemaining - $damageQty;
            $difference        = $physicalRemaining - $systemRemaining;

            $item->update([
                'system_remaining_qty'   => $systemRemaining,
                'physical_remaining_qty' => $physicalRemaining,
                'difference_qty'         => $difference,
            ]);

            // 🔴 MINUS → KASBON
            if ($difference < 0) {

                $hasMinus = true;

                $minusQty       = abs($difference);
                $warehousePrice = (float) $item->product->warehouse_price;
                $amountTotal    = $minusQty * $warehousePrice;

                Kasbon::create([
                    'user_id'        => $session->user_id,
                    'created_by'     => auth()->id(),
                    'amount_total'   => $amountTotal,
                    'type'           => 'shortage',
                    'reference_id'   => $session->id,
                    'reference_type' => 'sales_stock_session',
                    'description'    => 'Stok minus session ID '.$session->id.' - '.$item->product->name,
                ]);
            }

            // 🔧 BARANG RUSAK
            if ($damageQty > 0) {

                StockMovement::create([
                    'product_id'     => $item->product_id,
                    'quantity'       => -$damageQty,
                    'type'           => 'damage',
                    'reference_id'   => $session->id,
                    'reference_type' => 'sales_stock_session_close',
                    'session_id'     => $session->id,
                    'notes'          => 'Barang rusak (tidak kembali ke gudang)',
                ]);
            }

            // 🔄 RESET SALDO SESSION
            if ($goodStock > 0) {

                StockMovement::create([
                    'product_id'     => $item->product_id,
                    'quantity'       => $goodStock,
                    'type'           => 'warehouse_in',
                    'reference_id'   => $session->id,
                    'reference_type' => 'sales_stock_session_close',
                    'session_id'     => $session->id,
                    'notes'          => 'Stok kembali ke gudang',
                ]);
            }
        }

        $session->update([
       'status'   => $hasMinus ? 'minus' : 'done',
       'end_date' => now(),
       'photo'    => $photoPath
       ]);

        $link = '/sales-stock-sessions/'.$session->id;

/* NOTIFIKASI KE SEMUA USER */

$users = User::all();

foreach($users as $user){

    Notification::create([
        'user_id' => $user->id,
        'type' => 'stock_session_close',
        'title' => 'Session Stok Ditutup',
        'message' => 'Session stok sales telah ditutup.',
        'link' => $link,
    ]);

}

    });

    return redirect()
        ->route('sales-stock-sessions.index')
        ->with('success','Session berhasil ditutup & saldo direset.');
}

public function editOpening($id)
{
    // 🔐 HANYA ADMIN
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    // ambil session + item
    $session = SalesStockSession::with('items.product')
        ->where('status', 'open') // hanya boleh edit saat OPEN
        ->findOrFail($id);

    return view('sales_stock_sessions.edit_opening', compact('session'));
}

public function updateOpening(Request $request, $id)
{
    // 🔐 ADMIN ONLY
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    $session = SalesStockSession::with('items.product')
        ->where('status', 'open')
        ->findOrFail($id);

    DB::transaction(function () use ($request, $session) {

        foreach ($session->items as $item) {

            $productId = $item->product_id;

            $oldQty = (int) $item->opening_qty;
            $newQty = (int) ($request->opening[$productId] ?? $oldQty);

            // skip kalau tidak berubah
            if ($newQty === $oldQty) continue;

            $diff = $newQty - $oldQty;

            /*
            -----------------------------------------
            🔴 JIKA TAMBAH STOK KE SALES
            -----------------------------------------
            */
            if ($diff > 0) {

                // cek stok gudang
                $warehouseStock = DB::table('stock_movements')
                    ->select(DB::raw("
                        SUM(
                            CASE
                                WHEN type = 'warehouse_in' THEN quantity
                                WHEN type = 'warehouse_out' THEN -quantity
                                WHEN type = 'adjustment' THEN quantity
                                ELSE 0
                            END
                        ) as stock
                    "))
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->value('stock') ?? 0;

                if ($diff > $warehouseStock) {
                    throw new \Exception(
                        'Stok gudang tidak cukup untuk produk: ' . $item->product->name
                    );
                }

                // movement keluar gudang
                StockMovement::create([
                    'product_id'     => $productId,
                    'quantity'       => $diff,
                    'type'           => 'warehouse_out',
                    'reference_id'   => $session->id,
                    'reference_type' => 'sales_stock_session_adjustment',
                    'session_id'     => $session->id,
                    'notes'          => 'Penambahan opening stock (koreksi admin)',
                ]);
            }

            /*
            -----------------------------------------
            🔵 JIKA DIKURANGI (BALIK KE GUDANG)
            -----------------------------------------
            */
            if ($diff < 0) {

                $qtyBack = abs($diff);

                StockMovement::create([
                    'product_id'     => $productId,
                    'quantity'       => $qtyBack,
                    'type'           => 'warehouse_in',
                    'reference_id'   => $session->id,
                    'reference_type' => 'sales_stock_session_adjustment',
                    'session_id'     => $session->id,
                    'notes'          => 'Pengurangan opening stock (koreksi admin)',
                ]);
            }

            // update opening_qty
            $item->update([
                'opening_qty' => $newQty
            ]);
        }

    });

    return redirect()
        ->route('sales-stock-sessions.show', $session->id)
        ->with('success', 'Opening stock berhasil dikoreksi.');
}

public function reopen($id)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    $session = SalesStockSession::with('items.product')
        ->whereIn('status',['done','minus'])
        ->findOrFail($id);

    DB::transaction(function () use ($session) {

        /*
        -----------------------------------------
        1. HAPUS KASBON SESSION
        -----------------------------------------
        */

        Kasbon::where('reference_id',$session->id)
            ->where('reference_type','sales_stock_session')
            ->delete();

        /*
        -----------------------------------------
        2. HAPUS MOVEMENT CLOSING
        -----------------------------------------
        */

        StockMovement::where('session_id',$session->id)
            ->where('reference_type','sales_stock_session_close')
            ->delete();

        /*
        -----------------------------------------
        3. RESET DATA ITEM SESSION
        -----------------------------------------
        */

        foreach($session->items as $item){

            $item->update([
                'system_remaining_qty'   => null,
                'physical_remaining_qty' => null,
                'difference_qty'         => null,
            ]);

        }

        /*
        -----------------------------------------
        4. RESET SESSION
        -----------------------------------------
        */

        $session->update([
            'status'   => 'open',
            'end_date' => null
        ]);

        /*
        -----------------------------------------
        5. NOTIFIKASI
        -----------------------------------------
        */

        $link = '/sales-stock-sessions/'.$session->id;

        $users = User::all();

        foreach($users as $user){

            Notification::create([
                'user_id' => $user->id,
                'type' => 'stock_session_reopen',
                'title' => 'Session Stok Dibuka Kembali',
                'message' => 'Session stok sales telah dibuka kembali.',
                'link' => $link,
            ]);

        }

    });

    return redirect()
        ->route('sales-stock-sessions.show',$session->id)
        ->with('success','Session berhasil dibuka kembali.');
}

}
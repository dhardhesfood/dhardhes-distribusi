<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\WarehouseNote;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

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

        $stocks = DB::table('stock_movements')
            ->select(
                'products.id',
                'products.name',
                DB::raw("
                    SUM(
                        CASE
                            WHEN stock_movements.type = 'warehouse_in' THEN stock_movements.quantity
                            WHEN stock_movements.type = 'warehouse_out' THEN -stock_movements.quantity
                            WHEN stock_movements.type = 'adjustment' THEN stock_movements.quantity
                            ELSE 0
                        END
                    ) as stock
                ")
            )
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->whereIn('stock_movements.type', [
                'warehouse_in',
                'warehouse_out',
                'adjustment'
            ])
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();

        $notes = \App\Models\WarehouseNote::with('user')
           ->where('created_at', '>=', now()->subDays(7))
           ->orderBy('created_at', 'desc')
           ->get();

       return view('warehouse.index', compact('stocks','notes'));
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

                if ($qty <= 0) {
                    continue;
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
}
<?php

namespace App\Http\Controllers;

use App\Models\CashSale;
use App\Models\CashSaleItem;
use App\Models\Product;
use App\Models\SalesStockSession;
use App\Models\StockMovement;
use App\Models\Kasbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashSaleController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        $session = SalesStockSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return redirect()->route('dashboard')
                ->with('error', 'Tidak ada session stok aktif.');
        }

        $products = Product::where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('cash_sales.create', compact('products', 'session'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer',
            'paid_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        $user = Auth::user();

        $session = SalesStockSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return back()
                ->withInput()
                ->withErrors(['session' => 'Tidak ada session stok aktif.']);
        }

        $subtotal = 0;
        $feeTotal = 0;
        $itemsData = [];

        foreach ($request->items as $item) {

            $product = Product::findOrFail($item['product_id']);

            $qty = (int) $item['qty'];
            $bonusQty = isset($item['bonus_qty']) ? (int) $item['bonus_qty'] : 0;

            if ($qty <= 0) {
                return back()
                    ->withInput()
                    ->withErrors(['qty' => 'Qty tidak valid.']);
            }

            $movementSum = StockMovement::where('session_id', $session->id)
                ->where('product_id', $product->id)
                ->sum('quantity');

            $openingQty = DB::table('sales_stock_session_items')
                ->where('session_id', $session->id)
                ->where('product_id', $product->id)
                ->value('opening_qty');

            $availableStock = $openingQty + $movementSum;

            if ($qty + $bonusQty > $availableStock) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'stock' => 'Stok tidak mencukupi untuk produk: ' . $product->name
                    ]);
            }

            $price = $product->warehouse_price;
            $lineSubtotal = $qty * $price;
            $lineFee = $qty * $product->default_fee_nominal;

            $subtotal += $lineSubtotal;
            $feeTotal += $lineFee;

            $itemsData[] = [
                'product' => $product,
                'qty' => $qty,
                'bonus_qty' => $bonusQty,
                'price' => $price,
                'subtotal' => $lineSubtotal,
                'fee_nominal' => $product->default_fee_nominal,
                'hpp_snapshot' => $product->hpp ?? 0,
            ];
        }

        $discount = $request->discount ?? 0;
        $total = $subtotal - $discount;

        if ($request->paid_amount > $total) {
            return back()
                ->withInput()
                ->withErrors([
                    'paid_amount' => 'Nominal bayar tidak boleh lebih dari total.'
                ]);
        }

        return DB::transaction(function () use (
            $request,
            $user,
            $session,
            $subtotal,
            $feeTotal,
            $discount,
            $total,
            $itemsData
        ) {

            $kasbonAmount = $total - $request->paid_amount;

            $cashSale = CashSale::create([
                'user_id' => $user->id,
                'sale_date' => $request->sale_date,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'kasbon_amount' => $kasbonAmount,
                'fee_total' => $feeTotal,
                'status' => 'locked',
            ]);

            foreach ($itemsData as $data) {

                CashSaleItem::create([
                    'cash_sale_id' => $cashSale->id,
                    'product_id' => $data['product']->id,
                    'qty' => $data['qty'],
                    'bonus_qty' => $data['bonus_qty'],
                    'price' => $data['price'],
                    'subtotal' => $data['subtotal'],
                    'fee_nominal' => $data['fee_nominal'],
                    'hpp_snapshot' => $data['hpp_snapshot'],
                ]);

                StockMovement::create([
                    'product_id' => $data['product']->id,
                    'quantity' => -$data['qty'],
                    'type' => 'cash_sale',
                    'reference_id' => $cashSale->id,
                    'reference_type' => CashSale::class,
                    'session_id' => $session->id,
                    'notes' => 'Cash Sale',
                ]);

                if ($data['bonus_qty'] > 0) {
                    StockMovement::create([
                        'product_id' => $data['product']->id,
                        'quantity' => -$data['bonus_qty'],
                        'type' => 'bonus',
                        'reference_id' => $cashSale->id,
                        'reference_type' => CashSale::class,
                        'session_id' => $session->id,
                        'notes' => 'Bonus Cash Sale',
                    ]);
                }
            }

            if ($kasbonAmount > 0) {
                Kasbon::create([
                    'user_id' => $user->id,
                    'created_by' => $user->id,
                    'amount_total' => $kasbonAmount,
                    'amount_paid' => 0,
                    'type' => 'cash_sale',
                    'reference_id' => $cashSale->id,
                    'reference_type' => CashSale::class,
                    'description' => 'Kasbon dari Cash Sale',
                    'status' => 'open',
                ]);
            }

            return redirect()
                ->route('cash-sales.create')
                ->with('success', 'Cash sale berhasil disimpan.');
        });
    }
}
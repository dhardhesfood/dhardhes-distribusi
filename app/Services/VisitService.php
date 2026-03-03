<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\Product;
use App\Models\StorePrice;
use App\Models\VisitItem;
use App\Models\StoreStockMovement;
use App\Models\StockMovement;
use App\Models\ProductCostHistory;
use App\Models\SalesStockSession;
use App\Models\Receivable;
use Carbon\Carbon;

class VisitService
{
    /*
    |--------------------------------------------------------------------------
    | GENERATE VISIT ITEMS
    |--------------------------------------------------------------------------
    */

    public function generateVisitItems(Visit $visit)
    {
        if ($visit->items()->count() > 0) {
            throw new \Exception("Visit items sudah pernah digenerate.");
        }

        $visitDate = $visit->visit_date;

        $storePrices = StorePrice::where('store_id', $visit->store_id)->get();

        if ($storePrices->isEmpty()) {
            throw new \Exception("Toko tidak memiliki harga produk.");
        }

        foreach ($storePrices as $storePrice) {

            $product = Product::findOrFail($storePrice->product_id);

            $cost = ProductCostHistory::getCostForDate(
                $storePrice->product_id,
                $visitDate
            );

            if ($cost <= 0) {
                throw new \Exception(
                    "Produk '{$product->name}' belum memiliki HPP aktif pada tanggal visit."
                );
            }

            $systemStock = StoreStockMovement::getStoreProductStock(
                $visit->store_id,
                $storePrice->product_id
            );

            if ($systemStock <= 0) {
                continue;
            }

            VisitItem::create([
                'visit_id'            => $visit->id,
                'product_id'          => $storePrice->product_id,
                'initial_stock'       => $systemStock,
                'remaining_stock'     => 0,
                'sold_qty'            => 0,
                'return_qty'          => 0,
                'new_delivery_qty'    => 0,
                'bonus_qty'           => 0,
                'stock_reduction_qty' => 0,
                'price_snapshot'      => $storePrice->price,
                'fee_snapshot'        => $product->default_fee_nominal,
                'cost_snapshot'       => $cost,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS VISIT (SESSION AWARE)
    |--------------------------------------------------------------------------
    */

    public function processVisit(Visit $visit, $cashPaid = 0)
    {
        DB::transaction(function () use ($visit, $cashPaid) {

            if ($visit->status === 'completed') {
                throw new \Exception("Visit sudah diproses.");
            }

            $session = SalesStockSession::where('user_id', $visit->user_id)
                ->where('status', 'open')
                ->first();

            if (!$session) {
                throw new \Exception("Sales tidak memiliki session stok aktif.");
            }

            $sessionId = $session->id;

            $totalPenjualan = 0.0;
            $totalFee       = 0.0;
            $totalHpp       = 0.0;

            $transactionId = DB::table('sales_transactions')->insertGetId([
                'visit_id'         => $visit->id,
                'store_id'         => $visit->store_id,
                'user_id'          => $visit->user_id,
                'transaction_date' => $visit->visit_date,
                'total_amount'     => 0,
                'cash_paid'        => $cashPaid,
                'total_fee'        => 0,
                'total_hpp'        => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            foreach ($visit->items as $item) {

    $soldQty      = (int) $item->initial_stock - (int) $item->return_qty;
                $reductionQty = (int) $item->stock_reduction_qty;
                $newQty       = (int) $item->new_delivery_qty;

                if ($soldQty < 0) {
                    $soldQty = 0;
                }

                $item->update([
                    'sold_qty' => $soldQty
                ]);

                if ($soldQty > 0) {

                    StoreStockMovement::create([
                        'store_id'       => $visit->store_id,
                        'product_id'     => $item->product_id,
                        'quantity'       => -$soldQty,
                        'type'           => 'sale',
                        'reference_id'   => $visit->id,
                        'reference_type' => 'visit',
                        'notes'          => 'Barang terjual ke konsumen'
                    ]);
                }

                if ($newQty > 0) {

                    StoreStockMovement::create([
                        'store_id'       => $visit->store_id,
                        'product_id'     => $item->product_id,
                        'quantity'       => $newQty,
                        'type'           => 'send_from_sales',
                        'reference_id'   => $visit->id,
                        'reference_type' => 'visit',
                        'notes'          => 'Penambahan stok dari sales'
                    ]);

                    StockMovement::create([
                        'product_id'     => $item->product_id,
                        'quantity'       => -$newQty,
                        'type'           => 'send_to_store',
                        'reference_id'   => $visit->id,
                        'reference_type' => 'visit',
                        'session_id'     => $sessionId,
                        'notes'          => 'Kirim ke toko'
                    ]);
                }

                if ($reductionQty > 0) {

                    StoreStockMovement::create([
                        'store_id'       => $visit->store_id,
                        'product_id'     => $item->product_id,
                        'quantity'       => -$reductionQty,
                        'type'           => 'return_to_sales',
                        'reference_id'   => $visit->id,
                        'reference_type' => 'visit',
                        'notes'          => 'Penarikan barang ke sales'
                    ]);

                    StockMovement::create([
                        'product_id'     => $item->product_id,
                        'quantity'       => $reductionQty,
                        'type'           => 'return_from_store',
                        'reference_id'   => $visit->id,
                        'reference_type' => 'visit',
                        'session_id'     => $sessionId,
                        'notes'          => 'Barang ditarik dari toko'
                    ]);
                }

                if ($soldQty <= 0) {
                    continue;
                }

                $price = (float) $item->price_snapshot;
                $fee   = (float) $item->fee_snapshot;
                $cost  = (float) $item->cost_snapshot;

                $subtotalAmount = $soldQty * $price;
                $subtotalFee    = $soldQty * $fee;
                $subtotalHpp    = $soldQty * $cost;

                DB::table('sales_transaction_items')->insert([
                    'sales_transaction_id' => $transactionId,
                    'product_id'           => $item->product_id,
                    'quantity_sold'        => $soldQty,
                    'price_snapshot'       => $price,
                    'fee_snapshot'         => $fee,
                    'cost_snapshot'        => $cost,
                    'subtotal_amount'      => $subtotalAmount,
                    'subtotal_fee'         => $subtotalFee,
                    'subtotal_hpp'         => $subtotalHpp,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $totalPenjualan += $subtotalAmount;
                $totalFee       += $subtotalFee;
                $totalHpp       += $subtotalHpp;
            }

            foreach ($visit->bonuses as $bonus) {

                if ($bonus->qty > 0) {

                    StockMovement::create([
                        'product_id'     => $bonus->product_id,
                        'quantity'       => -$bonus->qty,
                        'type'           => 'bonus',
                        'reference_id'   => $visit->id,
                        'reference_type' => 'visit',
                        'session_id'     => $sessionId,
                        'notes'          => 'Bonus gratis ke toko'
                    ]);
                }
            }

            if ($cashPaid > $totalPenjualan) {
                throw new \Exception("Jumlah bayar melebihi total tagihan.");
            }

            DB::table('sales_transactions')
                ->where('id', $transactionId)
                ->update([
                    'total_amount' => $totalPenjualan,
                    'total_fee'    => $totalFee,
                    'total_hpp'    => $totalHpp,
                    'cash_paid'    => $cashPaid,
                ]);

            $remaining = $totalPenjualan - $cashPaid;

            $existingReceivable = Receivable::where('sales_transaction_id', $transactionId)->first();

            if ($remaining > 0) {

                $dueDate = Carbon::parse($visit->visit_date)->addDays(30);

                if (!$existingReceivable) {

                    Receivable::create([
                        'sales_transaction_id' => $transactionId,
                        'store_id'             => $visit->store_id,
                        'total_amount'         => $remaining,
                        'paid_amount'          => 0,
                        'remaining_amount'     => $remaining,
                        'status'               => 'unpaid',
                        'due_date'             => $dueDate,
                    ]);

                } else {

                    $existingReceivable->update([
                        'total_amount'     => $remaining,
                        'remaining_amount' => $remaining,
                        'status'           => 'unpaid',
                        'due_date'         => $dueDate,
                    ]);
                }

            } else {

                if ($existingReceivable) {
                    $existingReceivable->delete();
                }
            }

            $visit->update([
                'status' => 'completed',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK VISIT (ADMIN SAFE REOPEN)
    |--------------------------------------------------------------------------
    */

    public function rollbackVisit(Visit $visit)
    {
        DB::transaction(function () use ($visit) {

            if ($visit->status !== 'completed' && $visit->status !== 'approved') {
                throw new \Exception("Visit belum bisa di-rollback.");
            }

            $transaction = $visit->salesTransaction;

            if (!$transaction) {
                throw new \Exception("Sales transaction tidak ditemukan.");
            }

            // Cek apakah sudah ada pembayaran piutang
            $receivable = \App\Models\Receivable::where(
                'sales_transaction_id',
                $transaction->id
            )->first();

            if ($receivable && $receivable->payments()->exists()) {
                throw new \Exception("Visit tidak bisa diubah karena sudah ada pembayaran piutang.");
            }

            /*
            |-----------------------------------------
            | Hapus ledger stok toko
            |-----------------------------------------
            */
            StoreStockMovement::where('reference_id', $visit->id)
                ->where('reference_type', 'visit')
                ->delete();

            /*
            |-----------------------------------------
            | Hapus ledger stok sales
            |-----------------------------------------
            */
            StockMovement::where('reference_id', $visit->id)
                ->where('reference_type', 'visit')
                ->delete();

            /*
            |-----------------------------------------
            | Hapus sales transaction
            | (items & receivable cascade otomatis)
            |-----------------------------------------
            */
            $transaction->delete();

            /*
            |-----------------------------------------
            | Reset visit item
            |-----------------------------------------
            */
            foreach ($visit->items as $item) {
                $item->update([
                    'sold_qty' => 0,
                ]);
            }

            /*
            |-----------------------------------------
            | Kembalikan status visit
            |-----------------------------------------
            */
            $visit->update([
                'status' => 'draft',
                'approved_at' => null,
                'approved_by' => null,
            ]);
        });    
    }

}

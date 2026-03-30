<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StoreStockMovement;
use App\Models\Product;
use App\Models\Visit;
use App\Models\Store;
use Carbon\Carbon;

class StockOpnameService
{
    public function process($storeId, $createdBy, $notes, $actualStocks, $visitDate)
    {

        return DB::transaction(function () use ($storeId, $createdBy, $notes, $actualStocks, $visitDate) {

            // 1️⃣ Buat header opname
            $opname = StockOpname::create([
                'store_id'  => $storeId,
                'created_by'=> $createdBy,
                'notes'     => $notes,
                'visit_date' => $visitDate
            ]);

            // 2️⃣ Ambil semua produk (TERMASUK yang belum pernah masuk toko)
            $products = Product::orderBy('name')->get();

            foreach ($products as $product) {

                $systemStock = StoreStockMovement::getStoreProductStock($storeId, $product->id);
                $actualStock = (int) ($actualStocks[$product->id] ?? 0);
                $difference  = $actualStock - $systemStock;

                // 3️⃣ Simpan detail opname
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id'      => $product->id,
                    'system_stock'    => $systemStock,
                    'actual_stock'    => $actualStock,
                    'difference'      => $difference,
                ]);

                // 4️⃣ Jika ada selisih → buat ledger adjustment
                if ($difference != 0) {

                    StoreStockMovement::create([
                        'store_id'       => $storeId,
                        'product_id'     => $product->id,
                        'quantity'       => $difference,
                        'type'           => 'adjustment',
                        'reference_id'   => $opname->id,
                        'reference_type' => 'stock_opname',
                        'notes'          => 'Stock Opname Adjustment',
                    ]);
                }
            }

            $store = Store::find($storeId);

            $visitDateParsed = Carbon::parse($visitDate);

            $nextVisit = $visitDateParsed->copy()->addDays($store->visit_interval_days);

Visit::create([
    'store_id' => $storeId,
    'user_id' => $createdBy,
    'visit_date' => $visitDateParsed,
    'next_visit_date' => $nextVisit,
    'status' => 'approved'
]);

$store->update([
    'last_visit_date' => $visitDateParsed
]);

return $opname;
        });
    }
}
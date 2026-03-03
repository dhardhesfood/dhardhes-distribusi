<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StoreStockMovement;
use App\Models\Product;

class StockOpnameService
{
    public function process($storeId, $createdBy, $notes, $actualStocks)
    {

        return DB::transaction(function () use ($storeId, $createdBy, $notes, $actualStocks) {

            // 1️⃣ Buat header opname
            $opname = StockOpname::create([
                'store_id'  => $storeId,
                'created_by'=> $createdBy,
                'notes'     => $notes,
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

            return $opname;
        });
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;

class WarehouseAdjustmentController extends Controller
{

    public function create()
    {
        if(auth()->user()->role !== 'admin'){
            abort(403);
        }

        $products = DB::table('stock_movements')
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
            ->groupBy('products.id','products.name')
            ->orderBy('products.name')
            ->get();

        return view('warehouse.adjustment', compact('products'));
    }


    public function store(Request $request)
    {
        if(auth()->user()->role !== 'admin'){
            abort(403);
        }

        $realStocks = $request->input('real_stock', []);

        DB::transaction(function () use ($realStocks) {

            foreach($realStocks as $productId => $realStock){

                $realStock = (int) $realStock;

                $systemStock = DB::table('stock_movements')
                    ->where('product_id',$productId)
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
                    ->value('stock') ?? 0;

                $difference = $realStock - $systemStock;

                if($difference != 0){

                    StockMovement::create([
                            'product_id'     => $productId,
                            'quantity'       => $difference,
                            'type'           => 'adjustment',
                            'reference_type' => 'warehouse_adjustment',
                            'notes'          => 'Penyesuaian stok gudang',
                            'created_by'     => auth()->id()
                   ]);

                }

            }

        });

        return redirect()
            ->route('warehouse.index')
            ->with('success','Penyesuaian stok berhasil disimpan.');
    }

}
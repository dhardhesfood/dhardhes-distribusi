<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;

class SalesStockController extends Controller
{
    public function index()
    {
        $stocks = DB::table('products as p')
            ->leftJoin('stock_movements as sm', 'sm.product_id', '=', 'p.id')
            ->select(
                'p.id',
                'p.name',
                'p.warehouse_price',
                DB::raw('COALESCE(SUM(sm.quantity), 0) as total_stock'),
                DB::raw('COUNT(sm.id) as total_movements')
            )
            ->groupBy('p.id', 'p.name', 'p.warehouse_price')
            ->orderBy('p.name')
            ->get()
            ->map(function ($row) {

                $estimatedValue = $row->total_stock * $row->warehouse_price;

                return [
                    'id'              => $row->id,
                    'name'            => $row->name,
                    'warehouse_price' => $row->warehouse_price,
                    'total_stock'     => (int) $row->total_stock,
                    'total_movements' => (int) $row->total_movements,
                    'estimated_value' => $estimatedValue,
                    'is_minus'        => $row->total_stock < 0,
                ];
            });

        return view('sales_stock.index', compact('stocks'));
    }

    public function show($productId)
    {
        $product = Product::findOrFail($productId);

        $movements = StockMovement::where('product_id', $productId)
            ->orderBy('created_at')
            ->get();

        $runningBalance = 0;

        $movements = $movements->map(function ($movement) use (&$runningBalance) {

            $runningBalance += $movement->quantity;

            return [
                'date'            => $movement->created_at,
                'type'            => $movement->type,
                'quantity'        => $movement->quantity,
                'reference_type'  => $movement->reference_type,
                'reference_id'    => $movement->reference_id,
                'running_balance' => $runningBalance,
            ];
        });

        return view('sales_stock.show', compact('product', 'movements', 'runningBalance'));
    }

    public function createWarehouseIn()
    {
        $products = Product::whereNull('deleted_at')->get();

        return view('sales_stock.warehouse_in', compact('products'));
    }

    public function storeWarehouseIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string'
        ]);

        StockMovement::create([
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity,
            'type'           => 'warehouse_in',
            'reference_id'   => null,
            'reference_type' => 'manual_warehouse_in',
            'notes'          => $request->notes,
        ]);

        return redirect()->route('sales.stock')
            ->with('success', 'Stok sales berhasil ditambahkan.');
    }
}
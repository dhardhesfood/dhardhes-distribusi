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

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $orderId = DB::table('online_orders')->insertGetId([
                'customer_name' => $request->customer_name,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->items as $item) {

                if (($item['qty'] ?? 0) <= 0) {
                    continue;
                }

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

        return redirect('/online-orders/create')
            ->with('success', 'Order berhasil disimpan');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageTemplateController extends Controller
{
    public function index()
    {
        $templates = DB::table('package_templates')
            ->orderBy('id', 'desc')
            ->get();

        return view('package_templates.index', compact('templates'));
    }

    public function create()
    {
        $products = DB::table('products')
            ->where('channel_type', 'online')
            ->orderBy('name')
            ->get();

        return view('package_templates.create', compact('products'));
    }

    public function getVariants($productId)
    {
        $variants = DB::table('product_variants')
            ->where('product_id', $productId)
            ->where('is_active', 1)
            ->get();

        return response()->json($variants);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $templateId = DB::table('package_templates')->insertGetId([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->items as $item) {

                if (($item['qty'] ?? 0) <= 0) continue;

                DB::table('package_template_items')->insert([
                    'package_template_id' => $templateId,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        });

        return redirect('/package-templates')->with('success', 'Paket berhasil dibuat');
    }

    public function destroy($id)
    {
        DB::table('package_templates')->where('id', $id)->delete();

        return back()->with('success', 'Paket dihapus');
    }
}
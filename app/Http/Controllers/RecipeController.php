<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class RecipeController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active',1)->get();

        $productId = request('product_id') ?? $products->first()->id;

        $variants = DB::table('product_variants')
            ->where('is_active',1)
            ->where('product_id', $productId)
            ->get();

        $recipes = DB::table('product_pack_recipes')
            ->where('is_active',1)
            ->pluck('id','product_id')
            ->toArray();

        $recipeItems = DB::table('product_pack_recipe_items')
            ->get()
            ->groupBy('recipe_id');

        $variantNames = DB::table('product_variants')
            ->pluck('name','id')
            ->toArray();

        return view('recipes.index', compact(
            'products',
            'variants',
            'recipes',
            'recipeItems',
            'variantNames',
            'productId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'variants' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {

            // nonaktifkan lama
            DB::table('product_pack_recipes')
                ->where('product_id',$request->product_id)
                ->update(['is_active'=>0]);

            // buat baru
            $recipeId = DB::table('product_pack_recipes')->insertGetId([
                'product_id'=>$request->product_id,
                'is_active'=>1,
                'created_at'=>now(),
                'updated_at'=>now()
            ]);

            foreach($request->variants as $variant){

                if(!isset($variant['qty']) || $variant['qty'] <= 0){
                    continue;
                }

                DB::table('product_pack_recipe_items')->insert([
                    'recipe_id'=>$recipeId,
                    'product_variant_id'=>$variant['id'],
                    'qty_per_pack'=>$variant['qty'],
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);
            }

        });

        return redirect('/recipes?product_id='.$request->product_id)
            ->with('success','Recipe berhasil disimpan');
    }
}
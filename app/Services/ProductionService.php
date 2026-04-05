<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\ProductCostHistory;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    /**
     * Hitung produksi + HPP
     */
    public function calculate($productId, $outputGram, $laborPercentage)
    {
        $product = Product::with('recipeItems.ingredient')->findOrFail($productId);

        $totalMaterialCost = 0;
        $materialDetails = [];

        foreach ($product->recipeItems as $item) {
            $ingredient = $item->ingredient;

            // kebutuhan bahan
            $neededGram = $item->qty_per_gram_output * $outputGram;

            // biaya bahan
            $cost = $neededGram * $ingredient->price_per_gram;

            $totalMaterialCost += $cost;

            $materialDetails[] = [
                'ingredient_name' => $ingredient->name,
                'needed_gram' => $neededGram,
                'price_per_gram' => $ingredient->price_per_gram,
                'cost' => $cost,
            ];
        }

        // 🔥 ongkos produksi (1.8 / gram default)
        $laborRate = 1.8;
        $totalLaborCost = $outputGram * $laborRate * $laborPercentage;

        // 🔥 HPP
        $hppPerGram = ($totalMaterialCost + $totalLaborCost) / $outputGram;

        return [
            'product' => $product->name,
            'output_gram' => $outputGram,

            'materials' => $materialDetails,
            'total_material_cost' => $totalMaterialCost,

            'labor_rate' => $laborRate,
            'labor_percentage' => $laborPercentage,
            'total_labor_cost' => $totalLaborCost,

            'hpp_per_gram' => $hppPerGram,
            'total_cost' => $totalMaterialCost + $totalLaborCost,
        ];
    }

    /**
     * Simpan hasil produksi + update HPP
     */
    public function store($productId, $outputGram, $laborPercentage, $userId, $photo = null)
    {
        return DB::transaction(function () use ($productId, $outputGram, $laborPercentage, $userId, $photo) {

            // hitung dulu
            $result = $this->calculate($productId, $outputGram, $laborPercentage);

            // 🔥 simpan ke production_runs
            $production = ProductionRun::create([
                'product_id' => $productId,
                'output_gram' => $outputGram,
                'total_material_cost' => $result['total_material_cost'],
                'labor_rate_per_gram' => $result['labor_rate'],
                'labor_percentage' => $result['labor_percentage'],
                'total_labor_cost' => $result['total_labor_cost'],
                'hpp_per_gram' => $result['hpp_per_gram'],
                'created_by' => $userId,
                'photo' => $photo,
            ]);

            return $production;
        });
    }
}
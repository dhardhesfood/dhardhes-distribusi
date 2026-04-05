<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'price_per_unit',
        'unit',
        'price_per_gram'
    ];

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
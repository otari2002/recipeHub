<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class RecipeAPI
{
    private static $apiKey;
    public static function init()
    {
        self::$apiKey = env('SPOONACULAR_API', '');
    }

    public static function randomRecipes($num=10){
        $recipes = Http::withHeader('x-api-key',self::$apiKey)
        ->get('https://api.spoonacular.com/recipes/random',
        [
            'limitLicense' => true,
            'number' => $num
        ]);
        return json_decode($recipes);
    }

    public static function getRecipe($id){
        $recipe = Http::withHeader('x-api-key',self::$apiKey)
        ->get('https://api.spoonacular.com/recipes/'.$id.'/information');
        return json_decode($recipe);
    }

    public static function similarRecipes($id, $num=10){
        $recipes = Http::withHeader('x-api-key',self::$apiKey)
        ->get('https://api.spoonacular.com/recipes/'.$id.'/similar',
        [
            'limitLicense' => true,
            'number' => $num
        ]);
        return json_decode($recipes);
    }

}

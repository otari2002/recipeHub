<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class RecipeAPI
{
    private static $apiKey;
    public static function init()
    {
        self::$apiKey = 'dada6460d0d04992ba1294662629d53e';
    }

    public static function randomRecipes($num=10){
        $recipes = Http::withHeader('x-api-key','dada6460d0d04992ba1294662629d53e')
        ->get('https://api.spoonacular.com/recipes/random',
        [
            'limitLicense' => true,
            'number' => $num
        ]);
        return json_decode($recipes);
    }

}

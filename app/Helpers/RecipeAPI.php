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

    private static function dataExtract($data){
        $name = $data['title'];
        $img = $data['image'];
        $time = $data['readyInMinutes'];
        $dishTypes = $data['dishTypes'];
        $ingredients = [];
        foreach ($data['extendedIngredients'] as $ingredient) {
            $ingredientData = [
                'titleIngredient' => $ingredient['nameClean'],
                'imgIngredient' => 'https://img.spoonacular.com/ingredients_100x100/' . $ingredient['image'],
                'metric' => sprintf("%.2f", $ingredient['measures']['metric']['amount'])." ".$ingredient['measures']['metric']['unitShort']
            ];
            $ingredients[] = $ingredientData;
        }

        $extractedData = [
            'name' => $name,
            'img' => $img,
            'time' => $time,
            'dishTypes' => $dishTypes,
            'ingredients' => $ingredients
        ];
        return $extractedData;
    }

    public static function getRecipe($id){
        try {
            $recipe = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/'.$id.'/information');
            $data = json_decode($recipe, true);
            return self::dataExtract($data);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not get recipe from API'
            ]);
        }
    }

    public static function randomRecipes($num, $try=1){
        try {
            if($try > 3){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get recipes from API after 3 tries'
                ]);
            }
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/random',
            [
                'limitLicense' => true,
                'number' => $num
            ]);
            $data = json_decode($recipes, true);
            $recipes = [];
            foreach ($data['recipes'] as $recipe) {
                $extractedData = self::dataExtract($recipe);
                $recipes[] = $extractedData;
            }
            return $recipes;
        } catch (\Throwable $th) {
            return self::randomRecipes($num, $try+1);
        }
        
    }

    public static function similarRecipes($id, $num, $try=1){
        try {
            if($try > 3){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get similar recipes from API after 3 tries'
                ]);
            }
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/'.$id.'/similar',
            [
                'limitLicense' => true,
                'number' => $num
            ]);
            return json_decode($recipes);
        } catch (\Throwable $th) {
            return self::similarRecipes($id, $num, $try+1);
        }
        
    }

}

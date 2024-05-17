<?php

namespace App\Helpers;

use App\Models\SavedRecipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class RecipeAPI
{
    private static $apiKey;
    public static function init()
    {
        self::$apiKey = env('SPOONACULAR_API', '');
    }
    private static function isRecipeSaved($idRecipe){
        $idUser = Auth::user()->idUser;
        return SavedRecipe::where(['idRecipe' => $idRecipe, 'idUser' => $idUser])->first();
    }

    private static function dataExtract($data, $isSaved = false){
        $id = $data['id'];
        if($isSaved){
            $saved = true;
        }else{
            $saved = self::isRecipeSaved($id);
        }
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
            'id' => $id,
            'saved' => $saved ? true : false,
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
        } catch (Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not get recipe from API',
                'error' => $th->getMessage() ?? 'Unknown error'
            ]);
        }
    }

    public static function getRecipesBulk($ids, $isSaved=false, $try=1, $th=null){
        try {
            if($try > 2){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get recipes from API after 2 tries',
                    'error' => $th->getMessage() ?? 'Unknown error'
                ]);
            }
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/informationBulk',
            [
                'ids' => $ids,
                'addRecipeInformation' => "true",
                'fillIngredients' => "true",
                'limitLicense' => "true",
            ]);
            $data = json_decode($recipes, true);
            $recipes = [];
            foreach ($data as $recipe) {
                $extractedData = self::dataExtract($recipe, $isSaved);
                $recipes[] = $extractedData;
            }
            return $recipes;
        } catch (Throwable $th) {
            return self::getRecipesBulk($ids, $isSaved, $try+1, $th);
        }
    }

    public static function recipesByType($type, $page, $num = 10, $try=1, $th=null){
        try {
            if($try > 2){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get recipes from API after 2 tries',
                    'error' => $th->getMessage() ?? 'Unknown error'
                ]);
            }
            $offset = ($page - 1) * $num;
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/complexSearch',
            [
                'addRecipeInformation' => "true",
                'fillIngredients' => "true",
                'limitLicense' => "true",
                'number' => $num,
                'offset' => $offset,
                'type' => $type,
            ]);
            $data = json_decode($recipes, true);
            $recipes = [];
            foreach ($data['results'] as $recipe) {
                $extractedData = self::dataExtract($recipe);
                $recipes[] = $extractedData;
            }
            return $recipes;
        } catch (Throwable $th) {
            return self::recipesByType($type,$page,$num,$try+1, $th);
        }
    }

    public static function recipesByName($name, $page, $num = 10, $try=1, $th=null){
        try {
            if($try > 2){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get recipes from API after 2 tries',
                    'error' => $th->getMessage() ?? 'Unknown error'
                ]);
            }
            $offset = ($page - 1) * $num;
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/complexSearch',
            [
                'addRecipeInformation' => "true",
                'fillIngredients' => "true",
                'limitLicense' => "true",
                'number' => $num,
                'offset' => $offset,
                'query' => $name
            ]);
            $data = json_decode($recipes, true);
            $recipes = [];
            foreach ($data['results'] as $recipe) {
                $extractedData = self::dataExtract($recipe);
                $recipes[] = $extractedData;
            }
            return $recipes;
        } catch (Throwable $th) {
            return self::recipesByName($name,$page,$num,$try+1, $th);
        }
    }

    public static function randomRecipes($num, $try=1, $th=null){
        try {
            if($try > 2){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get recipes from API after 2 tries',
                    'error' => $th->getMessage() ?? 'Unknown error'
                ]);
            }
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/random',
            [
                'limitLicense' => "true",
                'number' => $num
            ]);
            $data = json_decode($recipes, true);
            $recipes = [];
            foreach ($data['recipes'] as $recipe) {
                $extractedData = self::dataExtract($recipe);
                $recipes[] = $extractedData;
            }
            return $recipes;
        } catch (Throwable $th) {
            return self::randomRecipes($num, $try+1, $th);
        }
        
    }

    public static function similarRecipes($id, $num, $try=1, $th=null){
        try {
            if($try > 2){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not get similar recipes from API after 2 tries',
                    'error' => $th->getMessage() ?? 'Unknown error'
                ]);
            }
            $recipes = Http::withHeader('x-api-key',self::$apiKey)
            ->get('https://api.spoonacular.com/recipes/'.$id.'/similar',
            [
                'limitLicense' => "true",
                'number' => $num
            ]);
            return json_decode($recipes);
        } catch (Throwable $th) {
            return self::similarRecipes($id, $num, $try+1, $th);
        }
        
    }

}

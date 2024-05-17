<?php

namespace App\Http\Controllers;

use App\Helpers\RecipeAPI;
use App\Models\RecipeLike;
use App\Models\SavedRecipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    // Get current user's saved recipes
    public function getSavedRecipes()
    {
        $user = Auth::user();
        $ids = $user->savedRecipes->pluck('idRecipe')->implode(',');
        return RecipeAPI::getRecipesBulk($ids, true);
    }
    public function getRecipe($id){
        return RecipeAPI::getRecipe($id);
    }
    public function getRandomRecipes(Request $request){
        $num = intval($request->query('num', 10));
        return RecipeAPI::randomRecipes($num);
    }
    public function getSimilarRecipes($id, Request $request){
        $num = intval($request->query('num', 10));
        return RecipeAPI::similarRecipes($id,$num);
    }
    public function getRecipesByType(Request $request){
        $page = intval($request->query('page', 1));
        $num = intval($request->query('num', 10));
        $type = $request->query('type', 'main course');
        return RecipeAPI::recipesByType($type,$page,$num);
    }
    public function getRecipesByName(Request $request){
        $page = intval($request->query('page', 1));
        $num = intval($request->query('num', 10));
        $name = $request->query('name', '');
        return RecipeAPI::recipesByName($name,$page,$num);
    }
    public function saveRecipe(Request $request)
    {
        $user = Auth::user();
        $formFields = $request->only('idRecipe');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idRecipe' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        $idRecipe = $formFields['idRecipe'];

        $recipeSaved = SavedRecipe::where(['idRecipe' => $idRecipe, 'idUser' => $user->idUser])->first();

        // Check if recipe was already saved
        if ($recipeSaved) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe already saved by user',
                'saved' => true
            ]);
        }

        // Create a record for saved recipe
        $recipeToSave = new SavedRecipe();
        $recipeToSave->idRecipe = $idRecipe;
        $recipeToSave->idUser = $user->idUser;
        $recipeToSave->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe saved successfully',
            'saved' => true
        ]);
    }

    public function unsaveRecipe(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only('idRecipe');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idRecipe' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        $idRecipe = $formFields['idRecipe'];

        $savedRecipe = SavedRecipe::where(['idRecipe' => $idRecipe, 'idUser' => $user->idUser])->first();

        // Check if recipe was saved by the user and delete it the save record
        if ($savedRecipe) {
            $savedRecipe->delete();
            return response()->json([
                'status' => 'success',
                'message' => "Recipe removed from Saved Recipes",
                'saved' => false
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "User didn't save this recipe"
            ]);
        }
    }

    public function likeRecipe(Request $request)
    {
        $user = Auth::user();

        $formFields = $request->only('idRecipe');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['idRecipe' => ['required', 'numeric']]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        $idRecipe = $formFields['idRecipe'];
        $likeExists = RecipeLike::where(["idRecipe" => $idRecipe, "idUser" => $user->idUser])->get()->first();
        
        // Check if recipe was already liked by the user
        if ($likeExists) {
               if ($likeExists->delete()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Recipe unliked succefully',
                    'like' => false
                ]);
            }
        }

        // Create a record for the recipe like
        $recipeLike = new RecipeLike();
        $recipeLike->idUser = $user->idUser;
        $recipeLike->idRecipe = $idRecipe;
        $recipeLike->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe liked successfully',
            'like' => true
        ]);
    }

}

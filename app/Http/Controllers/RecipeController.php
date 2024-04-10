<?php

namespace App\Http\Controllers;

use App\Helpers\RecipeAPI;
use App\Models\RecipeLike;
use Illuminate\Support\Facades\Http;
use App\Models\SavedRecipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    public function getRecipe($id){
        $recipe = Http::get('https://api.spoonacular.com/recipes/'.$id.'/information', 
        []);
        return response()->json([
            'recipe' => $recipe
        ]);
    }
    public function getRandomRecipes(){
        $recipes = RecipeAPI::randomRecipes(1);
        return $recipes;
    }
    public function savePost(Request $request)
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

        // Check if post was already saved
        if ($recipeSaved) {
            return $this->unsavePost($request);
        }

        // Create a record for saved post
        $recipeToSave = new SavedRecipe();
        $recipeToSave->idRecipe = $idRecipe;
        $recipeToSave->idUser = $user->idUser;
        $recipeToSave->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post saved successfully',
            'saved' => true
        ]);
    }

    public function unsavePost(Request $request)
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

        // Check if post was saved by the user and delete it the save record
        if ($savedRecipe) {
            $savedRecipe->delete();
            return response()->json([
                'status' => 'success',
                'message' => "Post removed from Saved Posts",
                'saved' => false
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "User didn't save this post"
            ]);
        }
    }

    public function likePost(Request $request)
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
        
        // Check if post was already liked by the user
        if ($likeExists) {
               if ($likeExists->delete()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Post unliked succefully',
                    'like' => false
                ]);
            }
        }

        // Create a record for the post like
        $recipeLike = new RecipeLike();
        $recipeLike->idUser = $user->idUser;
        $recipeLike->idRecipe = $idRecipe;
        $recipeLike->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post liked successfully',
            'like' => true
        ]);
    }

}

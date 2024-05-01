<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserLoggedIn;

use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginOtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\UploadImageController;

// Register Login (fullName, username?, email, password)
Route::post('register', [RegisterController::class, 'register']);
Route::get('test', [RegisterController::class, 'index']);

// User Login (email, password)
Route::post('login', [LoginController::class, 'login']);

// Social Login (token)
Route::post('auth/google', [SocialiteController::class, 'requestTokenGoogle']);
Route::post('auth/facebook', [SocialiteController::class, 'requestTokenFacebook']);

// Login via OTP
// Sending otp code
Route::post('login/email', [LoginOtpController::class, 'requestEmail']); // (email)

// Verify the code and login (code, email)
Route::post('login/verify-otp', [LoginOtpController::class, 'processLogin']);

//Password Reset
// Sending otp code
Route::post('forgot-password/email', [ResetPasswordController::class, 'requestEmail']); // (email)
// Confirm otp code (code, email)
Route::post('reset-password/confirm-otp', [ResetPasswordController::class, 'confirmOTP']);
// Verify the code and reset (code, email, password, password_confirmation)
Route::post('reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::get('random-recipes', [RecipeController::class, 'getRandomRecipes']);
Route::get('recipe/{id}', [RecipeController::class, 'getRecipe']);
Route::get('similar-recipes/{id}', [RecipeController::class, 'getSimilarRecipes']);
Route::get('recipe-search', [RecipeController::class, 'getRecipesByName']);
Route::get('recipes-by-type', [RecipeController::class, 'getRecipesByType']);

Route::post('image-text', [UploadImageController::class, 'upload']);
// Group of routes only available to logged in users
Route::middleware([EnsureUserLoggedIn::class])->group(function () {
    // Logout
    Route::get('logout', [LoginController::class, 'logout']);
    // User Token Refresh
    Route::get('refresh', [LoginController::class, 'refresh']);

    // Email Adress Verification
    Route::get('email/send', [EmailVerificationController::class, 'sendOTP']);
    Route::post('email/verify', [EmailVerificationController::class, 'verify']); //(code)

    Route::post('recipes/save', [RecipeController::class, 'saveRecipe']);
    // Remove a recipefrom saved recipes (idRecipe)
    Route::post('recipes/unsave', [RecipeController::class, 'unsaveRecipe']);
    // Like a recipe(idRecipe)
    Route::post('recipes/like', [RecipeController::class, 'likeRecipe']);
    // Get Recipe with its ID
    Route::post('post', [RecipeController::class, 'getRecipe']);
    // Remove Like from a recipe(idRecipe)
    // Route::post('recipes/unlike', [RecipeController::class, 'unlikeRecipe']);

    // Create a comment (idRecipe, commentText)
    Route::post('comments/create', [CommentController::class, 'createComment']);
    // Delete a comment (idComment)
    Route::post('comments/delete', [CommentController::class, 'deleteComment']);

    // Get current user
    Route::get('me', [UserController::class, 'getCurrentUser']);

    // Change personnal data (fullName?, username?, email?)
    Route::post('changePersonnalData', [UserController::class, 'changePersonnalData']);

    // ChangePassword (current_password, new_password)
    Route::post('changePassword', [UserController::class, 'changePassword']);
});


Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Endpoint not found'
    ], 404);
});

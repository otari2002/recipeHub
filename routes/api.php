<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserLoggedIn;

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginOtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;

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

// Group of routes only available to logged in users
Route::middleware([EnsureUserLoggedIn::class])->group(function () {

    // Logout
    Route::get('logout', [LoginController::class, 'logout']);
    // User Token Refresh
    Route::get('refresh', [LoginController::class, 'refresh']);

    // Invite Via Email
    Route::post("invite-contact", [UserController::class, "inviteContact"]);

    // Email Adress Verification
    Route::get('email/send', [EmailVerificationController::class, 'sendOTP']);
    Route::post('email/verify', [EmailVerificationController::class, 'verify']); //(code)

    // Create a post (idRestaurant, postText, rating, pictures? = file[])
    Route::post('posts/create', [PostController::class, 'createPost']);
    // Delete a post created by user (idPost)
    Route::post('posts/delete', [PostController::class, 'deletePost']);
    // Save/Unsave post (idPost)
    Route::post('posts/save', [PostController::class, 'savePost']);
    // Remove a post from saved posts (idPost)
    // Route::post('posts/unsave', [PostController::class, 'unsavePost']);
    // Like a post (idPost)
    Route::post('posts/like', [PostController::class, 'likePost']);
    // Get Post with its ID
    Route::post('post', [PostController::class, 'getPost']);
    // Remove Like from a post (idPost)
    // Route::post('posts/unlike', [PostController::class, 'unlikePost']);
    // Share a post (idPost)
    Route::post('posts/share', [PostController::class, 'sharePost']);

    // Create a comment (idPost, idParent?, commentText)
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

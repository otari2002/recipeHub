<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class SocialiteController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }
    public function requestTokenGoogle(Request $request)
    {
        $formFields = $request->only('social_token');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['social_token' => 'required']);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $social_token = $formFields['social_token'];

        // Get the user info via Socialite
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($social_token);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token!',
                'error' => $e->getMessage()
            ]);
        };

        $email = $googleUser->email;
        $user = User::where('email', $email)->first();
        $new = false;


        // Register user if email does not exist
        if (!$user) {

            $username = substr($googleUser->email, 0, strpos($googleUser->email, '@'));
            $user = User::create([
                'email' => $googleUser->email,
                'fullName' => $googleUser->name ? $googleUser->name : $username,
                'username' => $username,
                'password' => null,
                "uuid" => (string) Str::uuid(),
                'provider' => 'Google',
                'email_verified' => 1
            ]);
            $new = true;
        } else if (!$user->provider) { // Check if the user exists check if he registered normally
            return response()->json([
                'status' => 'error',
                'message' => 'User should login normaly with email'
            ]);
        } else if ($user->provider != 'Google') {  // Check if the user logged via another provider
            return response()->json([
                'status' => 'error',
                'message' => 'User should log via ' . $user->provider
            ]);
        }

        // Log in the user
        $token = Auth::login($user);
        $loggedInUser = Auth::user();

        return response()->json([
            'status' => 'success',
            'new_user' => $new,
            'token' => $token,
            'user' => $loggedInUser
        ]);
    }
    public function requestTokenFacebook(Request $request)
    {
        $formFields = $request->only('social_token');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['social_token' => 'required']);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $social_token = $formFields['social_token'];

        // Get the user info via Socialite
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->userFromToken($social_token);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token!',
                'error' => $e->getMessage()
            ]);
        };

        $email = $facebookUser->email;
        $user = User::where('email', $email)->first();
        $new = false;

        // Register user if email does not exist
        if (!$user) {
            $username = $facebookUser->nickname ? $facebookUser->nickname : substr($facebookUser->email, 0, 4) . Str::random(4);
            $user = User::create([
                'email' => $email,
                'fullName' => $facebookUser->name ? $facebookUser->name : $username,
                'username' => $username,
                'password' => null,
                "uuid" => (string) Str::uuid(),
                'provider' => 'Facebook',
                'email_verified' => 1
            ]);
            $new = true;
        } else if (!$user->provider) { // If the user exists check if he registered normally
            return response()->json([
                'status' => 'error',
                'message' => 'User should login normaly with email/password'
            ]);
        } else if ($user->provider != 'Facebook') { // Check if the user logged via another provider
            return response()->json([
                'status' => 'error',
                'message' => 'User should log via ' . $user->provider
            ]);
        }

        // Log in the user
        $token = Auth::login($user);
        $loggedInUser = Auth::user();
        return response()->json([
            'status' => 'success',
            'new_user' => $new,
            'token' => $token,
            'user' => $loggedInUser
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request){
        // Check if user is logged in
        if(Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'User already logged in.'
            ]);
        }
        $formFields = $request->only(['email', 'password']);
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'email' => 'email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => implode($validator->errors()->all())
            ]);
        }
        $key = 'email';
        $credentials = $request->only($key, 'password');
        // Verify credentials
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect Credentials'
            ]);
        }
        // Get verification status
        $reminder = '';
        if (!Auth::user()->email_verified){
            $reminder .= 'You have not verified your email. ';
        }
        $user = Auth::user();
        $refreshToken = Str::random(60);
        
        User::where('idUser', $user->idUser)->update(['refresh_token' => $refreshToken]);
        return response()->json([
            'status' => 'success',
            'message' => 'Login Successfull',
            'reminder' => $reminder,
            'token' => $token,
            'user' => $user,
            'refresh_token' => $refreshToken
        ]);
    }

    public function logout() {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'logout'
        ]);
    }

    // Function that returns a new valid token
    public function refresh(Request $request) {
        $request->validate([
            'refresh_token' => 'required'
        ]);
    
        // Find the user by refresh token 
        $user = User::where('refresh_token',$request->refresh_token)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }
    
        // Generate new access token
        $newToken = Auth::login($user);
        $newRefreshToken = Str::random(60);
    
        // Update the refresh token in the database using the user's ID
        User::where('idUser', $user->idUser)->update(['refresh_token' => $newRefreshToken]);
    
        return response()->json([
            'token' => $newToken,
            'refresh_token' => $newRefreshToken
        ]);
    }
}

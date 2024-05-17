<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

        // Check if user logged in via a provider
        // $socialLogin = User::where($key, $credentials[$key])->where('provider', '<>', null)->first();
        // if($socialLogin){
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'User with '.$key.' ('.$credentials[$key].') has registered via '.$socialLogin->provider
        //     ]);
        // }

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
        
        return response()->json([
            'status' => 'success',
            'message' => 'Login Successfull',
            'reminder' => $reminder,
            'token' => $token,
            'user' => $user,
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
    public function refresh(){
        try{
            $newToken = Auth::refresh();
        }catch(\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['error' => $e->getMessage()], 401);
        }
        return response()->json(['status' => 'success', 'token' => $newToken]);
    }
}

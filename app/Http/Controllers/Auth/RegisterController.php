<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{   
    public function index(){
        return csrf_token(); 
    }
    public function register(Request $request){
        // Check if user is logged in
        if(Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'User already logged in.'
            ]);
        }

        $formFields = $request->only(['fullName', 'username', 'email', 'password']);
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'fullName' => 'required',
            'username' => 'required',
            'email' => 'email',
            'password' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }

        $formFields['password'] = bcrypt($formFields['password']);

        $key = 'email';

        // Check if a user with the same email has logged in via a provider
        $socialLogin = User::where($key, $formFields[$key])->where('provider', '<>', null)->first();
        if($socialLogin){
            return response()->json([
                'status' => 'error',
                'message' => 'User with '.$key.' ('.$formFields[$key].') has registered via '.$socialLogin->provider
            ]);
        }

        // Check if an email or a username already exists
        $errorMessage = '';
        if(array_key_exists('email',$formFields) && User::where('email', $formFields['email'])->first()){
            $errorMessage .= 'Email already exists. ';
        }
        if(array_key_exists('username',$formFields) && User::where('username', $formFields['username'])->first()){
            $errorMessage .= 'Username already exists. ';
        }

        if($errorMessage != ''){
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ]);
        }
        // Register user and log in
        $formFields["uuid"] = (string) \Illuminate\Support\Str::uuid();
        $user = User::create($formFields);
        $token = Auth::login($user);
        $refreshToken = Str::random(60);
        User::where('idUser', $user->idUser)->update(['refresh_token' => $refreshToken]);

        return response()->json([
            'status' => 'success',
            'message' => 'Register Successful',
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $user
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use App\Models\PasswordOtp;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\PasswordResetMailNotification;

class ResetPasswordController extends Controller
{

    public function requestEmail(Request $request) {
        $formFields = $request->only('email');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['email' => 'required|email']);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $email = $formFields['email'];
        $user = User::where('email',$email)->first();

        // Check if user with this email exists and if he does check if he logged in via a provider
        if(!$user){
               return response()->json([
                'status' => 'error',
               'message' => 'A user with the email '.$email.' does not exist'
            ]);
        }else if($user->provider){
            return response()->json([
                'status' => 'error',
               'message' => 'User has to log in via '.$user->provider
            ]);
        }

        // Send code to the user's email
        try{
            $user->notify(new PasswordResetMailNotification);
        }catch(Exception $e){
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Password reset code sent to '.$user->email
        ]);
    }

    // Function to confirm the otp
    public function confirmOTP(Request $request){
        $formFields = $request->only('code', 'email');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'code' => ['required', 'numeric', 'digits:6'],
            'email' => 'email'
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $key = 'email';

        $otpRecord = PasswordOtp::where($key, $formFields[$key])->first();

        // Check if a code was to the user
        if(!$otpRecord){
            return response()->json([
                'status' => 'error',
                'message' => 'No valid code is associated with this '.$key
            ]);
        }

        // Check if code is correct
        if(!Hash::check($formFields['code'], $otpRecord->otp)){
            return response()->json([
                'status' => 'error',
                'message' => 'The code entered is incorrect.'
            ]);
        }

        // Check if code is still valid
        $expirationDate = Carbon::parse($otpRecord->expiration_date);
        if(Carbon::now()->gt($expirationDate)){
            $otpRecord->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'This code has expired.'
            ]);
        }

        $user = User::where($key, $formFields[$key])->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Code confirmed successfully',
            'code' => $formFields['code'],
            'user' => $user
        ]);
    }

    // Function to confirm otp and reset the password
    public function resetPassword(Request $request){
        $formFields = $request->only('code', 'email', 'password', 'password_confirmation');
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'code' => ['required', 'numeric', 'digits:6'],
            'email' => 'email',
            'password' => 'required|confirmed'
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $key = 'email';
        $otpRecord = PasswordOtp::where($key, $formFields[$key])->first();

        // Check if a code was to the user
        if(!$otpRecord){
            return response()->json([
                'status' => 'error',
                'message' => 'No valid code is associated with this '.$key
            ]);
        }

        // Check if code is correct
        if(!Hash::check($formFields['code'], $otpRecord->otp)){
            return response()->json([
                'status' => 'error',
                'message' => 'The code entered is incorrect.'
            ]);
        }

        // Check if code is still valid
        $expirationDate = Carbon::parse($otpRecord->expiration_date);
        if(Carbon::now()->gt($expirationDate)){
            $otpRecord->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'This code has expired.'
            ]);
        }

        // Update the password
        $user = User::where($key, $formFields[$key])->first();
        $user->update(['password' => Hash::make($formFields['password'])]);

        $otpRecord->delete();

        // Log in the user
        $token = Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
            'user' => $user,
            'token' => $token
        ]);
    }
}

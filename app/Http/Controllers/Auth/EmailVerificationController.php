<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use App\Models\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;

class EmailVerificationController extends Controller
{
    // Function to send verification otp to email
    public function sendOTP(){
        $user = Auth::user();

        // Check if user has an email
        if(!$user->email){
            return response()->json([
                'status' => 'error',
                'message' => 'No email is associated with this user.'
            ]);
        }

        // Check if email already verified
        if($user->email_verified){
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified.'
            ]);
        }

        // Send verification code
        try{
            $user->notify(new EmailVerificationNotification);
        }catch(Exception $e){
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification code sent successfully to '.$user->email
        ]);
    }

    // Function to verify the code entered by the user
    public function verify(Request $request){
        $formFields = $request->only('code');

        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['code' => ['required', 'numeric', 'digits:6']]);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $code = $formFields['code'];
        $user = Auth::user();

        // Check if email already verified
        if($user->email_verified){
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified.'
            ]);
        }
        $email = $user->email;
        $otpRecord = EmailOtp::find($user->email);

        // Check if user was sent a code
        if(!$otpRecord){
            return response()->json([
                'status' => 'error',
                'message' => 'No valid code is associated with this email.'
            ]);
        }
        $expirationDate = Carbon::parse($otpRecord->expiration_date);

        // Check if code is still valid
        if(Carbon::now()->gt($expirationDate)){
            $otpRecord->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'This code has expired.'
            ]);
        }

        // Check if code is correct
        if(!Hash::check($code, $otpRecord->otp)){
            return response()->json([
                'status' => 'error',
                'message' => 'The code entered is incorrect.'
            ]);
        }
        $user = User::where('email', $email)->first();

        // Verify user's email
        $user->update(['email_verified' => 1]);
        $otpRecord->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Email verified',
            'user' => $user
        ]);
    }
}

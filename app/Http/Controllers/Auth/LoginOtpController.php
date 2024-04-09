<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use App\Models\LoginOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Notifications\LoginOtpMailNotification;

class LoginOtpController extends Controller
{
    // Function to send login otp via email
    public function requestEmail(Request $request)
    {
        $formFields = $request->only('email');

        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, ['email' => 'required|email']);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $email = $formFields['email'];
        $user = User::where('email', $email)->first();

        // Check if email exists or if the user logged in via a provider
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'A user with the email ' . $email . ' does not exist'
            ]);
        } else if ($user->provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'User has to log in via ' . $user->provider
            ]);
        }

        // Send login otp via email
        try {
            $user->notify(new LoginOtpMailNotification);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Code sent successfully to ' . $user->email
        ]);
    }

    // Function to verify the code entered by the user
    public function processLogin(Request $request)
    {
        $formFields = $request->only(['email', 'code']);
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'email' => 'email',
            'code' => ['required', 'numeric', 'digits:6']
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode($validator->errors()->all())
            ]);
        }
        $key = 'email';

        $otpRecord = LoginOtp::where($key, $formFields[$key])->first();

        // Check if user was sent a code
        if (!$otpRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'No valid code is associated with this ' . $key
            ]);
        }

        // Check if code is correct
        if (!Hash::check($formFields['code'], $otpRecord->otp)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The code entered is incorrect.'
            ]);
        }

        // Check if code is still valid
        $expirationDate = Carbon::parse($otpRecord->expiration_date);
        if (Carbon::now()->gt($expirationDate)) {
            $otpRecord->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'This code has expired.'
            ]);
        }
        $user = $otpRecord->usersWithEmailOtp;
        $user->email_verified = 1;

        // Log user in
        $token = Auth::login($user);
        $otpRecord->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successfull!',
            'token' => $token,
            'user' => $user
        ]);
    }
}

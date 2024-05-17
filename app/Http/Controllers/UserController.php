<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailOtp;
use App\Models\LoginOtp;
use App\Models\PasswordOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function changePersonnalData(Request $request)
    {
        $idUser = Auth::user()->idUser;
        $user = User::where('idUser', $idUser)->first();
        $formFields = $request->only(['fullName', 'username', 'email']);
        // Validate the data sent in the body of the request
        $validator = Validator::make($formFields, [
            'fullName' => 'nullable',
            'username' => 'nullable',
            'email' => ['nullable', 'email'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode($validator->errors()->all())
            ]);
        }
        $hasProvider = $user->provider ? true : false;
        $message = '';
        // Delete all OTPs related to current email
        if ($request->has('email') && $formFields['email'] != null) {
            PasswordOtp::where('email', $user->email)->delete();
            EmailOtp::where('email', $user->email)->delete();
        }
        // For each value check if it already exists or if the current user already has that value
        foreach ($formFields as $key => $value) {
            if ($value != null) {
                if ($key == 'email' && $hasProvider) { // if user logged in via a provider he cannot change his email
                    if ($user->email && $formFields[$key] != $user->email) {
                        $message .= 'The email cannot be changed. ';
                    }
                } else if ($key != 'fullName') {
                    $existingValue = User::where($key, $value)->where('idUser', '<>', $user->idUser)->first();
                    if ($existingValue) {
                        $message .= 'This ' . $key . ' already exists. ';
                    } elseif ($user->$key == $value) {
                        continue;
                    } else {
                        $user->$key = $value;
                        $message .= 'The ' . $key . ' was changed successfully. ';
                        // A new email requires verification
                        if ($key == 'email') {
                            $user->email_verified = 0;
                        }
                    }
                }
                if ($key == 'fullName') { // Unlike the other keys, the fullName is not unique
                    if ($user->$key == $value) {
                        continue;
                    } else {
                        $user->$key = $value;
                        $message .= 'The ' . $key . ' was changed successfully. ';
                    }
                }
            }
        }
        if ($message == "") $message = "Nothing was changed";
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => $user
        ]);
    }
    public function changePassword(Request $request)
    {
        $idUser = Auth::user()->idUser;
        $user = User::where('idUser', $idUser)->first();
        // If user logged in via a provider he cannot set up a password for his acccount
        if ($user->provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'User logs in via ' . $user->provider
            ]);
        }
        // Validate the data sent in the body of the request
        $formFields = $request->only(['current_password', 'new_password', 'new_password_confirmation']);
        $validator = Validator::make($formFields, [
            'current_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(" ", $validator->messages()->all())
            ]);
        }
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The current password is incorrect'
            ]);
        }
        // Update the password
        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password successfully changed'
        ]);
    }

    // Get a user with his uuid
    public function getUser($uuid)
    {
        $currentUser = Auth::user();
        $user = User::where('uuid', $uuid)->first();
        if (!$user || $user->disabled) {
            return response()->json([
                'status' => 'error',
                'message' => 'User with uuid ' . $uuid . ' does not exist'
            ]);
        }
        $user->itsme = false;
        if ($currentUser->idUser == $user->idUser) {
            $user->itsme = true;
        }

        return response()->json([
            'status' => 'success',
            'user' => $user
        ]);
    }

    // Get current user
    public function getCurrentUser()
    {
        $user = Auth::user();
        return $this->getUser($user->uuid);
    }

    // Get current user's saved recipes
    public function getSavedRecipes()
    {
        $user = Auth::user();
        $recipes = $user->savedRecipes;
        return $recipes;
    }

}

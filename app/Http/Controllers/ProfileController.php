<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function updatePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail('The current password is incorrect.');
                    }
                }],
                'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
            ]);

            $user = Auth::user();
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('profile.index')
                ->with('success', 'Password updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('profile.index')
                ->withErrors($e->validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }
}

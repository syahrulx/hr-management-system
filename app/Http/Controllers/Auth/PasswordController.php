<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8)->mixedCase()->symbols(), 'confirmed'],
        ], [
            'current_password.current_password' => 'Current password does not match existing record!',
            'password.min' => 'New password does not meet security requirements!',
            'password.mixed' => 'New password does not meet security requirements!',
            'password.symbols' => 'New password does not meet security requirements!',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password successfully changed.');
    }
}

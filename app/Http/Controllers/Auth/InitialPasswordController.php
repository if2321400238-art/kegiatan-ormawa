<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InitialPasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.change-initial-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (hash_equals((string) $request->user()->nim, $validated['password'])) {
            throw ValidationException::withMessages([
                'password' => 'Password baru tidak boleh sama dengan NIM.',
            ]);
        }

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah.');
    }
}

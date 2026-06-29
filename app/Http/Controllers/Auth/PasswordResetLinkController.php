<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UnujaMahasiswaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, UnujaMahasiswaService $mahasiswaService): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        $login = trim($validated['login']);
        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $login)->first()
            : User::where('nim', $login)->first();

        if (! $user && preg_match('/^\d+$/', $login)) {
            try {
                $user = $mahasiswaService->syncUserByNim($login);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if (! $user) {
            return back()->withInput()->withErrors([
                'login' => 'Akun tidak ditemukan.',
            ]);
        }

        if (str_ends_with($user->email, '@mahasiswa.unuja.local')) {
            return back()->withInput()->withErrors([
                'login' => 'API UNUJA tidak menyediakan email untuk NIM ini. Hubungi admin untuk pemulihan akun.',
            ]);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput(['login' => $login])
                ->withErrors(['login' => __($status)]);
    }
}

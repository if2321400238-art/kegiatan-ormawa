<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        // Load Ormawa if user is Ormawa
        if ($user->isOrmawa()) {
            $user->load('ormawa');
            $dosen = \App\Models\User::where('role', 'dosen')->get();
        }

        return view('profile.edit', [
            'user' => $user,
            'dosen' => $dosen ?? collect(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validate user data
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        // Update user
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // If Ormawa, update Ormawa data
        if ($user->isOrmawa()) {
            $this->updateOrmawaProfile($request, $user);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile berhasil diperbarui');
    }

    /**
     * Update Ormawa specific profile.
     */
    private function updateOrmawaProfile(Request $request, $user)
    {
        $validated = $request->validate([
            'nama_ormawa' => ['required', 'string', 'max:255'],
            'ketua' => ['required', 'string', 'max:255'],
            'pembina' => ['nullable', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'kop_surat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Get or create Ormawa
        $ormawa = $user->ormawa;

        if (!$ormawa) {
            $ormawa = new \App\Models\Ormawa();
            $ormawa->user_id = $user->id;
        }

        // Handle kop surat upload
        if ($request->hasFile('kop_surat')) {
            // Delete old kop surat if exists
            if ($ormawa->kop_surat) {
                Storage::disk('public')->delete($ormawa->kop_surat);
            }

            // Store new kop surat
            $path = $request->file('kop_surat')->store('kop_surat', 'public');
            $validated['kop_surat'] = $path;
        }

        // Update ormawa data
        $pembinaUser = null;
        if (!empty($validated['pembina']) && $user->isOrmawa()) {
            $pembinaUser = \App\Models\User::where('role', 'dosen')->where('nama', $validated['pembina'])->first();
        }

        $ormawa->nama_ormawa = $validated['nama_ormawa'];
        $ormawa->ketua = $validated['ketua'];
        $ormawa->pembina = $pembinaUser?->nama ?? $validated['pembina'] ?? null;
        $ormawa->pembina_user_id = $pembinaUser?->id;
        $ormawa->kontak = $validated['kontak'] ?? null;
        $ormawa->deskripsi = $validated['deskripsi'] ?? null;

        if (isset($validated['kop_surat'])) {
            $ormawa->kop_surat = $validated['kop_surat'];
        }

        $ormawa->save();
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

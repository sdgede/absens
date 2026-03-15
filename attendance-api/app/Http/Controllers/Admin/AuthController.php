<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login admin.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Pastikan hanya admin & super_admin yang bisa masuk
            if (! $user->hasAnyRole(['admin', 'super_admin'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Akun ini tidak memiliki akses admin.']);
            }

            $user->update(['last_login_at' => now()]);

            Log::channel('auth_log')->info('Admin web login success', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        Log::channel('auth_log')->warning('Admin web login failed', [
            'email' => $request->email,
            'ip'    => $request->ip(),
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    /**
     * Proses logout admin.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            Log::channel('auth_log')->info('Admin web logout', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Anda berhasil logout.');
    }
}

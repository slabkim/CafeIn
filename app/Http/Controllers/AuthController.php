<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            $request->session()->regenerate();

            return redirect()->intended($this->redirectByRole());
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $customerRole = Role::where('name', 'Customer')->first();

        if (! $customerRole) {
            return back()->withErrors(['role' => 'Role customer belum tersedia, hubungi administrator.']);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => $customerRole->id,
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect($this->redirectByRole());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => str()->random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil diperbarui. Silakan login.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    private function redirectByRole(): string
    {
        $user = Auth::user();

        $role = $user?->role?->name;

        return match ($role) {
            'Admin' => route('admin.dashboard'),
            'Kasir' => route('kasir.dashboard'),
            default => route('menus'),
        };
    }

    // Google OAuth redirect
    public function redirectToGoogle()
    {
        $redirectUri = config('services.google.redirect');
        Log::debug('AuthController::redirectToGoogle - using redirect uri: ' . $redirectUri);

        $driver = Socialite::driver('google');
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */

        // Use the typed provider for static analysis and call chain safely
        return $driver->redirectUrl($redirectUri)
            ->stateless()
            ->redirect();
    }

    // Google OAuth callback
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        // Use stateless to avoid session issues when callback is from Google
        Log::debug('AuthController::handleGoogleCallback - request url: ' . $request->fullUrl());

        $driver = Socialite::driver('google');
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $googleUser = $driver->stateless()->user();

        if (! $googleUser || ! $googleUser->getEmail()) {
            return redirect()->route('login')->withErrors(['google' => 'Gagal mendapatkan informasi dari Google. Silakan coba lagi.']);
        }

        // Find or create user
        $user = User::firstWhere('email', $googleUser->getEmail());

        if (! $user) {
            $customerRole = Role::where('name', 'Customer')->first();
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? $googleUser->getEmail(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(str()->random(32)),
                'role_id' => $customerRole?->id,
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);
        } else if (! $user->google_id) {
            $user->google_id = $googleUser->getId();
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->redirectByRole());
    }
}
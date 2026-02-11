<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->validated())) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect('/');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');
        return $driver->stateless()->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver =  Socialite::driver('google');
        $googleUser = $driver->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    public function showVerifyEmail(): View
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect('/');
    }

    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

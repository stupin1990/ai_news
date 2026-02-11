<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', ['initialView' => 'buttons']);
    }

    public function showLoginEmail(): View
    {
        return view('auth.login', ['initialView' => 'email']);
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
        return view('auth.login', ['initialView' => 'register']);
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
        $driver = Socialite::driver('google');
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

    public function showForgotPassword(): View
    {
        return view('auth.login', ['initialView' => 'password']);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

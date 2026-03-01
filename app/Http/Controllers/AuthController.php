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
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): \Illuminate\View\View
    {
        return $this->authPage('buttons');
    }

    public function showLoginEmail(): \Illuminate\View\View
    {
        return $this->authPage('email');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister(): \Illuminate\View\View
    {
        return $this->authPage('register');
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

    public function showVerifyEmail(): \Illuminate\View\View
    {
        return $this->react('VerifyEmailPage', [
            'appName' => config('app.name', 'Ai News'),
            'message' => session('message'),
            'routes' => [
                'resend' => route('verification.send'),
                'logout' => route('logout'),
            ],
        ]);
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

    public function showForgotPassword(): \Illuminate\View\View
    {
        return $this->authPage('password');
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

    public function showResetPassword(Request $request, string $token): \Illuminate\View\View
    {
        return $this->react('ResetPasswordPage', [
            'appName' => config('app.name', 'Ai News'),
            'token' => $token,
            'email' => $request->query('email', ''),
            'errors' => $this->collectErrors(),
            'routes' => [
                'update' => route('password.update'),
                'login' => route('login'),
            ],
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

    private function authPage(string $initialView): \Illuminate\View\View
    {
        return $this->react('AuthPage', [
            'appName' => config('app.name', 'Ai News'),
            'initialView' => $initialView,
            'status' => session('status'),
            'errors' => $this->collectErrors(),
            'old' => [
                'name' => old('name'),
                'email' => old('email'),
                'remember' => old('remember'),
            ],
            'routes' => [
                'google' => route('auth.google'),
                'loginSubmit' => route('login.submit'),
                'registerSubmit' => route('register.submit'),
                'passwordSubmit' => route('password.submit'),
            ],
            'viewPaths' => [
                'buttons' => route('login', absolute: false),
                'email' => route('login.email', absolute: false),
                'register' => route('register', absolute: false),
                'password' => route('password.request', absolute: false),
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function collectErrors(): array
    {
        return session('errors')?->all() ?? [];
    }
}

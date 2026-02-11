@extends('layouts.app')

@section('content')
<div class="flex min-h-screen items-center justify-center">
    <div class="p-8">
        <h1 class="mb-8 text-center text-3xl font-semibold">Ai News</h1>

        {{-- Error messages --}}
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Main buttons --}}
        <div id="auth-buttons" class="flex w-fit flex-col gap-3">
            <a href="{{ route('auth.google') }}"
               class="flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1D1D1A]">
                <svg class="h-5 w-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.96 10.96 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Sign in with Google
            </a>
            <button onclick="document.getElementById('email-form').classList.remove('hidden'); document.getElementById('auth-buttons').classList.add('hidden');"
                    class="rounded-md cursor-pointer border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1D1D1A]">
                Sign in with Email
            </button>
            <a href="{{ route('register') }}"
               class="rounded-md border border-gray-300 bg-white px-4 py-2.5 text-center text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1D1D1A]">
                Create Account
            </a>
        </div>

        {{-- Email login form (hidden by default) --}}
        <form id="email-form" method="POST" action="{{ route('login.submit') }}" class="hidden w-fit flex-col gap-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="new-password" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
            </div>
            <button type="submit"
                    class="rounded-md cursor-pointer bg-[#1b1b18] px-4 py-2.5 mt-2 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white">
                Sign In
            </button>
            <button type="button"
                    onclick="document.getElementById('email-form').classList.add('hidden'); document.getElementById('auth-buttons').classList.remove('hidden');"
                    class="text-center ml-2 cursor-pointer text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]">
                &larr; Back
            </button>
        </form>
    </div>
</div>
@endsection

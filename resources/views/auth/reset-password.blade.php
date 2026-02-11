@extends('layouts.app')

@section('content')
<div class="flex min-h-screen items-center justify-center">
    <div class="p-8">
        <h1 class="mb-8 text-center text-3xl font-semibold">Ai News</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="flex w-fit flex-col gap-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">New Password</label>
                <input id="password" name="password" type="password" required
                       class="w-[300px] rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
                <p class="mt-1 text-xs text-gray-500 dark:text-[#A1A09A]">Minimum 8 characters</p>
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="w-[300px] rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
            </div>
            <div class="flex items-center gap-3 mt-2">
                <button type="submit"
                        class="cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white">
                    Reset Password
                </button>
                <a href="{{ route('login') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]">
                    &larr; Back to login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

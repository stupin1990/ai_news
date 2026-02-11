@extends('layouts.app')

@section('content')
<div class="flex min-h-screen items-center justify-center">
    <div class="w-full max-w-sm p-8 text-center">
        <h1 class="mb-4 text-3xl font-semibold">Ai News</h1>
        <p class="mb-6 text-sm text-gray-600 dark:text-[#A1A09A]">
            Please verify your email address by clicking the link we sent to your inbox.
        </p>

        @if (session('message'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-600 dark:bg-green-900/20 dark:text-green-400">
                {{ session('message') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm cursor-pointer text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]">
                Logout
            </button>
        </form>
    </div>
</div>
@endsection

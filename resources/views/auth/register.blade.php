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

        <form method="POST" action="{{ route('register.submit') }}" class="flex w-fit flex-col gap-4">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-medium">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="w-[300px] rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="new-password" required
                       class="w-[300px] rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                       class="w-[300px] rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]">
                <p class="mt-1 text-xs text-gray-500 dark:text-[#A1A09A]">Minimum 8 characters</p>
            </div>
            <button type="submit"
                    class="self-center cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white">
                Create Account
            </button>
            <a href="{{ route('login') }}"
               class="text-center text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]">
                &larr; Already have an account? Sign in
            </a>
        </form>
    </div>
</div>
@endsection

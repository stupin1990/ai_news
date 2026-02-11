@extends('layouts.app')

@section('content')
<header class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-[#3E3E3A]">
    <h1 class="text-xl font-semibold">Ai News</h1>
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-600 dark:text-[#A1A09A]">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm cursor-pointer text-gray-500 underline underline-offset-4 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]">
                Logout
            </button>
        </form>
    </div>
</header>
<main class="p-6">
    <h1 class="text-4xl font-bold">Ai News</h1>
</main>
@endsection

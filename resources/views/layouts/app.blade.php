<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ai News') }}</title>
    @viteReactRefresh
    @vite(['resources/react/app.tsx'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen font-sans antialiased">
    <div id="app"></div>
    <script>
        window.__INITIAL_PAGE__ = {{ Js::from([
            'page' => $page ?? 'NewsPage',
            'props' => $props ?? [],
        ]) }};
    </script>
</body>
</html>

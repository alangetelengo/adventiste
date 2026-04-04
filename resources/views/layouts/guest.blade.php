<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Connexion') — {{ config('app.name') }}</title>
        @include('partials.favicon')
        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <meta name="service-worker-url" content="{{ asset('sw.js') }}">
        <meta name="theme-color" content="#0f172a">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen bg-slate-100 dark:bg-slate-900 flex items-center justify-center p-6">
        @yield('content')
    </body>
</html>

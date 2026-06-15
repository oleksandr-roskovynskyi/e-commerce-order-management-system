<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Storefront' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('catalog.browse') }}" class="text-lg font-semibold tracking-tight">
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-4 text-sm text-gray-500">
                <a href="{{ route('catalog.browse') }}" class="hover:text-gray-900">Catalog</a>
                @if (Route::has('orders.create'))
                    <a href="{{ route('orders.create') }}" class="hover:text-gray-900">New order</a>
                @endif
                @if (Route::has('orders.track'))
                    <a href="{{ route('orders.track') }}" class="hover:text-gray-900">Track order</a>
                @endif
                <a href="/admin" class="hover:text-gray-900">Admin</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>

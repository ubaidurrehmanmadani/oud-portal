<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ str_starts_with(app()->getLocale(), 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} | OUD Portal</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
    <div class="app-shell">
        <header class="app-header">
            <div class="app-header-inner">
                <a href="{{ url('/') }}" class="brand-link">
                    <img src="{{ asset('images/oud-logo.webp') }}" alt="OUD Real Estate">
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="button button-secondary">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="app-main">
            @yield('content')
        </main>
    </div>
</body>
</html>

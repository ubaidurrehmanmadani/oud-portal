<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ str_starts_with(app()->getLocale(), 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'OUD Portal' }}</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-brand-panel">
            <div>
                <img src="{{ asset('images/oud-logo.webp') }}" alt="OUD Real Estate" class="auth-brand-logo">
                <p class="eyebrow eyebrow-light">OUD Staff & Landlord Portal</p>
                <h1 class="auth-brand-title">One secure portal for staff, departments, and landlords.</h1>
                <p class="auth-brand-copy">Inspired by OUD's official brand language: refined, private, and built for premium real-estate operations.</p>
            </div>

            <div class="auth-feature-list">
                <div class="auth-feature">
                    <p>Role-based access</p>
                    <span>Users are sent to the correct dashboard after login.</span>
                </div>
                <div class="auth-feature">
                    <p>Built for bilingual rollout</p>
                    <span>Layouts leave room for English and Arabic content.</span>
                </div>
            </div>
        </section>

        <section class="auth-form-panel">
            <div class="auth-form-wrap">
                <div class="auth-mobile-heading">
                    <img src="{{ asset('images/oud-logo.webp') }}" alt="OUD Real Estate" class="auth-mobile-logo">
                    <h1>Secure account access</h1>
                </div>

                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>

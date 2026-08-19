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
                <p class="eyebrow eyebrow-light">{{ __('portal.brand_eyebrow') }}</p>
                <h1 class="auth-brand-title">{{ __('portal.brand_title') }}</h1>
                <p class="auth-brand-copy">{{ __('portal.brand_copy') }}</p>
            </div>

            <div class="auth-feature-list">
                <div class="auth-feature">
                    <p>{{ __('portal.feature_role_title') }}</p>
                    <span>{{ __('portal.feature_role_body') }}</span>
                </div>
                <div class="auth-feature">
                    <p>{{ __('portal.feature_bilingual_title') }}</p>
                    <span>{{ __('portal.feature_bilingual_body') }}</span>
                </div>
            </div>
        </section>

        <section class="auth-form-panel">
            <div class="auth-form-wrap">
                <div class="auth-form-switcher">
                    @include('partials.language-switcher')
                </div>

                <div class="auth-mobile-heading">
                    <img src="{{ asset('images/oud-logo.webp') }}" alt="OUD Real Estate" class="auth-mobile-logo">
                    <h1>{{ __('portal.secure_access') }}</h1>
                </div>

                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>

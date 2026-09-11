<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ str_starts_with(app()->getLocale(), 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'OUD Portal' }}</title>
    <link rel="stylesheet" href="{{ asset('oud/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('oud/application.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Aboreto&family=Noto+Sans+Arabic:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap">
</head>
<body data-show-password="{{ __('workspace.show_password') }}" data-hide-password="{{ __('workspace.hide_password') }}">
    <main class="auth-shell">
        <section class="brand-panel">
            <div class="brand-copy">
                <img src="{{ asset('oud/assets/oud-logo.png') }}" alt="OUD Real Estate" class="auth-brand-logo">
                <p class="eyebrow eyebrow-light">{{ __(request()->routeIs('login') ? 'portal.login_brand_eyebrow' : 'portal.brand_eyebrow') }}</p>
                <h1 class="auth-brand-title">{{ __('workspace.'.match (true) { request()->routeIs('register') => 'register_brand', request()->routeIs('password.request') => 'forgot_brand', request()->routeIs('password.reset') => 'reset_brand', default => 'login_brand' }) }}</h1>
                <p class="auth-brand-copy">{{ __(request()->routeIs('login') ? 'portal.login_brand_copy' : 'portal.brand_copy') }}</p>
            </div>

            <div class="feature-list">
                <div class="feature">
                    <p>{{ __('portal.feature_role_title') }}</p>
                    <span>{{ __('portal.feature_role_body') }}</span>
                </div>
                <div class="feature">
                    <p>{{ __('portal.feature_bilingual_title') }}</p>
                    <span>{{ __('portal.feature_bilingual_body') }}</span>
                </div>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-inner">
                <div class="auth-form-switcher">
                    @include('partials.language-switcher')
                </div>

                <div class="auth-mobile-heading">
                    <img src="{{ asset('oud/assets/oud-logo.png') }}" alt="OUD Real Estate" class="auth-mobile-logo">
                    <h1>{{ __('portal.secure_access') }}</h1>
                </div>

                @yield('content')
            </div>
        </section>
    </main>
<script src="{{ asset('oud/password-eye.js') }}"></script>
</body>
</html>

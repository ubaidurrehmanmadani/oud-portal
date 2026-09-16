<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ str_starts_with(app()->getLocale(), 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'OUD Portal' }}</title>
    <link rel="stylesheet" href="{{ asset('oud/styles.css') }}">
    @include('partials.select-assets')
    <link rel="stylesheet" href="{{ asset('oud/application.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Aboreto&family=Noto+Sans+Arabic:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap">
</head>
<body data-show-password="{{ __('workspace.show_password') }}" data-hide-password="{{ __('workspace.hide_password') }}">
    <main class="auth-shell {{ request()->routeIs('login') ? 'auth-shell-login' : '' }}">
        <section class="brand-panel">
            <img src="{{ asset('oud/assets/oud-logo.png') }}" alt="OUD Real Estate" class="auth-brand-logo">
            <div class="brand-copy">
                <p class="eyebrow eyebrow-light">{{ __('portal.login_brand_eyebrow') }}</p>
                <h1 class="auth-brand-title">{{ __('workspace.login_brand') }}</h1>
                <p class="auth-brand-copy">{{ __('portal.login_brand_copy') }}</p>
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
            <div class="auth-toolbar">
                @include('partials.language-switcher')
                @if (config('demo-access.enabled') && count($demoAccounts ?? []))
                    <div class="auth-demo-menu">
                        @include('auth.partials.demo-access')
                    </div>
                @endif
            </div>
            <div class="auth-inner">

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

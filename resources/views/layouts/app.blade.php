<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ str_starts_with(app()->getLocale(), 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('portal.dashboard') }} | {{ __('portal.app_name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <script>
        if (localStorage.getItem('oud-sidebar-collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-is-collapsed');
        }
    </script>
</head>
<body>
    <div class="app-shell">
        @include('partials.dashboard-sidebar')

        <div class="dashboard-workspace">
            <header class="app-header">
                <div class="app-header-inner">
                    <details class="mobile-menu">
                        <summary aria-label="{{ __('portal.open_navigation_menu') }}">
                            <span></span>
                            <span></span>
                            <span></span>
                        </summary>
                        <div class="mobile-menu-panel">
                            @include('partials.dashboard-sidebar')
                        </div>
                    </details>

                    <div class="topbar-heading">
                        <span>{{ __('portal.brand_eyebrow') }}</span>
                        <strong>{{ $title ?? __('portal.dashboard') }}</strong>
                    </div>

                    <div class="header-actions">
                        @include('partials.language-switcher')
                        @include('partials.profile-menu')
                    </div>
                </div>
            </header>

            @unless (request()->routeIs('admin.*'))
                <div class="dashboard-toolbar">
                    <div class="toolbar-search">
                        <span aria-hidden="true"></span>
                        <input type="search" placeholder="{{ __('portal.search_placeholder') }}">
                    </div>
                    <div class="toolbar-actions">
                        <a href="#" class="button button-secondary">{{ __('portal.new_upload') }}</a>
                        <a href="#" class="button button-primary">{{ __('portal.create_report') }}</a>
                    </div>
                </div>
            @endunless

            <main class="app-main">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="{{ asset('js/portal.js') }}"></script>
</body>
</html>

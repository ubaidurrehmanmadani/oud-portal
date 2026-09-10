<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | OUD Compass</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Aboreto&family=Noto+Sans+Arabic:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('oud/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('oud/excel-reporting.css') }}">
    <link rel="stylesheet" href="{{ asset('oud/report-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('oud/user-role-button.css') }}">
    <link rel="stylesheet" href="{{ asset('oud/user-role-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('oud/application.css') }}">
    <script src="{{ asset('oud/application.js') }}" defer></script>
</head>
<body data-portal-shell data-portal-role="{{ auth()->user()->role->value }}" class="{{ ($isFinancialReport ?? false) ? 'reporting' : '' }}">
<div class="portal {{ $isLandlord ? 'landlord-portal' : '' }}">
    <aside class="sidebar">
        <div class="sidebar-brand"><a href="{{ route(auth()->user()->dashboardRouteName()) }}"><img src="{{ asset('oud/assets/oud-logo.png') }}" alt="OUD Real Estate"></a></div>
        <div><p class="nav-label">{{ $isLandlord ? __('financial.property_reports') : __('workspace.workspace') }}</p>
            <nav class="nav" aria-label="{{ $isLandlord ? __('financial.property_reports') : __('workspace.workspace') }}">
                <a class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route(auth()->user()->dashboardRouteName()) }}">{{ __('workspace.dashboard') }}</a>
                @if ($isLandlord && $properties->isNotEmpty())
                    <div class="property-subnav" role="group" aria-label="{{ __('financial.property_reports') }}">
                        @foreach ($properties as $property)<a href="{{ route('landlord.financials', $property) }}" @if(request()->routeIs('landlord.financials') && $selectedProperty?->id === $property->id) aria-current="page" @endif>{{ $property->name }}</a>@endforeach
                    </div>
                @endif
                @foreach ($isLandlord ? ['properties', 'reports', 'documents', 'approvals'] : ['documents', 'training', 'announcements', 'search'] as $navSection)
                    <a class="{{ ($section ?? '') === $navSection ? 'active' : '' }}" href="{{ route($isLandlord ? 'landlord.index' : 'staff.index', ['section' => $navSection]) }}">{{ __('workspace.'.$navSection) }}</a>
                @endforeach
                @if (in_array(auth()->user()->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::DEPARTMENT_MANAGER]))
                    <a href="{{ route('content.index') }}">{{ __('workspace.manage_content') }}</a>
                @endif
                @if (auth()->user()->role === \App\Enums\UserRole::ADMIN)
                    @foreach (['users', 'permissions', 'departments', 'properties', 'documents', 'academy', 'reports', 'approvals', 'announcements', 'notifications', 'integrations', 'settings'] as $adminModule)
                        <a href="{{ route('admin.'.$adminModule.'.view') }}">{{ __('portal.'.($adminModule === 'academy' ? 'oud_academy' : $adminModule)) }}</a>
                    @endforeach
                    <a href="{{ route('admin.audit.view') }}">{{ __('portal.audit_logs') }}</a>
                @endif
            </nav>
        </div>
    </aside>
    <main class="portal-main">
        <header class="topbar">
            <div class="topbar-title"><span>{{ $isLandlord ? 'Oud Compass | Landlord portal' : __('portal.brand_eyebrow') }}</span><strong data-page-title>{{ $title }}</strong></div>
            <div class="topbar-actions">
                @include('partials.language-switcher')
                <div class="portal-profile"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->role->label() }} · {{ auth()->user()->email }}</span></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-secondary">{{ __('portal.logout') }}</button></form>
            </div>
        </header>
        <section class="content {{ $isLandlord ? 'landlord-content' : '' }}" data-page-content>
            @if ($isLandlord && $properties->isNotEmpty() && !request()->routeIs('workspace.*') && !request()->routeIs('dashboard.landlord') && !request()->routeIs('landlord.financials'))
                <form method="GET" class="workspace-filters">
                    <label for="property">{{ __('workspace.property') }}</label>
                    <select id="property" name="property">@unless(request()->routeIs('dashboard.landlord'))<option value="">{{ __('workspace.all_properties') }}</option>@endunless
@foreach ($properties as $property)<option value="{{ $property->id }}" @selected($selectedProperty?->id === $property->id)>{{ $property->name }}</option>@endforeach</select>
                    <button class="button button-secondary">{{ __('workspace.apply') }}</button>
                </form>
            @endif
            @include('auth.partials.errors')
            @yield('content')
        </section>
    </main>
</div>
@include('partials.role-guide')
</body>
</html>

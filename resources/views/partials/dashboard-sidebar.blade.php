@php
    $primaryNavigation = [
        ['label' => __('portal.dashboard'), 'route' => auth()->user()->dashboardRouteName(), 'icon' => 'grid'],
        ['label' => __('portal.users'), 'href' => '#', 'icon' => 'users'],
        ['label' => __('portal.departments'), 'href' => '#', 'icon' => 'departments'],
        ['label' => __('portal.properties'), 'href' => '#', 'icon' => 'properties'],
        ['label' => __('portal.documents'), 'href' => '#', 'icon' => 'documents'],
        ['label' => __('portal.reports'), 'href' => '#', 'icon' => 'reports'],
        ['label' => __('portal.approvals'), 'href' => '#', 'icon' => 'approvals'],
        ['label' => __('portal.announcements'), 'href' => '#', 'icon' => 'announcements'],
    ];

    $secondaryNavigation = [
        ['label' => __('portal.audit_logs'), 'href' => '#', 'icon' => 'audit'],
        ['label' => __('portal.settings'), 'href' => '#', 'icon' => 'settings'],
    ];
@endphp

<aside class="dashboard-sidebar" aria-label="{{ __('portal.main_navigation') }}">
    <div class="sidebar-brand">
        <a href="{{ url('/') }}" aria-label="{{ __('portal.dashboard_home') }}">
            <img src="{{ asset('images/oud-logo.webp') }}" alt="OUD Real Estate">
        </a>
        <span>{{ __('portal.portal') }}</span>
    </div>

    <nav class="sidebar-nav">
        <p class="sidebar-nav-label">{{ __('portal.workspace') }}</p>
        @foreach ($primaryNavigation as $item)
            @php
                $href = isset($item['route']) ? route($item['route']) : $item['href'];
                $active = isset($item['route']) && request()->routeIs($item['route']);
            @endphp
            <a href="{{ $href }}" class="sidebar-link {{ $active ? 'is-active' : '' }}">
                <span class="sidebar-icon sidebar-icon-{{ $item['icon'] }}" aria-hidden="true"></span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <nav class="sidebar-nav sidebar-nav-secondary">
        <p class="sidebar-nav-label">{{ __('portal.control') }}</p>
        @foreach ($secondaryNavigation as $item)
            <a href="{{ $item['href'] }}" class="sidebar-link">
                <span class="sidebar-icon sidebar-icon-{{ $item['icon'] }}" aria-hidden="true"></span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-status">
        <span class="status-dot"></span>
        <div>
            <p>{{ __('portal.secure_session') }}</p>
            <span>{{ optional(auth()->user()->last_login_at)->diffForHumans() ?? __('portal.active_now') }}</span>
        </div>
    </div>
</aside>

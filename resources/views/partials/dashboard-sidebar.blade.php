@php
    use App\Enums\UserRole;

    $isAdmin = auth()->user()->role === UserRole::ADMIN;

    $primaryNavigation = $isAdmin
        ? [
            ['label' => __('portal.dashboard'), 'route' => auth()->user()->dashboardRouteName(), 'active' => auth()->user()->dashboardRouteName(), 'icon' => 'grid'],
        ]
        : [
            ['label' => __('portal.dashboard'), 'route' => auth()->user()->dashboardRouteName(), 'active' => auth()->user()->dashboardRouteName(), 'icon' => 'grid'],
            ['label' => __('portal.documents'), 'href' => '#', 'active' => '', 'icon' => 'documents'],
            ['label' => __('portal.announcements'), 'href' => '#', 'active' => '', 'icon' => 'announcements'],
            ['label' => __('portal.settings'), 'href' => '#', 'active' => '', 'icon' => 'settings'],
        ];

    $adminGroups = [
        ['module' => 'users', 'label' => __('portal.users'), 'view' => __('portal.view_users'), 'create' => __('portal.create_user'), 'viewRoute' => 'admin.users.view', 'createRoute' => 'admin.users.create', 'icon' => 'users'],
        ['module' => 'permissions', 'label' => __('portal.permissions'), 'view' => __('portal.view_permissions'), 'create' => __('portal.create_permission'), 'viewRoute' => 'admin.permissions.view', 'createRoute' => 'admin.permissions.create', 'icon' => 'audit'],
        ['module' => 'departments', 'label' => __('portal.departments'), 'view' => __('portal.view_departments'), 'create' => __('portal.create_department'), 'viewRoute' => 'admin.departments.view', 'createRoute' => 'admin.departments.create', 'icon' => 'departments'],
        ['module' => 'properties', 'label' => __('portal.properties'), 'view' => __('portal.view_properties'), 'create' => __('portal.create_property'), 'viewRoute' => 'admin.properties.view', 'createRoute' => 'admin.properties.create', 'icon' => 'properties'],
        ['module' => 'documents', 'label' => __('portal.documents'), 'view' => __('portal.view_documents'), 'create' => __('portal.upload_document'), 'viewRoute' => 'admin.documents.view', 'createRoute' => 'admin.documents.upload', 'icon' => 'documents'],
        ['module' => 'academy', 'label' => __('portal.oud_academy'), 'view' => __('portal.view_training'), 'create' => __('portal.upload_training'), 'viewRoute' => 'admin.academy.view', 'createRoute' => 'admin.academy.upload', 'icon' => 'documents'],
        ['module' => 'reports', 'label' => __('portal.reports'), 'view' => __('portal.view_reports'), 'create' => __('portal.create_report'), 'viewRoute' => 'admin.reports.view', 'createRoute' => 'admin.reports.create', 'icon' => 'reports'],
        ['module' => 'approvals', 'label' => __('portal.approvals'), 'view' => __('portal.view_approvals'), 'create' => __('portal.create_approval'), 'viewRoute' => 'admin.approvals.view', 'createRoute' => 'admin.approvals.create', 'icon' => 'approvals'],
        ['module' => 'announcements', 'label' => __('portal.announcements'), 'view' => __('portal.view_announcements'), 'create' => __('portal.create_announcement'), 'viewRoute' => 'admin.announcements.view', 'createRoute' => 'admin.announcements.create', 'icon' => 'announcements'],
        ['module' => 'notifications', 'label' => __('portal.notifications'), 'view' => __('portal.view_notifications'), 'create' => __('portal.create_notification'), 'viewRoute' => 'admin.notifications.view', 'createRoute' => 'admin.notifications.create', 'icon' => 'notifications'],
    ];

    $secondaryNavigation = $isAdmin
        ? [
            ['module' => 'audit', 'label' => __('portal.audit_logs'), 'view' => __('portal.view_audit_logs'), 'viewRoute' => 'admin.audit.view', 'icon' => 'audit'],
            ['module' => 'integrations', 'label' => __('portal.integrations'), 'view' => __('portal.view_integrations'), 'create' => __('portal.create_integration'), 'viewRoute' => 'admin.integrations.view', 'createRoute' => 'admin.integrations.create', 'icon' => 'integrations'],
            ['module' => 'settings', 'label' => __('portal.settings'), 'view' => __('portal.view_settings'), 'create' => __('portal.update_settings'), 'viewRoute' => 'admin.settings.view', 'createRoute' => 'admin.settings.update', 'icon' => 'settings'],
        ]
        : [];
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
                $href = isset($item['route']) && Route::has($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
                $active = request()->routeIs($item['active']);
            @endphp
            <a href="{{ $href }}" class="sidebar-link {{ $active ? 'is-active' : '' }}">
                <span class="sidebar-icon sidebar-icon-{{ $item['icon'] }}" aria-hidden="true"></span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if ($isAdmin)
            @foreach ($adminGroups as $group)
                @php
                    $open = request()->routeIs($group['viewRoute']) || request()->routeIs($group['createRoute']);
                @endphp
                <details class="sidebar-group" {{ $open ? 'open' : '' }}>
                    <summary class="sidebar-link {{ $open ? 'is-active' : '' }}">
                        <span class="sidebar-icon sidebar-icon-{{ $group['icon'] }}" aria-hidden="true"></span>
                        <span>{{ $group['label'] }}</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="sidebar-subnav">
                        <a href="{{ route($group['viewRoute']) }}" class="{{ request()->routeIs($group['viewRoute']) ? 'is-active' : '' }}">{{ $group['view'] }}</a>
                        <a href="{{ route($group['createRoute']) }}" class="{{ request()->routeIs($group['createRoute']) ? 'is-active' : '' }}">{{ $group['create'] }}</a>
                    </div>
                </details>
            @endforeach
        @endif
    </nav>

    @if ($secondaryNavigation !== [])
        <nav class="sidebar-nav sidebar-nav-secondary">
            <p class="sidebar-nav-label">{{ __('portal.control') }}</p>
            @foreach ($secondaryNavigation as $item)
                @php
                    $open = request()->routeIs($item['viewRoute']) || (isset($item['createRoute']) && request()->routeIs($item['createRoute']));
                @endphp
                <details class="sidebar-group" {{ $open ? 'open' : '' }}>
                    <summary class="sidebar-link {{ $open ? 'is-active' : '' }}">
                        <span class="sidebar-icon sidebar-icon-{{ $item['icon'] }}" aria-hidden="true"></span>
                        <span>{{ $item['label'] }}</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="sidebar-subnav">
                        <a href="{{ route($item['viewRoute']) }}" class="{{ request()->routeIs($item['viewRoute']) ? 'is-active' : '' }}">{{ $item['view'] }}</a>
                        @isset($item['create'])
                            <a href="{{ route($item['createRoute']) }}" class="{{ request()->routeIs($item['createRoute']) ? 'is-active' : '' }}">{{ $item['create'] }}</a>
                        @endisset
                    </div>
                </details>
            @endforeach
        </nav>
    @endif

    <div class="sidebar-status">
        <span class="status-dot"></span>
        <div>
            <p>{{ __('portal.secure_session') }}</p>
            <span>{{ optional(auth()->user()->last_login_at)->diffForHumans() ?? __('portal.active_now') }}</span>
        </div>
    </div>
</aside>

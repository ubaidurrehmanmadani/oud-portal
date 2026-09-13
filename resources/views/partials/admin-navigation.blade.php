@php
    $navigationGroups = [
        'nav_people' => [
            ['admin.users.view', 'portal.users'],
            ['admin.account-requests.index', 'portal.account_requests'],
            ['admin.permissions.view', 'portal.permissions'],
            ['admin.departments.view', 'portal.departments'],
        ],
        'nav_properties' => [
            ['admin.properties.view', 'portal.properties'],
            ['admin.reports.view', 'portal.reports'],
            ['admin.approvals.view', 'portal.approvals'],
        ],
        'nav_content' => [
            ['content.index', 'workspace.manage_content'],
            ['admin.documents.view', 'portal.documents'],
            ['admin.academy.view', 'portal.oud_academy'],
            ['admin.announcements.view', 'portal.announcements'],
        ],
        'nav_workspace' => [
            ['staff.index', 'workspace.documents', ['section' => 'documents']],
            ['staff.index', 'workspace.training', ['section' => 'training']],
            ['staff.index', 'workspace.announcements', ['section' => 'announcements']],
            ['staff.index', 'workspace.search', ['section' => 'search']],
        ],
        'nav_system' => [
            ['admin.notifications.view', 'portal.notifications'],
            ['admin.integrations.view', 'portal.integrations'],
            ['admin.audit.view', 'portal.audit_logs'],
            ['admin.settings.view', 'portal.settings'],
        ],
    ];
@endphp
@foreach ($navigationGroups as $category => $links)
    <details class="admin-nav-category" open>
        <summary>{{ __('portal.'.$category) }}</summary>
        <div class="admin-nav-subcategories">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link[0]) && (!isset($link[2]['section']) || request()->route('section') === $link[2]['section']))
                <a href="{{ route($link[0], $link[2] ?? []) }}" @if($active) class="active" aria-current="page" @endif>{{ __($link[1]) }}</a>
            @endforeach
        </div>
    </details>
@endforeach

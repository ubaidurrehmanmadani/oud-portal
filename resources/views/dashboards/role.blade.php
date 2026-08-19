@extends('layouts.app', ['title' => $title])

@section('content')
    <section class="dashboard-hero">
        <div>
            <p class="eyebrow">{{ $eyebrow }}</p>
            <h1>{{ $title }}</h1>
            <p>{{ __('portal.dashboard_intro') }}</p>
        </div>
        <div class="hero-panel">
            <span>{{ __('portal.today') }}</span>
            <strong>{{ now()->format('d M Y') }}</strong>
            <p>{{ __('portal.protected_modules') }}</p>
        </div>
    </section>

    <section class="metrics-grid" aria-label="{{ __('portal.dashboard_metrics') }}">
        <div class="metric-card">
            <span>{{ __('portal.open_tasks') }}</span>
            <strong>18</strong>
            <p>{{ __('portal.open_tasks_note') }}</p>
        </div>
        <div class="metric-card">
            <span>{{ __('portal.active_users') }}</span>
            <strong>42</strong>
            <p>{{ __('portal.active_users_note') }}</p>
        </div>
        <div class="metric-card">
            <span>{{ __('portal.reports_metric') }}</span>
            <strong>12</strong>
            <p>{{ __('portal.reports_metric_note') }}</p>
        </div>
        <div class="metric-card">
            <span>{{ __('portal.security') }}</span>
            <strong>100%</strong>
            <p>{{ __('portal.security_note') }}</p>
        </div>
    </section>

    <section class="dashboard-grid">
        @foreach ($items as $item)
            <div class="dashboard-card">
                <p>{{ $item }}</p>
                <span>{{ __('portal.module_ready') }}</span>
                <a href="#">{{ __('portal.open_module') }}</a>
            </div>
        @endforeach
    </section>

    <section class="dashboard-lower-grid">
        <div class="activity-panel">
            <div class="panel-heading">
                <h2>{{ __('portal.recent_activity') }}</h2>
                <a href="#">{{ __('portal.view_all') }}</a>
            </div>
            <div class="activity-list">
                <div>
                    <span class="activity-dot"></span>
                    <p>{{ __('portal.activity_dashboard_shell') }}</p>
                    <time>{{ __('portal.now') }}</time>
                </div>
                <div>
                    <span class="activity-dot"></span>
                    <p>{{ __('portal.activity_login_events') }}</p>
                    <time>{{ __('portal.today') }}</time>
                </div>
                <div>
                    <span class="activity-dot"></span>
                    <p>{{ __('portal.activity_language_switcher') }}</p>
                    <time>{{ __('portal.today') }}</time>
                </div>
            </div>
        </div>

        <div class="quick-panel">
            <h2>{{ __('portal.quick_controls') }}</h2>
            <a href="#">{{ __('portal.upload_document') }}</a>
            <a href="#">{{ __('portal.review_approvals') }}</a>
            <a href="#">{{ __('portal.manage_announcements') }}</a>
            <a href="#">{{ __('portal.open_security_settings') }}</a>
        </div>
    </section>
@endsection

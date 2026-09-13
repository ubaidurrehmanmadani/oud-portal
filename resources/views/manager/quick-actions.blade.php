<section class="card manager-overview">
    <p class="eyebrow">{{ __('portal.manager_workspace') }}</p>
    <h2>{{ auth()->user()->department?->name ?? __('portal.manager_unassigned') }}</h2>
    <p class="muted">{{ __('portal.manager_scope') }}</p>
    @if (auth()->user()->department_id)
        <div class="workspace-filters">
            @foreach (['document', 'training', 'announcement'] as $kind)
                <a class="button button-secondary" href="{{ route('content.create', ['kind' => $kind]) }}">{{ __('workspace.create') }} · {{ __('workspace.'.$kind) }}</a>
            @endforeach
            <a class="button button-secondary" href="{{ route('content.index') }}">{{ __('workspace.manage_content') }}</a>
            @if (auth()->user()->canSubmitFinancialReports())
                <a class="button button-primary" href="{{ route('manager.reports.create') }}">{{ __('portal.manager_upload') }}</a>
                <a class="text-link" href="{{ route('manager.reports.index') }}">{{ __('portal.manager_reports') }}</a>
            @else
                <p class="muted">{{ __('portal.manager_permission_help') }}</p>
            @endif
        </div>
    @else
        <p role="status">{{ __('portal.manager_unassigned_help') }}</p>
    @endif
</section>

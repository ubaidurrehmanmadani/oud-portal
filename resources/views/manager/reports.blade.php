@extends('layouts.workspace')
@section('content')
<section class="hero"><div><p class="eyebrow">{{ auth()->user()->department?->name }}</p><h1>{{ __('portal.manager_reports') }}</h1><p>{{ __('portal.manager_report_intro') }}</p></div></section>
<div class="workspace-filters"><a class="button button-primary" href="{{ route('manager.reports.create') }}">{{ __('portal.manager_upload') }}</a></div>
<form method="GET" class="workspace-filters">
    <div class="field"><label for="q">{{ __('workspace.search') }}</label><input id="q" name="q" value="{{ request('q') }}" maxlength="255"></div>
    <div class="field"><label for="status">{{ __('workspace.status') }}</label><select id="status" name="status"><option value="">{{ __('portal.manager_all_statuses') }}</option>@foreach (['draft', 'pending'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __('portal.manager_'.$status) }}</option>@endforeach</select></div>
    <button class="button button-secondary">{{ __('workspace.apply') }}</button><a href="{{ route('manager.reports.index') }}">{{ __('portal.manager_clear') }}</a>
</form>
<section class="card table-scroll"><table class="content-table"><thead><tr>@foreach (['title', 'property', 'period', 'status', 'action'] as $heading)<th>{{ __('workspace.'.$heading) }}</th>@endforeach</tr></thead><tbody>
@forelse ($submissions as $submission)
    <tr><td>{{ $submission->title }}<br><small>{{ $submission->file_name }}</small></td><td>{{ $submission->property->name }}</td><td>{{ $submission->report_month->format('Y-m') }}</td><td><span class="status">{{ __('portal.manager_'.$submission->status) }}</span>@if ($submission->submitted_at)<br><small>{{ $submission->submitted_at->format('Y-m-d H:i') }}</small>@endif</td><td><a href="{{ route('manager.reports.edit', $submission) }}">{{ $submission->status === 'draft' ? __('workspace.edit') : __('workspace.open') }}</a> · <a href="{{ route('manager.reports.download', $submission) }}">{{ __('workspace.download') }}</a></td></tr>
@empty
    <tr><td colspan="5"><h2>{{ __('portal.manager_empty') }}</h2><p>{{ __('portal.manager_empty_help') }}</p></td></tr>
@endforelse
</tbody></table></section>
{{ $submissions->links('pagination.oud') }}
@endsection

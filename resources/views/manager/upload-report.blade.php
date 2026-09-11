@extends('layouts.workspace')
@section('content')
<section class="hero"><div><p class="eyebrow">{{ __('portal.manager_workspace') }}</p><h1>{{ __('portal.manager_upload') }}</h1><p>{{ __('portal.manager_report_intro') }}</p></div></section>
<a class="text-link" href="{{ route('manager.reports.index') }}">{{ __('portal.manager_reports') }}</a>
<ol class="manager-steps" aria-label="{{ __('portal.manager_workflow') }}">@foreach (['manager_step_upload', 'manager_step_check', 'manager_step_submit'] as $step)<li>{{ __('portal.'.$step) }}</li>@endforeach</ol>
@if ($availableProperties->isEmpty())
    <section class="card"><h2>{{ __('portal.manager_no_properties') }}</h2><p>{{ __('portal.manager_no_properties_help') }}</p></section>
@else
    @if ($record->status === 'pending')<p class="card" role="status">{{ __('portal.manager_locked') }}</p>@endif
    <section class="card">
    <form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route('manager.reports.update', $record) : route('manager.reports.store') }}">
        @csrf @if ($record->exists) @method('PUT') @endif
        <fieldset class="manager-fields content-form" @disabled($record->status === 'pending')>
            <legend>{{ __('portal.manager_report_details') }}</legend>
            <div class="field wide"><label for="title">{{ __('workspace.title') }}</label><input id="title" name="title" required maxlength="255" value="{{ old('title', $record->title) }}"></div>
            <div class="field"><label for="property_id">{{ __('workspace.property') }}</label><select id="property_id" name="property_id" required><option value="">{{ __('workspace.none') }}</option>@foreach ($availableProperties as $property)<option value="{{ $property->id }}" @selected(old('property_id', $record->property_id) == $property->id)>{{ $property->name }}</option>@endforeach</select></div>
            <div class="field"><label for="report_month">{{ __('workspace.period') }}</label><input id="report_month" name="report_month" type="month" required value="{{ old('report_month', $record->report_month?->format('Y-m')) }}"></div>
            <div class="field wide manager-upload"><label for="file">{{ __('portal.manager_file') }}</label><input id="file" name="file" type="file" accept=".xlsx,.xls,.pdf" @required(!$record->exists) aria-describedby="file-help"><p id="file-help">{{ __('portal.manager_file_help') }}</p>@if ($record->exists)<p>{{ $record->file_name }}</p>@endif</div>
            <div class="wide"><h2>{{ __('portal.manager_metrics') }}</h2><p class="muted">{{ __('portal.manager_metrics_help') }}</p></div>
            @foreach (['occupancy', 'gross_revenue', 'net_revenue', 'rent'] as $metric)
                <div class="field"><label for="{{ $metric }}">{{ __('portal.manager_'.$metric) }}</label><input id="{{ $metric }}" name="metrics[{{ $metric }}]" type="number" step="0.01" min="{{ $metric === 'net_revenue' ? '-99999999999999' : '0' }}" max="{{ $metric === 'occupancy' ? '100' : '99999999999999' }}" value="{{ old('metrics.'.$metric, data_get($record->metrics, $metric)) }}"></div>
            @endforeach
            <div class="field wide"><label for="notes">{{ __('portal.manager_notes') }}</label><textarea id="notes" name="notes" maxlength="10000">{{ old('notes', $record->notes) }}</textarea></div>
            <div class="wide workspace-filters"><button class="button button-secondary" name="action" value="draft">{{ __('portal.manager_save_draft') }}</button><button class="button button-primary" name="action" value="submit">{{ __('portal.manager_submit') }}</button></div>
        </fieldset>
    </form>
    @if ($record->exists)<a class="text-link" href="{{ route('manager.reports.download', $record) }}">{{ __('workspace.download') }} · {{ $record->file_name }}</a>@endif
    </section>
@endif
@endsection

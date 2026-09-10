@extends('layouts.workspace')
@section('content')
<section class="hero"><div><p class="eyebrow">{{ __('workspace.manage_content') }}</p><h1>{{ isset($record) ? __('workspace.edit') : __('workspace.create') }} · {{ $kind === 'user' ? __('portal.user') : __('workspace.'.$kind) }}</h1></div></section>
<section class="card">
<form method="POST" action="{{ isset($record) ? route('content.update', $record) : route('content.store') }}" enctype="multipart/form-data" class="content-form">
    @csrf
    @if (isset($record)) @method('PUT') @endif
    <input type="hidden" name="kind" value="{{ $kind }}">
    @if (in_array($kind, ['department', 'property', 'user']))
        <div class="field"><label for="name">{{ __('workspace.name') }}</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255"></div>
        @if ($kind === 'user')
            <div class="field"><label for="email">{{ __('workspace.email') }}</label><input type="email" id="email" name="email" value="{{ old('email') }}" required></div>
            <div class="field"><label for="password">{{ __('workspace.password') }}</label><input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"></div>
            <div class="field"><label for="role">{{ __('workspace.role') }}</label><select id="role" name="role">@foreach (\App\Enums\UserRole::options() as $value => $label)<option value="{{ $value }}" @selected(old('role', 'employee') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="department_id">{{ __('workspace.department') }}</label><select id="department_id" name="department_id"><option value="">{{ __('workspace.none') }}</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
            <div class="field"><label for="properties">{{ __('workspace.properties') }}</label><select id="properties" name="properties[]" multiple>@foreach ($availableProperties as $property)<option value="{{ $property->id }}" @selected(in_array($property->id, old('properties', [])))>{{ $property->name }}</option>@endforeach</select></div>
        @else
            @if ($kind === 'property')
                @foreach (['location', 'type', 'total_units'] as $field)<div class="field"><label for="{{ $field }}">{{ __('workspace.'.($field === 'total_units' ? 'units' : $field)) }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'total_units' ? 'number' : 'text' }}" value="{{ old($field, $field === 'total_units' ? 0 : '') }}" @if ($field === 'total_units') min="0" required @endif></div>@endforeach
                <div class="field"><label for="landlords">{{ __('workspace.landlords') }}</label><select id="landlords" name="landlords[]" multiple>@foreach ($landlords as $landlord)<option value="{{ $landlord->id }}" @selected(in_array($landlord->id, old('landlords', [])))>{{ $landlord->name }} · {{ $landlord->email }}</option>@endforeach</select></div>
            @endif
            <div class="field wide"><label for="description">{{ __('workspace.body') }}</label><textarea id="description" name="description">{{ old('description') }}</textarea></div>
        @endif
    @else
        <div class="field wide"><label for="title">{{ __('workspace.title') }}</label><input id="title" name="title" value="{{ old('title', $record->title ?? '') }}" required maxlength="255"></div>
        <div class="field wide"><label for="body">{{ __('workspace.body') }}</label><textarea id="body" name="body">{{ old('body', $record->body ?? '') }}</textarea></div>
        <div class="field"><label for="category">{{ __('workspace.category') }}</label><input id="category" name="category" value="{{ old('category', $record->category ?? '') }}"></div>
        <div class="field"><label for="status">{{ __('workspace.status') }}</label><select id="status" name="status">@foreach ($kind === 'approval' ? ['pending'] : ['published', 'draft'] as $status)<option value="{{ $status }}" @selected(old('status', $record->status ?? '') === $status)>{{ __('workspace.'.$status) }}</option>@endforeach</select></div>
        @if (auth()->user()->role === \App\Enums\UserRole::ADMIN)
            <div class="field"><label for="audience">{{ __('workspace.audience') }}</label><select id="audience" name="audience">@foreach (in_array($kind, ['report', 'approval']) ? ['landlord'] : ['staff', 'landlord', 'admin'] as $audience)<option value="{{ $audience }}" @selected(old('audience', $record->audience ?? '') === $audience)>{{ __('workspace.'.$audience) }}</option>@endforeach</select></div>
            <div class="field"><label for="department_id">{{ __('workspace.department') }}</label><select id="department_id" name="department_id"><option value="">{{ __('workspace.all_departments') }}</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $record->department_id ?? '') == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
            <div class="field"><label for="property_id">{{ __('workspace.property') }}</label><select id="property_id" name="property_id" @required(in_array($kind, ['report', 'approval']))><option value="">{{ __('workspace.none') }}</option>@foreach ($availableProperties as $property)<option value="{{ $property->id }}" @selected(old('property_id', $record->property_id ?? '') == $property->id)>{{ $property->name }}</option>@endforeach</select></div>
        @else<input type="hidden" name="audience" value="staff">@endif
        <div class="field"><label for="published_at">{{ __('workspace.published_at') }}</label><input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', isset($record) ? $record->published_at?->format('Y-m-d\TH:i') : '') }}"></div>
        <div class="field"><label for="file">{{ __('workspace.file') }}</label><input type="file" id="file" name="file">@if (isset($record) && $record->file_name)<small>{{ $record->file_name }}</small>@endif</div>
        @if ($kind === 'report')
            <div class="field"><label for="period">{{ __('workspace.period') }}</label><input id="period" name="period" value="{{ old('period', $record->period ?? '') }}"></div>
            @foreach (['occupancy', 'net_revenue', 'leased_area'] as $metric)<div class="field"><label for="{{ $metric }}">{{ __('workspace.'.$metric) }}</label><input id="{{ $metric }}" name="{{ $metric }}" type="number" min="0" step="0.01" @if ($metric === 'occupancy') max="100" @endif value="{{ old($metric, $record->$metric ?? '') }}"></div>@endforeach
        @endif
        @if ($kind === 'approval')<div class="field"><label for="amount">{{ __('workspace.amount') }} (SAR)</label><input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $record->amount ?? '') }}"></div>@endif
    @endif
    <div class="wide"><button class="button button-primary">{{ __('workspace.save') }}</button></div>
</form>
</section>
@endsection

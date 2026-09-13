@extends('layouts.workspace')
@section('content')
<section class="hero"><div><p class="eyebrow">{{ __('workspace.edit') }}</p><h1>{{ $record->name }}</h1></div></section>
<section class="card"><form method="POST" action="{{ route('accounts.update', ['kind' => $kind, 'id' => $record->id]) }}" class="content-form">@csrf @method('PUT')
    <div class="field"><label for="name">{{ __('workspace.name') }}</label><input id="name" name="name" required value="{{ old('name', $record->name) }}"></div>
    @if ($kind === 'user')
        <div class="field wide"><label><input type="checkbox" name="can_submit_financial_reports" value="1" @checked(old('can_submit_financial_reports', $record->can_submit_financial_reports))> {{ __('portal.manager_permission') }}</label><p class="muted">{{ __('portal.manager_permission_help') }}</p></div>
        <div class="field"><label for="email">{{ __('workspace.email') }}</label><input id="email" name="email" type="email" required value="{{ old('email', $record->email) }}"></div>
        <div class="field"><label for="role">{{ __('workspace.role') }}</label><select id="role" name="role">@foreach (\App\Enums\UserRole::options() as $value => $label)<option value="{{ $value }}" @selected(old('role', $record->role->value) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="department_id">{{ __('workspace.department') }}</label><select id="department_id" name="department_id"><option value="">{{ __('workspace.none') }}</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $record->department_id) == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
        <div class="field"><label for="properties">{{ __('workspace.properties') }}</label><select id="properties" name="properties[]" multiple>@foreach ($availableProperties as $property)<option value="{{ $property->id }}" @selected(in_array($property->id, old('properties', $record->properties->modelKeys())))>{{ $property->name }}</option>@endforeach</select></div>
    @else
        @if ($kind === 'property')
            @foreach (['location', 'type', 'total_units'] as $field)<div class="field"><label for="{{ $field }}">{{ __('workspace.'.($field === 'total_units' ? 'units' : $field)) }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'total_units' ? 'number' : 'text' }}" value="{{ old($field, $record->$field) }}" @if ($field === 'total_units') min="0" required @endif></div>@endforeach
            <div class="field"><label for="landlords">{{ __('workspace.landlords') }}</label><select id="landlords" name="landlords[]" multiple>@foreach ($landlords as $landlord)<option value="{{ $landlord->id }}" @selected(in_array($landlord->id, old('landlords', $record->users->modelKeys())))>{{ $landlord->name }} · {{ $landlord->email }}</option>@endforeach</select></div>
        @endif
        <div class="field wide"><label for="description">{{ __('workspace.body') }}</label><textarea id="description" name="description">{{ old('description', $record->description) }}</textarea></div>
    @endif
    <div class="wide"><button class="button button-primary">{{ __('workspace.save') }}</button></div>
</form></section>
@endsection

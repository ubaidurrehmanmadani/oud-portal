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
@if ($kind === 'user')
<p><a class="button button-secondary" href="{{ route('admin.users.preview',$record) }}">{{ __('permissions.preview') }}</a></p>
@if($record->capabilityDefaults())
<section class="card"><h2>{{ __('permissions.title') }}</h2><p>{{ __('permissions.help') }}</p>
<form method="POST" action="{{ route('admin.users.permissions',$record) }}" class="content-form">@csrf
@foreach($record->capabilityDefaults() as $capability => $default)
<div class="field"><label for="override_{{ $capability }}">{{ __('permissions.'.$capability) }}</label><select id="override_{{ $capability }}" name="overrides[{{ $capability }}]"><option value="">{{ __('permissions.default') }} ({{ __('permissions.'.($default ? 'allow' : 'deny')) }})</option>@foreach(['1'=>'allow','0'=>'deny'] as $value => $label)<option value="{{ $value }}" @selected(array_key_exists($capability,$record->permission_overrides ?? []) && (int)$record->permission_overrides[$capability] === (int)$value)>{{ __('permissions.'.$label) }}</option>@endforeach</select></div>
@endforeach
<div class="field"><label for="permissions_password">{{ __('lifecycle.password') }}</label><input id="permissions_password" name="current_password" type="password" required autocomplete="current-password"></div><button class="button button-primary">{{ __('workspace.save') }}</button></form></section>
@endif
<section class="card">
    <h2>{{ __('lifecycle.title') }}</h2>
    <p>{{ __('lifecycle.help') }}</p>
    <p>{{ __('lifecycle.'.($record->suspended_at ? 'suspended' : 'active')) }}</p>
    <form method="POST" action="{{ route('accounts.lifecycle', $record->id) }}" class="content-form">
        @csrf
        <div class="field"><label for="current_password">{{ __('lifecycle.password') }}</label><input type="password" id="current_password" name="current_password" required autocomplete="current-password"></div>
        <div class="wide">
            @if ($record->id !== auth()->id())
                <button class="button" name="action" value="{{ $record->suspended_at ? 'restore' : 'suspend' }}">{{ __('lifecycle.'.($record->suspended_at ? 'restore' : 'suspend')) }}</button>
            @endif
            @if (!$record->suspended_at && $record->approval_status === 'approved')
                <button class="button" name="action" value="reset_password">{{ __('lifecycle.reset_password') }}</button>
            @endif
        </div>
    </form>
</section>
@endif
@if ($kind === 'user' && $record->suspended_at && $record->id !== auth()->id())
<section class="card"><h2>{{ __('lifecycle.delete') }}</h2><p>{{ __('lifecycle.delete_help') }}</p>
<form method="POST" action="{{ route('accounts.lifecycle', $record->id) }}" class="content-form">@csrf
<input type="hidden" name="action" value="delete">
<div class="field"><label for="delete_password">{{ __('lifecycle.password') }}</label><input id="delete_password" type="password" name="current_password" required autocomplete="current-password"></div>
<div class="field wide"><label><input type="checkbox" name="confirm_delete" value="1" required> {{ __('lifecycle.confirm_delete') }}</label></div>
<button class="button button-secondary">{{ __('lifecycle.delete') }}</button>
</form></section>
@endif
@if (in_array($kind, ['department', 'property']))
<section class="card"><h2>{{ __('setup.title') }}</h2><p>{{ __('setup.help') }}</p>
<form method="POST" class="content-form" action="{{ route('setup.lifecycle', ['kind'=>$kind,'id'=>$record->id]) }}">@csrf
<div class="field"><label for="setup_password">{{ __('lifecycle.password') }}</label><input id="setup_password" type="password" name="current_password" required autocomplete="current-password"></div>
<div class="wide"><button class="button button-secondary" name="action" value="{{ $record->archived_at ? 'restore' : 'archive' }}">{{ __('setup.'.($record->archived_at ? 'restore' : 'archive')) }}</button>
@if($record->archived_at)<button class="button button-secondary" name="action" value="delete">{{ __('setup.delete') }}</button>@endif</div></form></section>
@endif
@endsection

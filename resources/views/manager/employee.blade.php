@extends('layouts.workspace')
@section('content')
<section class="hero"><h1>{{ $member->name }}</h1></section>
<section class="card"><form method="POST" action="{{ route('manager.employees.update',$member) }}" class="content-form">@csrf @method('PUT')
<div class="field"><label for="name">{{ __('workspace.name') }}</label><input id="name" name="name" required value="{{ old('name',$member->name) }}"></div><div class="field"><label for="email">{{ __('workspace.email') }}</label><input id="email" type="email" name="email" required value="{{ old('email',$member->email) }}"></div><button class="button button-primary">{{ __('workspace.save') }}</button></form></section>
<section class="card"><p>{{ __('lifecycle.delete_help') }}</p><form method="POST" action="{{ route('manager.employees.action',$member) }}" class="content-form">@csrf
<div class="field"><label for="action">{{ __('workspace.action') }}</label><select id="action" name="action">@foreach($member->suspended_at ? ['restore','delete'] : ['suspend','reset_password'] as $action)<option value="{{ $action }}">{{ __('lifecycle.'.$action) }}</option>@endforeach</select></div>
<div class="field"><label for="current_password">{{ __('permissions.confirm_password') }}</label><input id="current_password" type="password" name="current_password" required autocomplete="current-password"></div>
@if($member->suspended_at)<div class="field wide"><label><input type="checkbox" name="confirm_delete" value="1"> {{ __('lifecycle.confirm_delete') }}</label></div>@endif
<button class="button button-primary">{{ __('workspace.save') }}</button></form></section>
@endsection

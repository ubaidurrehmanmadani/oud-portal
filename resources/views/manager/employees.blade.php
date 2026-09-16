@extends('layouts.workspace')
@section('content')
<section class="hero"><h1>{{ __('permissions.manage_employees') }}</h1></section>
<section class="card"><h2>{{ __('workspace.create') }}</h2><form method="POST" action="{{ route('manager.employees.store') }}" class="content-form">@csrf
<div class="field"><label for="name">{{ __('workspace.name') }}</label><input id="name" name="name" required maxlength="255"></div>
<div class="field"><label for="email">{{ __('workspace.email') }}</label><input id="email" type="email" name="email" required></div>
<div class="field"><label for="password">{{ __('workspace.password') }}</label><input id="password" type="password" name="password" required minlength="12" autocomplete="new-password"></div><button class="button button-primary">{{ __('workspace.save') }}</button></form></section>
<section class="card table-scroll"><table class="content-table"><thead><tr><th>{{ __('workspace.name') }}</th><th>{{ __('workspace.email') }}</th><th>{{ __('workspace.action') }}</th></tr></thead><tbody>@forelse($members as $member)<tr><td>{{ $member->name }}</td><td>{{ $member->email }}</td><td><a href="{{ route('manager.employees.edit',$member) }}">{{ __('workspace.edit') }}</a></td></tr>@empty<tr><td colspan="3">{{ __('workspace.empty') }}</td></tr>@endforelse</tbody></table></section>{{ $members->links('pagination.oud') }}
@endsection

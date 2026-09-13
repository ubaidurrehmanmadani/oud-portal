@extends('layouts.workspace')
@section('content')
<section class="hero"><div><h1>{{ __('portal.account_requests') }}</h1><p>{{ __('portal.account_review_help') }}</p></div></section>
<section class="card table-scroll"><table class="content-table"><thead><tr><th>{{ __('workspace.name') }}</th><th>{{ __('workspace.email') }}</th><th>{{ __('workspace.role') }}</th><th>{{ __('workspace.action') }}</th></tr></thead><tbody>
@forelse ($requests as $account)
<tr><td>{{ $account->name }}</td><td>{{ $account->email }}</td><td>{{ $account->role->label() }}</td><td>
    <a href="{{ route('accounts.edit', ['kind' => 'user', 'id' => $account->id]) }}">{{ __('workspace.edit') }}</a>
    <form method="POST" action="{{ route('admin.account-requests.decide', $account) }}" class="workspace-filters">@csrf
        <button class="button button-secondary" name="decision" value="approved">{{ __('portal.account_approve') }}</button>
        <button class="button button-secondary" name="decision" value="rejected">{{ __('portal.account_reject') }}</button>
    </form>
</td></tr>
@empty<tr><td colspan="4">{{ __('portal.account_no_requests') }}</td></tr>@endforelse
</tbody></table></section>
{{ $requests->links('pagination.oud') }}
@endsection

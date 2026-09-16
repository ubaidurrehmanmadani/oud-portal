@extends('layouts.app', ['title'=>$title])
@section('content')
<section class="admin-screen"><section class="hero"><div><h1>{{ __('operations.title') }}</h1><p>{{ __('operations.help') }}</p></div></section>
<section class="card"><h2>{{ __('operations.pending') }}</h2>@forelse($counts as $queue=>$total)<p>{{ $queue }}: {{ $total }}</p>@empty<p>{{ __('operations.no_counts') }}</p>@endforelse</section>
<section class="card table-scroll"><h2>{{ __('operations.failed') }}</h2><table class="content-table admin-table"><thead><tr><th>{{ __('operations.queue') }}</th><th>{{ __('workspace.updated') }}</th><th>{{ __('workspace.action') }}</th></tr></thead><tbody>
@forelse($failures ?? [] as $failure)<tr><td>{{ $failure->queue }}</td><td>{{ $failure->failed_at }}</td><td>@if($failure->retryable)<form method="POST" action="{{ route('admin.queue.retry',$failure->uuid) }}">@csrf<label>{{ __('lifecycle.password') }}<input name="current_password" type="password" required autocomplete="current-password"></label><button class="button button-secondary">{{ __('operations.retry') }}</button></form>@else{{ __('operations.operator') }}@endif</td></tr>@empty<tr><td colspan="3">{{ $supported ? __('workspace.empty') : __('workspace.not_configured') }}</td></tr>@endforelse
</tbody></table></section>@if($failures){{ $failures->links('pagination.oud') }}@endif</section>
@endsection

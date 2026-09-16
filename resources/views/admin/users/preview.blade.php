@extends('layouts.workspace')
@section('content')
<section class="hero"><div><h1>{{ __('permissions.preview') }} · {{ $account->name }}</h1><p>{{ __('permissions.preview_help') }}</p></div></section>
<section class="card table-scroll"><table class="content-table"><thead><tr><th>{{ __('workspace.title') }}</th><th>{{ __('workspace.department') }}</th><th>{{ __('workspace.property') }}</th></tr></thead><tbody>
@forelse($items as $item)<tr><td>{{ $item->title }}</td><td>{{ $item->department?->name }}</td><td>{{ $item->property?->name }}</td></tr>@empty<tr><td colspan="3">{{ __('workspace.empty') }}</td></tr>@endforelse
</tbody></table></section>{{ $items->links('pagination.oud') }}
@endsection

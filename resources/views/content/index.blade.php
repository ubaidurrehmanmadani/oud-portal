@extends('layouts.workspace')
@section('content')
<section class="hero"><div><p class="eyebrow">{{ __('workspace.workspace') }}</p><h1>{{ __('workspace.manage_content') }}</h1><p>{{ __('workspace.content_intro') }}</p></div></section>
<div class="workspace-filters">
    @foreach (auth()->user()->role === \App\Enums\UserRole::ADMIN ? [...\App\Http\Controllers\ContentController::KINDS, 'department', 'property', 'user'] : ['document', 'training', 'announcement'] as $kind)
        <a class="button button-secondary" href="{{ route('content.create', ['kind' => $kind]) }}">{{ __('workspace.create') }} · {{ $kind === 'user' ? __('portal.user') : __('workspace.'.$kind) }}</a>
    @endforeach
</div>
<section class="card table-scroll"><table class="content-table"><thead><tr><th>{{ __('workspace.title') }}</th><th>{{ __('workspace.type') }}</th><th>{{ __('workspace.audience') }}</th><th>{{ __('workspace.status') }}</th><th>{{ __('workspace.action') }}</th></tr></thead><tbody>
@forelse ($items as $record)<tr><td>{{ $record->title }}</td><td>{{ __('workspace.'.$record->kind) }}</td><td>{{ $record->property?->name ?? $record->department?->name ?? __('workspace.'.$record->audience) }}</td><td>{{ __('workspace.'.$record->status) }}</td><td>@if ($record->kind !== 'approval' || $record->status === 'pending')<a href="{{ route('content.edit', $record) }}">{{ __('workspace.edit') }}</a>@else<span>{{ $record->decided_at?->format('d M Y H:i') }}</span>@endif</td></tr>@empty<tr><td colspan="5">{{ __('workspace.empty') }}</td></tr>@endforelse
</tbody></table></section>
{{ $items->links('pagination.oud') }}
@endsection

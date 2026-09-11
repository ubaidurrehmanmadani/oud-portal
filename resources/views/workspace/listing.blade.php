@extends('layouts.workspace')
@section('content')
<section class="hero {{ $isLandlord ? 'landlord-hero' : '' }}"><div><p class="eyebrow">{{ $isLandlord ? __('workspace.'.$section.'_eyebrow') : __('workspace.workspace') }}</p><h1>{{ $isLandlord ? __('workspace.'.$section.'_page_title') : __('workspace.'.$section.'_heading') }}</h1><p>{{ $isLandlord ? __('workspace.'.$section.'_page_intro') : __('workspace.'.$section.'_intro') }}</p></div></section>
@if ($isLandlord)@include('workspace.landlord-filters')@endif
@if (!$isLandlord)<form method="GET" class="workspace-filters" role="search">
    @if ($selectedProperty)<input type="hidden" name="property" value="{{ $selectedProperty->id }}">@endif
    <label for="q">{{ __('workspace.search') }}</label><input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('workspace.search_placeholder') }}" maxlength="200">
    @if ($section === 'approvals')<select name="status" aria-label="{{ __('workspace.status') }}"><option value="">{{ __('workspace.all_statuses') }}</option>@foreach (['pending', 'approved', 'rejected'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __('workspace.'.$status) }}</option>@endforeach</select>@endif
    <button class="button button-primary">{{ __('workspace.search') }}</button>
</form>@endif
@if ($section === 'documents' && $isLandlord)
<section class="card landlord-record-list document-list">
    @forelse ($items as $record)<article class="landlord-record"><span><strong>{{ $record->title }}</strong><small>{{ strtoupper(pathinfo($record->file_name ?? '', PATHINFO_EXTENSION)) }} · {{ $record->updated_at->format('d M Y') }}</small></span><a class="row-action" href="{{ route($record->file_path ? 'workspace.download' : 'workspace.show', $record) }}">{{ __('workspace.'.($record->file_path ? 'download' : 'view')) }}</a></article>@empty<p class="empty-state">{{ __('workspace.empty') }}</p>@endforelse
</section>
@elseif ($section === 'documents')
<section class="module-grid document-table"><div class="document-table-head"><span>{{ __('workspace.title') }}</span><span>{{ __('workspace.category') }}</span><span>{{ __('workspace.updated') }}</span><span>{{ __('workspace.action') }}</span></div>
    @forelse ($items as $record)<article class="card module-card"><span class="card-kicker">{{ $record->category }}</span><h3>{{ $record->title }}</h3><p class="document-description">{{ $record->body }}</p><p class="muted">{{ strtoupper(pathinfo($record->file_name ?? '', PATHINFO_EXTENSION)) }} · {{ $record->updated_at->format('d M Y') }}</p><a href="{{ route($record->file_path ? 'workspace.download' : 'workspace.show', $record) }}">{{ __('workspace.'.($record->file_path ? 'download' : 'view')) }}</a></article>@empty<p class="empty-state">{{ __('workspace.empty') }}</p>@endforelse
</section>
@elseif ($section === 'training' || $section === 'search')
<section class="module-grid">
    @forelse ($items as $record)<article class="card module-card">
        @if ($record->kind === 'training')<button type="button" class="training-video-card" data-training-video="{{ $record->id }}"><img class="training-thumbnail" src="{{ asset('oud/assets/training-thumbnail.png') }}" alt=""><span class="training-play" aria-hidden="true">Play</span></button>@endif
        <span class="card-kicker">{{ $record->category ?? __('workspace.'.$record->kind) }}</span><h3>{{ $record->title }}</h3><p class="muted">{{ \Illuminate\Support\Str::limit($record->body, 160) }}</p><a href="{{ route('workspace.show', $record) }}">{{ __('workspace.open') }} →</a>
    </article>@empty<p class="card empty-state">{{ __('workspace.empty') }}</p>@endforelse
</section>
@elseif ($section === 'announcements')
<section class="card"><div class="panel-title"><h2>{{ __('workspace.announcements') }}</h2></div><div class="activity">
    @forelse ($items as $record)<article class="activity-item"><span class="dot"></span><div><h3><a href="{{ route('workspace.show', $record) }}">{{ $record->title }}</a></h3><p class="record-body">{{ $record->body }}</p><time>{{ $record->published_at?->format('d M Y') ?? $record->created_at->format('d M Y') }}</time></div></article>@empty<p class="empty-state">{{ __('workspace.empty') }}</p>@endforelse
</div></section>
@else
<section class="card {{ $section === 'approvals' ? 'approval-list' : 'report-list' }}">
    @forelse ($items as $record)<div class="workspace-row landlord-record"><a href="{{ route('workspace.show', $record) }}"><span><strong>{{ $record->title }}</strong><small>{{ $record->property?->name }} · {{ $record->period ?? $record->updated_at->format('d M Y') }}{{ $section === 'approvals' ? ' · '.__('workspace.'.$record->status) : '' }}</small></span><b class="row-action">{{ __('workspace.'.($section === 'approvals' ? 'review' : 'view')) }}</b></a>@if ($section === 'reports' && $record->file_path)<a class="row-action secondary-action" href="{{ route('workspace.download', $record) }}">{{ __('workspace.download') }}</a>@endif @if ($section === 'approvals' && $record->status === 'pending')<form method="POST" action="{{ route('workspace.decide', $record) }}" class="inline-decision">@csrf<button class="approval-approve" name="decision" value="approved">{{ __('workspace.approve_short') }}</button></form>@endif</div>@empty<p class="empty-state">{{ __('workspace.empty') }}</p>@endforelse
</section>
@endif
{{ $items->links('pagination.oud') }}
@endsection

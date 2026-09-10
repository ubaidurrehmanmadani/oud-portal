@extends('layouts.workspace')
@section('content')
<a class="detail-back" href="{{ route(auth()->user()->role === \App\Enums\UserRole::ADMIN ? 'content.index' : ($isLandlord ? 'landlord.index' : 'staff.index'), ['section' => ['document' => 'documents', 'training' => 'training', 'announcement' => 'announcements', 'report' => 'reports', 'approval' => 'approvals'][$item->kind]]) }}">← {{ __('workspace.back') }}</a>
<section class="hero {{ $isLandlord ? 'landlord-hero' : '' }} detail-hero"><div><p class="eyebrow">{{ $item->property?->name ?? $item->department?->name }} · {{ __('workspace.'.$item->kind) }}</p><h1>{{ $item->title }}</h1><p>{{ $item->period }}</p></div><div class="hero-side"><span>{{ __('workspace.status') }}</span><strong>{{ __('workspace.'.$item->status) }}</strong><small>{{ $item->created_at->format('d M Y') }}</small></div></section>
@if ($item->kind === 'report')
<section class="grid landlord-metrics"><article class="card metric"><span>{{ __('workspace.occupancy') }}</span><strong>{{ $item->occupancy !== null ? $item->occupancy.'%' : '—' }}</strong><p>{{ __('workspace.period') }}</p></article><article class="card metric"><span>{{ __('workspace.net_revenue') }}</span><strong>{{ $item->net_revenue !== null ? 'SAR '.number_format($item->net_revenue / 1000000, 1).'M' : '—' }}</strong><p>{{ __('workspace.period') }}</p></article><article class="card metric"><span>{{ __('workspace.leased_area') }}</span><strong>{{ $item->leased_area !== null ? number_format($item->leased_area).' sqm' : '—' }}</strong><p>{{ __('workspace.units') }}</p></article><article class="card metric"><span>{{ __('workspace.report') }}</span><strong>{{ $item->file_path ? strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) : '—' }}</strong><p>{{ $item->period }}</p></article></section>
@endif
<section class="lower-grid {{ $item->kind === 'report' ? 'report-detail-grid' : 'approval-detail-grid' }}">
    <article class="card"><span class="card-kicker">{{ __('workspace.details') }}</span><h2>{{ $item->title }}</h2><p class="record-body">{{ $item->body }}</p>
        @if ($item->amount !== null)<p>{{ __('workspace.amount') }}: SAR {{ number_format($item->amount, 2) }}</p>@endif
        @if ($item->kind === 'report')<ul class="detail-list"><li>{{ __('workspace.occupancy') }} and leasing performance</li><li>{{ __('workspace.net_revenue') }} summary</li><li>{{ __('workspace.leased_area') }} and operational notes</li></ul>@endif
        @if ($item->file_path)<a class="supporting-file" href="{{ route('workspace.download', $item) }}"><strong>{{ $item->file_name }}</strong><small>{{ __('workspace.download') }}</small></a>@endif
    </article>
    @if ($item->kind === 'approval')
        <article class="card decision-card"><span class="card-kicker">{{ __('workspace.decision') }}</span><h2>{{ __('workspace.review') }}</h2>
            @if ($item->status === 'pending' && $isLandlord)
                <form method="POST" action="{{ route('workspace.decide', $item) }}">@csrf<label for="comment">{{ __('workspace.comment') }}</label><textarea id="comment" name="comment" rows="5" maxlength="5000">{{ old('comment') }}</textarea><div class="decision-actions"><button class="button button-primary" name="decision" value="approved">{{ __('workspace.approve') }}</button><button class="button button-secondary" name="decision" value="rejected">{{ __('workspace.reject') }}</button></div></form>
            @else<p>{{ __('workspace.'.$item->status) }} · {{ $item->decided_at?->format('d M Y H:i') }}</p><p class="record-body">{{ $item->decision_comment }}</p>@endif
        </article>
    @endif
</section>
@endsection

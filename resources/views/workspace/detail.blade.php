@extends('layouts.workspace')
@section('content')
<a class="detail-back" href="{{ route(auth()->user()->role === \App\Enums\UserRole::ADMIN ? 'content.index' : ($isLandlord ? 'landlord.index' : 'staff.index'), ['section' => ['document' => 'documents', 'training' => 'training', 'announcement' => 'announcements', 'report' => 'reports', 'approval' => 'approvals'][$item->kind]]) }}">← {{ __('workspace.back') }}</a>
<section class="hero {{ $isLandlord ? 'landlord-hero' : '' }} detail-hero"><div><p class="eyebrow">{{ $item->property?->name ?? $item->department?->name }} · {{ __('workspace.'.$item->kind) }}</p><h1>{{ $item->title }}</h1><p>{{ $item->period }}</p></div><div class="hero-side"><span>{{ __('workspace.status') }}</span><strong>{{ __('workspace.'.$item->status) }}</strong><small>{{ $item->created_at->format('d M Y') }}</small></div></section>
<section class="lower-grid approval-detail-grid">
    <article class="card"><span class="card-kicker">{{ __('workspace.details') }}</span><h2>{{ $item->title }}</h2><p class="record-body">{{ $item->body }}</p>
        @if ($item->amount !== null)<p>{{ __('workspace.amount') }}: SAR {{ number_format($item->amount, 2) }}</p>@endif
        @if ($item->kind === 'report')<dl class="property-details">@foreach (['occupancy', 'net_revenue', 'leased_area'] as $metric)<div><dt>{{ __('workspace.'.$metric) }}</dt><dd>{{ $item->$metric ?? '—' }}</dd></div>@endforeach</dl>@endif
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

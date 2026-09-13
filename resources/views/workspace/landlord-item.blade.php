@extends('layouts.workspace')
@section('content')
<a class="detail-back" href="{{ route($isLandlord ? 'landlord.index' : 'content.index', ['section' => $item->kind === 'approval' ? 'approvals' : 'reports']) }}">← {{ __('workspace.back') }}</a>
<section class="hero landlord-hero detail-hero"><div><p class="eyebrow">{{ $item->kind === 'approval' ? __('workspace.approval').' · '.$item->property?->name : $item->title }}</p><h1>{{ $item->kind === 'report' ? ($item->period ?: $item->title) : $item->title }}</h1><p>{{ $item->body }}</p></div><div class="hero-side"><span>{{ __('workspace.status') }}</span><strong>{{ __('workspace.'.$item->status) }}</strong><small>{{ $item->kind === 'approval' ? $item->period : $item->published_at?->translatedFormat('d M Y') }}</small></div></section>
@if($item->kind === 'report')
<section class="grid landlord-metrics">
    @foreach(['occupancy' => $item->occupancy !== null ? $item->occupancy.'%' : '—', 'net_revenue' => $item->net_revenue !== null ? 'SAR '.number_format($item->net_revenue / 1000000, 1).'M' : '—', 'leased_area' => $item->leased_area !== null ? number_format($item->leased_area).' '.__('financial.sqm') : '—', 'report_type' => $item->file_name ? strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) : '—'] as $label => $value)<article class="card metric"><span>{{ __('workspace.'.$label) }}</span><strong>{{ $value }}</strong><p>{{ $item->period }}</p></article>@endforeach
</section>
<section class="lower-grid report-detail-grid">
    <article class="card"><span class="card-kicker">{{ __('workspace.report_contents') }}</span><h2>{{ __('workspace.included_sections') }}</h2><ul class="detail-list">@foreach(['section_occupancy', 'section_revenue', 'section_maintenance', 'section_market'] as $label)<li>{{ __('workspace.'.$label) }}</li>@endforeach</ul></article>
    <article class="card report-download-card"><span class="card-kicker">{{ __('workspace.available_file') }}</span><h2>{{ $item->title }}</h2><p>{{ $item->period }} · {{ $item->property?->name }} · {{ strtoupper(pathinfo($item->file_name ?? '', PATHINFO_EXTENSION)) }}</p>@if($item->file_path)<a class="button button-primary" href="{{ route('workspace.download', $item) }}">{{ __('workspace.download_report') }}</a>@endif</article>
</section>
@else
<section class="lower-grid approval-detail-grid">
    <article class="card"><span class="card-kicker">{{ __('workspace.request_details') }}</span><h2>{{ $item->title }}</h2><dl class="property-details">
        <div><dt>{{ __('workspace.property') }}</dt><dd>{{ $item->property?->name }}</dd></div>
        <div><dt>{{ __('workspace.amount') }}</dt><dd>SAR {{ number_format($item->amount ?? 0) }}</dd></div>
        <div><dt>{{ __('workspace.request_type') }}</dt><dd>{{ $item->category ?? '—' }}</dd></div>
        <div><dt>{{ __('workspace.submitted_by') }}</dt><dd>{{ str_contains($item->body ?? '', 'Submitted by ') ? rtrim(\Illuminate\Support\Str::afterLast($item->body, 'Submitted by '), '.') : '—' }}</dd></div>
    </dl>@if($item->file_path)<a class="supporting-file" href="{{ route('workspace.download', $item) }}"><strong>{{ $item->file_name }}</strong><small>{{ __('workspace.supporting_document') }}</small></a>@endif</article>
    <article class="card decision-card"><span class="card-kicker">{{ __('workspace.decision') }}</span><h2>{{ __('workspace.review') }}</h2>
        @if($item->status === 'pending' && $isLandlord)<form method="POST" action="{{ route('workspace.decide', $item) }}">@csrf<label for="approval-comment">{{ __('workspace.comment') }}</label><textarea id="approval-comment" name="comment" rows="5" maxlength="5000">{{ old('comment') }}</textarea><div class="decision-actions"><button class="button button-primary" name="decision" value="approved">{{ __('workspace.approve') }}</button><button class="button button-secondary" name="decision" value="rejected">{{ __('workspace.reject') }}</button></div></form>
        @else<p>{{ __('workspace.'.$item->status) }} · {{ $item->decided_at?->translatedFormat('d M Y H:i') }}</p><p class="record-body">{{ $item->decision_comment }}</p>@endif
    </article>
</section>
@endif
@endsection

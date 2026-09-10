@extends('layouts.workspace')
@section('content')
<section class="hero {{ $isLandlord ? 'landlord-hero' : '' }}">
    <div><p class="eyebrow">{{ __('workspace.workspace') }}</p><h1>{{ $isLandlord ? ($selectedProperty?->name ?? __('workspace.properties')) : __('workspace.welcome') }}</h1><p>{{ __('workspace.'.($isLandlord ? 'landlord_intro' : 'staff_intro')) }}</p></div>
    <div class="hero-side"><span>{{ __('workspace.'.($isLandlord ? 'period' : 'department')) }}</span><strong>{{ $isLandlord ? ($latestReport?->period ?? '—') : (auth()->user()->department?->name ?? __('workspace.general_library')) }}</strong><small>{{ auth()->user()->role->label() }}</small></div>
</section>
<section class="grid">
    @if ($isLandlord)
        @foreach (['occupancy' => $latestReport?->occupancy !== null ? $latestReport->occupancy.'%' : '—', 'net_revenue' => $latestReport?->net_revenue !== null ? 'SAR '.($latestReport->net_revenue >= 1000000 ? number_format($latestReport->net_revenue / 1000000, 1).'M' : number_format($latestReport->net_revenue, 0)) : '—', 'leased_area' => $latestReport?->leased_area !== null ? number_format($latestReport->leased_area).' m²' : '—', 'pending' => $pendingApprovals] as $label => $value)
            <article class="card metric"><span>{{ __('workspace.'.$label) }}</span><strong>{{ $value }}</strong><p>{{ $latestReport?->period }}</p></article>
        @endforeach
    @else
        @foreach (['documents' => $counts['document'] ?? 0, 'training' => $counts['training'] ?? 0, 'announcements' => $counts['announcement'] ?? 0, 'access' => __('workspace.active')] as $label => $value)
            <article class="card metric"><span>{{ __('workspace.'.$label) }}</span><strong class="{{ $label === 'access' ? 'text-metric' : '' }}">{{ $value }}</strong></article>
        @endforeach
    @endif
</section>
@if ($isLandlord)
<section class="landlord-main-grid">
    <article class="card performance-card"><div class="panel-title"><h2>{{ __('workspace.performance') }}</h2></div>
        @if ($latestReport)
            <p class="muted">{{ $latestReport->title }} · {{ $latestReport->period }}</p>
            @if ($latestReport->occupancy !== null)<label for="occupancy">{{ __('workspace.occupancy') }}: {{ $latestReport->occupancy }}%</label><meter id="occupancy" min="0" max="100" value="{{ $latestReport->occupancy }}">{{ $latestReport->occupancy }}%</meter>@endif
            <a class="button button-secondary" href="{{ route('workspace.show', $latestReport) }}">{{ __('workspace.view_report') }}</a>
        @else<p class="muted">{{ __('workspace.no_performance') }}</p>@endif
    </article>
    <article class="card property-card"><div class="panel-title"><h2>{{ $selectedProperty?->name ?? __('workspace.properties') }}</h2></div><dl class="property-details"><div><dt>{{ __('workspace.location') }}</dt><dd>{{ $selectedProperty?->location ?? '—' }}</dd></div><div><dt>{{ __('workspace.type') }}</dt><dd>{{ $selectedProperty?->type ?? '—' }}</dd></div><div><dt>{{ __('workspace.units') }}</dt><dd>{{ $selectedProperty?->total_units ?? '—' }}</dd></div></dl><a href="{{ route('landlord.index', ['section' => 'properties']) }}">{{ __('workspace.view_all') }}</a></article>
</section>
@else
<section class="module-grid">
    @foreach (['documents', 'training', 'announcements'] as $module)
        <article class="card module-card"><span class="card-kicker">{{ __('workspace.'.$module) }}</span><h3>{{ __('workspace.'.$module.'_heading') }}</h3><p class="muted">{{ __('workspace.'.$module.'_intro') }}</p><a href="{{ route('staff.index', ['section' => $module]) }}">{{ __('workspace.open') }} →</a></article>
    @endforeach
</section>
@endif
<section class="lower-grid">
    <article class="card"><div class="panel-title"><h2>{{ __('workspace.recent') }}</h2></div><div class="activity">
        @forelse ($recent as $record)<div class="activity-item"><span class="dot"></span><div><a href="{{ route('workspace.show', $record) }}">{{ $record->title }}</a><time>{{ $record->updated_at->format('d M Y') }}</time></div></div>@empty<p class="muted">{{ __('workspace.empty') }}</p>@endforelse
    </div></article>
    <article class="card"><h2>{{ __('workspace.quick_access') }}</h2><div class="quick-links">@foreach ($isLandlord ? ['reports', 'documents', 'approvals'] : ['documents', 'training', 'search'] as $module)<a href="{{ route($isLandlord ? 'landlord.index' : 'staff.index', ['section' => $module]) }}">{{ __('workspace.'.$module) }}</a>@endforeach</div></article>
</section>
@endsection

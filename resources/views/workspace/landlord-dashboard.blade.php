@extends('layouts.workspace')
@section('content')
<nav class="excel-property-pills" aria-label="{{ __('financial.property_reports') }}">
    @foreach ($properties as $property)<a class="button button-secondary" href="{{ route('landlord.financials', $property) }}">{{ $property->name }}</a>@endforeach
</nav>
<section class="hero landlord-hero"><div><p class="eyebrow">{{ __('financial.property_reports') }}</p><h1>{{ __('financial.title') }}</h1><p>{{ __('financial.intro') }}</p></div></section>
<section class="property-summaries" aria-label="{{ __('workspace.properties') }}">
    @forelse ($properties as $property)
        @php
            $latest = $summaries->get($property->id);
            $financial = new \App\Support\FinancialReport($latest);
        @endphp
        <article class="card property-summary"><span class="card-kicker">{{ $latest?->report_month?->translatedFormat('F Y') ?? $latest?->period ?? __('financial.latest') }}</span><h2>{{ $property->name }}</h2>
            <dl>
                @foreach (['rent' => $financial->componentTotal('rent'), 'service' => $financial->componentTotal('service'), 'total' => $financial->totalRevenue(), 'collection_rate' => $financial->collectionRate()] as $label => $value)
                    <div><dt>{{ __('financial.'.$label) }}</dt><dd>{{ \App\Support\FinancialReport::format($value, $label === 'collection_rate' ? '%' : ' '.__('financial.currency')) }}</dd></div>
                @endforeach
            </dl>
            @unless ($latest?->report_month)<p class="muted">{{ __('financial.no_report') }}</p>@endunless
            <a class="button button-secondary" href="{{ route('landlord.financials', $property) }}">{{ __('workspace.view_report') }}</a>
            @if ($latest && !$latest->report_month)<a class="text-link" href="{{ route('workspace.show', $latest) }}">{{ $latest->title }}</a>@endif
        </article>
    @empty<p class="empty-state">{{ __('workspace.empty') }}</p>@endforelse
</section>
@endsection

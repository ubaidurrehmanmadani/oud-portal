@extends('layouts.workspace')
@section('content')
<nav class="excel-property-pills" aria-label="{{ __('financial.property_reports') }}">
    @foreach ($properties as $property)<a class="button button-secondary" href="{{ route('landlord.financials', $property) }}">{{ $property->name }}</a>@endforeach
</nav>
<section class="hero landlord-hero financial-home-hero"><div><p class="eyebrow">{{ __('financial.property_reports') }}</p><h1>{{ __('financial.title') }}</h1><p>{{ __('financial.intro') }}</p></div></section>
<section class="property-summaries" aria-label="{{ __('workspace.properties') }}">
    @forelse ($properties as $property)
        @php
            $latest = $summaries->get($property->id);
            $financial = new \App\Support\FinancialReport($latest);
        @endphp
        <article class="card property-summary"><span class="card-kicker">{{ $latest?->report_month?->translatedFormat('F Y') ?? $latest?->period ?? __('financial.latest') }}</span><h2>{{ $property->name }}</h2>
            <dl>
                <div><dt>{{ __('financial.total') }} · {{ __('financial.currency') }}</dt><dd>{{ \App\Support\FinancialReport::format($financial->totalRevenue()) }}</dd></div>
                <div><dt>{{ __('workspace.occupancy') }}</dt><dd>{{ __('financial.office') }} {{ \App\Support\FinancialReport::format($financial->value('office_occupancy'), '%') }} · {{ __('financial.retail') }} {{ \App\Support\FinancialReport::format($financial->value('retail_occupancy'), '%') }}</dd></div>
                <div><dt>{{ __('financial.collected') }} · {{ __('financial.currency') }}</dt><dd>{{ \App\Support\FinancialReport::format($financial->value('collected')) }} · {{ \App\Support\FinancialReport::format($financial->collectionRate(), '%') }}</dd></div>
                <div><dt>{{ __('financial.total_area') }}</dt><dd>{{ \App\Support\FinancialReport::format($financial->componentTotal('area'), ' '.__('financial.sqm')) }}</dd></div>
            </dl>
            <div class="summary-profit"><strong>{{ __('financial.profit_loss') }}</strong><p>{{ __('financial.profit_revenue') }}: {{ \App\Support\FinancialReport::format($financial->value('profit_revenue')) }} · {{ __('financial.expenses') }}: {{ \App\Support\FinancialReport::format($financial->expenses()) }}</p><p>{{ __('financial.profit') }}: {{ \App\Support\FinancialReport::format($financial->profit()) }} · {{ __('financial.margin') }}: {{ \App\Support\FinancialReport::format($financial->value('profit_revenue') > 0 && $financial->profit() !== null ? $financial->profit() / $financial->value('profit_revenue') * 100 : null, '%') }}</p></div>
            @unless ($latest?->report_month)<p class="muted">{{ __('financial.no_report') }}</p>@endunless
            <a class="button button-secondary" href="{{ route('landlord.financials', $property) }}">{{ __('workspace.view_report') }}</a>
            @if ($latest && !$latest->report_month)<a class="text-link" href="{{ route('workspace.show', $latest) }}">{{ $latest->title }}</a>@endif
        </article>
    @empty<p class="empty-state">{{ __('workspace.empty') }}</p>@endforelse
</section>
@endsection

@extends('layouts.workspace')
@section('content')
@php
    $format = [\App\Support\FinancialReport::class, 'format'];
    $monthDate = \Illuminate\Support\Carbon::create($year, $month, 1);
@endphp
<a class="text-link" href="{{ route(auth()->user()->dashboardRouteName()) }}">← {{ __('workspace.back') }}</a>
<div class="filter-row">
    <div class="filters">
        <details class="picker"><summary>{{ $selectedProperty->name }}</summary><div>
            @foreach ($properties as $property)<a class="{{ $property->id === $selectedProperty->id ? 'selected' : '' }}" href="{{ route($financialRoute, ['property' => $property, 'year' => $year, 'month' => $month]) }}">{{ $property->name }}</a>@endforeach
        </div></details>
        <details class="picker"><summary>{{ $monthDate->translatedFormat('F Y') }}</summary><div class="month-menu">
            @foreach (range(1, 12) as $monthNumber)<a class="{{ $monthNumber === $month ? 'selected' : '' }}" @if($monthNumber === $month) aria-current="date" @endif href="{{ route($financialRoute, ['property' => $selectedProperty, 'year' => $year, 'month' => $monthNumber]) }}">{{ \Illuminate\Support\Carbon::create($year, $monthNumber, 1)->translatedFormat('F') }}</a>@endforeach
        </div></details>
        <details class="picker"><summary>{{ __('financial.year') }} · {{ $year }}</summary><div>
            @foreach ($years as $availableYear)<a class="{{ $availableYear === $year ? 'selected' : '' }}" href="{{ route($financialRoute, ['property' => $selectedProperty, 'year' => $availableYear, 'month' => $month]) }}">{{ $availableYear }}</a>@endforeach
        </div></details>
    </div>
    <span class="status">{{ $report->record ? __('workspace.'.$report->record->status) : __('financial.no_report') }}</span>
</div>
<section class="hero landlord-hero"><div><p class="eyebrow">{{ __('workspace.property') }} · {{ $monthDate->translatedFormat('F Y') }}</p><h1>{{ $selectedProperty->name }}</h1><p>{{ __('financial.monthly_intro') }}</p></div></section>
@unless ($report->record)<p class="notice">{{ __('financial.no_report') }}</p>@endunless
<section class="kpi-grid">
    @foreach (['rent' => $report->componentTotal('rent'), 'service' => $report->componentTotal('service'), 'total' => $report->totalRevenue(), 'collection_rate' => $report->collectionRate()] as $label => $value)
        <article class="card metric"><span>{{ __('financial.'.$label) }}</span><strong>{{ $format($value, $label === 'collection_rate' ? '%' : '') }}</strong><p>{{ $label === 'collection_rate' ? __('financial.rent_due') : __('financial.currency') }}</p></article>
    @endforeach
</section>
<section class="two-col">
    @include('workspace.financial-year-chart', ['chartType' => 'revenue'])
    @include('workspace.financial-year-chart', ['chartType' => 'occupancy'])
</section>
<section class="two-col">
    @php
        $due = $report->value('rent_due');
        $collected = $report->value('collected');
        $remaining = $due !== null && $collected !== null ? max(0, $due - $collected) : null;
        $officeRent = $report->componentTotal('rent', ['office', 'mezzanine', 'lobby', 'terrace']);
        $retailRent = $report->componentTotal('rent', ['retail', 'outdoor']);
        $rent = $report->componentTotal('rent');
    @endphp
    @include('workspace.financial-donut', ['heading' => 'collection', 'percentage' => $report->collectionRate(), 'values' => ['collected' => $collected, 'remaining' => $remaining]])
    @include('workspace.financial-donut', ['heading' => 'mix', 'percentage' => $rent > 0 && $officeRent !== null ? $officeRent / $rent * 100 : null, 'values' => ['office_mix' => $officeRent, 'retail_mix' => $retailRent]])
</section>
<section class="two-col">
    @include('workspace.financial-bars', ['field' => 'rent', 'heading' => 'revenue_components', 'unit' => __('financial.currency')])
    <article class="card"><h2>{{ __('financial.glance') }}</h2><dl class="facts">
        @foreach (['office_occupancy', 'retail_occupancy'] as $field)<div><dt>{{ __('financial.'.$field) }}</dt><dd>{{ $format($report->value($field), '%') }}</dd></div>@endforeach
        <div><dt>{{ __('financial.total_area') }}</dt><dd>{{ $format($report->componentTotal('area'), ' '.__('financial.sqm')) }}</dd></div>
        <div><dt>{{ __('workspace.leased_area') }}</dt><dd>{{ $format($report->record?->leased_area !== null ? (float) $report->record->leased_area : null, ' '.__('financial.sqm')) }}</dd></div>
        <div><dt>{{ __('workspace.location') }}</dt><dd>{{ $selectedProperty->location ?? '—' }}</dd></div>
        <div><dt>{{ __('workspace.units') }}</dt><dd>{{ $selectedProperty->total_units }}</dd></div>
    </dl></article>
</section>
<details class="report-disclosure"><summary>{{ __('financial.details') }}</summary><p>{{ __('financial.details_intro') }}</p>
    <article class="card"><h2>{{ __('financial.complete_monthly') }}</h2>
        <div class="table-scroll" tabindex="0" role="region" aria-label="{{ __('financial.complete_monthly') }}"><table><thead><tr><th>{{ __('financial.component') }}</th>@foreach (\App\Support\FinancialReport::COMPONENT_FIELDS as $field)<th>{{ __('financial.'.$field) }}</th>@endforeach</tr></thead><tbody>
        @foreach (\App\Support\FinancialReport::COMPONENTS as $component)<tr><th scope="row">{{ __('financial.'.$component) }}</th>@foreach (\App\Support\FinancialReport::COMPONENT_FIELDS as $field)<td>{{ $format($report->value('components.'.$component.'.'.$field)) }}</td>@endforeach</tr>@endforeach
        </tbody></table></div>
        @include('workspace.financial-source-table', ['rows' => data_get($report->record?->financial_data, 'monthly_rows', []), 'caption' => __('financial.monthly_rows')])
    </article>
    <article class="card"><h2>{{ __('financial.forecast') }}</h2><strong class="exact-total">{{ $format($report->sum([$report->value('office_forecast'), $report->value('retail_forecast')]), ' '.__('financial.currency')) }}</strong><dl class="facts">@foreach (['office_forecast', 'retail_forecast'] as $field)<div><dt>{{ __('financial.'.$field) }}</dt><dd>{{ $format($report->value($field), ' '.__('financial.currency')) }}</dd></div>@endforeach</dl><p class="data-note">{{ __('financial.forecast_note') }}</p></article>
    <section class="two-col">
        @include('workspace.financial-bars', ['field' => 'area', 'heading' => 'area_components', 'unit' => __('financial.sqm')])
        @include('workspace.financial-bars', ['field' => 'service', 'heading' => 'service_components', 'unit' => __('financial.currency')])
    </section>
    @include('workspace.financial-bars', ['field' => 'rate', 'heading' => 'rate', 'unit' => __('financial.currency').'/'.__('financial.sqm')])
    <article class="card"><h2>{{ __('financial.reconciliation') }}</h2><dl class="facts">
        @foreach (['saved_total' => $report->value('saved_total'), 'calculated_total' => $report->totalRevenue(), 'difference' => $report->value('saved_total') !== null && $report->totalRevenue() !== null ? $report->value('saved_total') - $report->totalRevenue() : null] as $label => $value)<div><dt>{{ __('financial.'.$label) }}</dt><dd>{{ $format($value, ' '.__('financial.currency')) }}</dd></div>@endforeach
    </dl></article>
    <article class="card"><h2>{{ __('financial.annual') }} · {{ $year }}</h2><details class="chart-values"><summary>{{ __('financial.annual_open') }}</summary>@include('workspace.financial-source-table', ['rows' => data_get($report->record?->financial_data, 'annual_rows', []), 'caption' => __('financial.annual')])</details></article>
    <details class="source-notes"><summary>{{ __('financial.source') }}</summary><p>{{ data_get($report->record?->financial_data, 'source_name') }}</p><p>{{ __('financial.calculation_note') }}</p><p class="record-body">{{ data_get($report->record?->financial_data, 'source_notes') }}</p></details>
</details>
<details class="report-disclosure"><summary>{{ __('financial.profit_loss') }}</summary>
    <p>{{ __('financial.profit_note') }}</p>
    <section class="kpi-grid">@foreach (['profit_revenue' => $report->value('profit_revenue'), 'expenses' => $report->expenses(), 'profit' => $report->profit(), 'margin' => $report->value('profit_revenue') > 0 && $report->profit() !== null ? $report->profit() / $report->value('profit_revenue') * 100 : null] as $label => $value)<article class="card metric"><span>{{ __('financial.'.$label) }}</span><strong>{{ $format($value, $label === 'margin' ? '%' : '') }}</strong><p>{{ $label === 'margin' ? $monthDate->translatedFormat('F Y') : __('financial.currency') }}</p></article>@endforeach</section>
    <section class="two-col">
        @include('workspace.financial-year-chart', ['chartType' => 'profit'])
        <article class="card"><h2>{{ __('financial.expense_breakdown') }}</h2>
            @php $expenses = $report->expenses(); @endphp
            @if ($expenses !== null)
                @php
                    $operationsAngle = $expenses > 0 ? ($report->value('operations') ?? 0) / $expenses * 360 : 0;
                    $maintenanceAngle = $operationsAngle + ($expenses > 0 ? ($report->value('maintenance') ?? 0) / $expenses * 360 : 0);
                @endphp
                <div class="donut-wrap"><div class="donut expense-donut" style="--operations-angle:{{ $operationsAngle }}deg;--maintenance-angle:{{ $maintenanceAngle }}deg"><div><strong>{{ $format($expenses) }}</strong><span>{{ __('financial.currency') }}</span></div></div></div>
            @endif
            <dl class="facts">@foreach (['operations', 'maintenance', 'administration'] as $field)<div><dt>{{ __('financial.'.$field) }}</dt><dd>{{ $format($report->value($field), ' '.__('financial.currency')) }}</dd></div>@endforeach</dl>
        </article>
    </section>
</details>
@if ($report->record)
    <article class="card"><h2>{{ $report->record->title }}</h2><p class="record-body">{{ $report->record->body }}</p><div class="decision-actions">
        @if ($report->record->file_path)<a class="button button-secondary" href="{{ route('workspace.download', $report->record) }}">{{ __('workspace.download') }} · {{ $report->record->file_name }}</a>@endif
        <button type="button" data-print-report>{{ __('financial.print') }}</button>
    </div></article>
@endif
@endsection

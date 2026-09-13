@php
    $series = match ($chartType) {
        'occupancy' => ['office_occupancy', 'retail_occupancy'],
        'profit' => ['profit_revenue', 'expenses'],
        default => ['rent', 'service'],
    };
    $chartTitle = match ($chartType) { 'occupancy' => 'occupancy_year', 'profit' => 'profit_chart', default => 'revenue_year' };
    $chartRows = collect(range(1, 12))->mapWithKeys(function ($monthNumber) use ($history, $chartType) {
        $monthly = $history->get($monthNumber, new \App\Support\FinancialReport(null));
        return [$monthNumber => match ($chartType) {
            'occupancy' => [$monthly->value('office_occupancy'), $monthly->value('retail_occupancy')],
            'profit' => [$monthly->value('profit_revenue'), $monthly->expenses()],
            default => [$monthly->componentTotal('rent'), $monthly->componentTotal('service')],
        }];
    });
    $chartMaximum = $chartType === 'occupancy' ? 100 : max(1, $chartRows->map(fn ($values) => $chartType === 'revenue' ? array_sum($values) : max($values))->max() ?? 0);
@endphp
<article class="card"><h2>{{ __('financial.'.$chartTitle) }}</h2><p class="subtitle">{{ $year }} · {{ $chartType === 'occupancy' ? '%' : __('financial.currency') }}</p>
    <svg class="{{ $chartType === 'occupancy' ? 'line-chart' : 'revenue-svg' }}" viewBox="0 0 620 260" role="img" aria-label="{{ __('financial.'.$chartTitle) }}">
        @foreach (range(0, 4) as $tick)
            <line class="grid-line" x1="68" x2="610" y1="{{ 210 - $tick * 45 }}" y2="{{ 210 - $tick * 45 }}"/>
            <text x="60" y="{{ 214 - $tick * 45 }}" text-anchor="end">{{ $chartType === 'occupancy' ? $tick * 25 .'%' : \App\Support\FinancialReport::axisLabel($chartMaximum * $tick / 4) }}</text>
        @endforeach
        @foreach ($chartRows as $monthNumber => $values)
            @php $position = 90 + ($monthNumber - 1) * 45; @endphp
            <text x="{{ $position }}" y="238" text-anchor="middle">{{ str_pad($monthNumber, 2, '0', STR_PAD_LEFT) }}</text>
            @if ($monthNumber === $month)<line x1="{{ $position - 12 }}" x2="{{ $position + 12 }}" y1="245" y2="245" stroke="currentColor" stroke-width="2"/>@endif
            @foreach ($values as $seriesIndex => $value)
                @if ($value !== null)
                    @php $height = $value / $chartMaximum * 180; @endphp
                    @if ($chartType === 'occupancy')
                        @php $previous = $chartRows->get($monthNumber - 1)[$seriesIndex] ?? null; @endphp
                        @if ($previous !== null)<line class="{{ $seriesIndex ? 'retail-line' : 'office-line' }}" x1="{{ $position - 45 }}" y1="{{ 210 - $previous / 100 * 180 }}" x2="{{ $position }}" y2="{{ 210 - $height }}"/>@endif
                        <circle class="{{ $seriesIndex ? 'retail-line' : 'office-line' }}" cx="{{ $position }}" cy="{{ 210 - $height }}" r="3"><title>{{ $monthNumber }} · {{ __('financial.'.$series[$seriesIndex]) }}: {{ $format($value, '%') }}</title></circle>
                    @else
                        @php
                            $barPosition = $chartType === 'profit' ? $position - 13 + $seriesIndex * 13 : $position - 11;
                            $barTop = 210 - $height - ($chartType === 'revenue' && $seriesIndex ? ($values[0] ?? 0) / $chartMaximum * 180 : 0);
                        @endphp
                        <rect x="{{ $barPosition }}" y="{{ $barTop }}" width="{{ $chartType === 'profit' ? 12 : 22 }}" height="{{ $height }}" fill="{{ $seriesIndex ? '#8A7155' : '#5E6655' }}"><title>{{ $monthNumber }} · {{ __('financial.'.$series[$seriesIndex]) }}: {{ $format($value) }} {{ __('financial.currency') }}</title></rect>
                    @endif
                @endif
            @endforeach
        @endforeach
    </svg>
    <div class="legend">@foreach ($series as $label)<span><i class="swatch {{ $loop->last ? 's1' : '' }}"></i>{{ __('financial.'.$label) }}</span>@endforeach</div>
    <details class="chart-values"><summary>{{ __('financial.monthly_figures') }}</summary><div class="table-scroll" tabindex="0" role="region" aria-label="{{ __('financial.'.$chartTitle) }}"><table><thead><tr><th>{{ __('financial.month') }}</th>@foreach ($series as $label)<th>{{ __('financial.'.$label) }}</th>@endforeach @if($chartType !== 'occupancy')<th>{{ __('financial.'.($chartType === 'profit' ? 'profit' : 'total')) }}</th>@endif</tr></thead><tbody>
        @foreach ($chartRows as $monthNumber => $values)<tr @if($monthNumber === $month) aria-current="date" @endif><th scope="row"><a href="{{ route($financialRoute, ['property' => $selectedProperty, 'year' => $year, 'month' => $monthNumber]) }}">{{ \Illuminate\Support\Carbon::create($year, $monthNumber, 1)->translatedFormat('F') }}</a></th>@foreach ($values as $value)<td>{{ $format($value, $chartType === 'occupancy' ? '%' : '') }}</td>@endforeach @if($chartType !== 'occupancy')<td>{{ $format($values[0] !== null && $values[1] !== null ? ($chartType === 'profit' ? $values[0] - $values[1] : array_sum($values)) : null) }}</td>@endif</tr>@endforeach
    </tbody></table></div></details>
</article>

@php
    $componentValues = collect(\App\Support\FinancialReport::COMPONENTS)->mapWithKeys(fn ($component) => [$component => $report->value('components.'.$component.'.'.$field)]);
    $maximum = max(1, $componentValues->max() ?? 0);
@endphp
<article class="card"><h2>{{ __('financial.'.$heading) }}</h2><div class="hbars">
    @foreach ($componentValues as $component => $value)
        <div class="bar-row"><div><span>{{ __('financial.'.$component) }}</span><strong>{{ $format($value, ' '.$unit) }}</strong></div><div class="track" aria-hidden="true"><span style="width:{{ $value !== null ? $value / $maximum * 100 : 0 }}%"></span></div></div>
    @endforeach
</div></article>

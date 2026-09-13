<article class="card"><h2>{{ __('financial.'.$heading) }}</h2><p class="subtitle">{{ __('financial.selected_month') }} · {{ __('financial.currency') }}</p>
    <div class="donut-wrap">
        @if ($percentage !== null)<div class="donut" style="--angle:{{ min(100, max(0, $percentage)) * 3.6 }}deg" role="img" aria-label="{{ __('financial.'.$heading).': '.$format($percentage, '%') }}"><div><strong>{{ $format($percentage, '%') }}</strong><span>{{ __('financial.'.array_key_first($values)) }}</span></div></div>@else<p class="data-note">{{ __('financial.no_figures') }}</p>@endif
        <div class="donut-key">@foreach ($values as $label => $value)<p><span class="swatch {{ $loop->last ? 's1' : '' }}"></span> {{ __('financial.'.$label) }}<br><b>{{ $format($value) }}</b></p>@endforeach</div>
    </div>
</article>

@if ($rows)
    <div class="table-scroll" tabindex="0" role="region" aria-label="{{ $caption }}"><table><caption>{{ $caption }}</caption><thead><tr><th>{{ __('financial.metric') }}</th><th>{{ __('financial.reference') }}</th>@foreach (\App\Support\FinancialReport::COMPONENTS as $component)<th>{{ __('financial.'.$component) }}</th>@endforeach</tr></thead><tbody>
        @foreach ($rows as $sourceRow)<tr><th scope="row">{{ $sourceRow['label'] ?? '—' }}</th><td>{{ $sourceRow['reference'] ?? '—' }}</td>@foreach (range(0, 5) as $valueIndex)<td>{{ $sourceRow['values'][$valueIndex] ?? '—' }}</td>@endforeach</tr>@endforeach
    </tbody></table></div>
@else<p class="data-note">{{ __('financial.no_figures') }}</p>@endif

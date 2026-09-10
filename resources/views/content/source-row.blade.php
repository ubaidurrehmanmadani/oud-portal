<tr>
    @foreach (['label' => 'metric', 'reference' => 'reference'] as $field => $label)
        <td><input aria-label="{{ __('financial.'.$label) }}" name="financial_data[{{ $table }}][{{ $rowIndex }}][{{ $field }}]" maxlength="{{ $field === 'label' ? 255 : 100 }}" value="{{ $sourceRow[$field] ?? '' }}"></td>
    @endforeach
    @foreach (\App\Support\FinancialReport::COMPONENTS as $valueIndex => $component)
        <td><input aria-label="{{ __('financial.'.$component) }}" name="financial_data[{{ $table }}][{{ $rowIndex }}][values][{{ $valueIndex }}]" maxlength="100" value="{{ $sourceRow['values'][$valueIndex] ?? '' }}"></td>
    @endforeach
    <td><button type="button" class="button button-secondary" data-remove-source-row>{{ __('financial.remove_row') }}</button></td>
</tr>

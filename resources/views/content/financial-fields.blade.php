<fieldset class="wide financial-fields">
    <legend>{{ __('financial.title') }}</legend>
    <p>{{ __('financial.monthly_help') }}</p>
    <div class="field"><label for="report_month">{{ __('financial.month') }}</label><input id="report_month" name="report_month" type="month" min="1900-01" max="2100-12" value="{{ substr(old('report_month', isset($record) ? $record->report_month?->format('Y-m') : '') ?? '', 0, 7) }}"></div>
    <h3>{{ __('financial.components') }}</h3>
    <div class="table-scroll"><table class="content-table"><thead><tr><th>{{ __('financial.component') }}</th>@foreach (\App\Support\FinancialReport::COMPONENT_FIELDS as $field)<th>{{ __('financial.'.$field) }}</th>@endforeach</tr></thead><tbody>
    @foreach (\App\Support\FinancialReport::COMPONENTS as $component)
        <tr><th scope="row">{{ __('financial.'.$component) }}</th>
        @foreach (\App\Support\FinancialReport::COMPONENT_FIELDS as $field)
            <td><input aria-label="{{ __('financial.'.$component).' · '.__('financial.'.$field) }}" name="financial_data[components][{{ $component }}][{{ $field }}]" type="number" min="0" max="99999999999999" step="0.01" value="{{ old('financial_data.components.'.$component.'.'.$field, data_get($record->financial_data ?? [], 'components.'.$component.'.'.$field)) }}"></td>
        @endforeach</tr>
    @endforeach
    </tbody></table></div>
    <div class="content-form">
    @foreach (\App\Support\FinancialReport::FIELDS as $field)
        <div class="field"><label for="financial-{{ $field }}">{{ __('financial.'.$field) }}</label><input id="financial-{{ $field }}" name="financial_data[{{ $field }}]" type="number" min="0" step="0.01" max="{{ str_ends_with($field, '_occupancy') ? 100 : 99999999999999 }}" value="{{ old('financial_data.'.$field, data_get($record->financial_data ?? [], $field)) }}"></div>
    @endforeach
        <div class="field wide"><label for="source_name">{{ __('financial.source_name') }}</label><input id="source_name" name="financial_data[source_name]" maxlength="255" value="{{ old('financial_data.source_name', data_get($record->financial_data ?? [], 'source_name')) }}"></div>
        <div class="field wide"><label for="source_notes">{{ __('financial.source_notes') }}</label><textarea id="source_notes" name="financial_data[source_notes]" maxlength="10000">{{ old('financial_data.source_notes', data_get($record->financial_data ?? [], 'source_notes')) }}</textarea></div>
    </div>
    <details class="report-disclosure"><summary>{{ __('financial.source_tables') }}</summary><p>{{ __('financial.source_help') }}</p>
        @foreach (['monthly_rows', 'annual_rows'] as $table)
            <section data-source-table="{{ $table }}"><h3>{{ __('financial.'.$table) }}</h3>
                <div class="table-scroll"><table class="content-table"><thead><tr><th>{{ __('financial.metric') }}</th><th>{{ __('financial.reference') }}</th>@foreach (\App\Support\FinancialReport::COMPONENTS as $component)<th>{{ __('financial.'.$component) }}</th>@endforeach<th>{{ __('workspace.actions') }}</th></tr></thead><tbody>
                @foreach (old('financial_data.'.$table, data_get($record->financial_data ?? [], $table)) ?: [[]] as $rowIndex => $sourceRow)
                    @include('content.source-row', ['rowIndex' => $rowIndex, 'sourceRow' => $sourceRow])
                @endforeach
                </tbody></table></div>
                <template>@include('content.source-row', ['rowIndex' => '__INDEX__', 'sourceRow' => []])</template>
                <button type="button" class="button button-secondary" data-add-source-row>{{ __('financial.add_row') }}</button>
            </section>
        @endforeach
    </details>
</fieldset>

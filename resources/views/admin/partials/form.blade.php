<section class="admin-screen admin-screen-{{ $section }}">
    <div class="admin-form-panel admin-form-panel-only">
        <div class="panel-heading">
            <h2>{{ $formTitle ?? $title }}</h2>
            <a href="{{ url()->previous() }}">{{ __('portal.back_to_view') }}</a>
        </div>
        @isset($formIntro)
            <p class="admin-form-intro">{{ $formIntro }}</p>
        @endisset

        <div class="admin-form-grid">
            @foreach ($formFields as $field)
                @php
                    $type = $field['type'] ?? 'text';
                    $isWide = (bool) ($field['wide'] ?? false);
                @endphp

                @if ($type === 'checkbox')
                    <label class="check-field admin-check-field {{ $isWide ? 'field-wide' : '' }}">
                        <input type="checkbox" @checked($field['checked'] ?? false)>
                        <span>{{ $field['label'] }}</span>
                    </label>
                @else
                    <label class="field {{ $isWide ? 'field-wide' : '' }}">
                        <span>{{ $field['label'] }}</span>

                        @if ($type === 'select')
                            <select>
                                @foreach (($field['options'] ?? []) as $option)
                                    <option>{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'textarea')
                            <textarea rows="{{ $field['rows'] ?? 4 }}" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                        @elseif ($type === 'file')
                            <input type="file" @if(isset($field['accept'])) accept="{{ $field['accept'] }}" @endif>
                        @else
                            <input type="{{ $type }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                        @endif
                    </label>
                @endif
            @endforeach
        </div>

        <div class="admin-form-actions">
            <a href="#" class="button button-secondary">{{ __('portal.cancel') }}</a>
            <a href="#" class="button button-primary">{{ $primaryAction }}</a>
        </div>
    </div>
</section>

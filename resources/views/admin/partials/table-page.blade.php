<section class="admin-screen admin-screen-{{ $section }}">
    <details class="admin-filter-panel">
        <summary>
            <span class="admin-filter-title">{{ __('portal.filters') }}</span>
            <span class="admin-filter-copy">{{ __('portal.show_hide_filters') }}</span>
            <span class="admin-filter-chevron" aria-hidden="true"></span>
        </summary>
        <div class="admin-filter-body">
            <label class="field">
                <span>{{ __('portal.keyword') }}</span>
                <input type="search" placeholder="{{ __('portal.search_this_screen') }}">
            </label>
            <label class="field">
                <span>{{ __('portal.status') }}</span>
                <select>
                    @foreach ($filters as $filter)
                        <option>{{ $filter }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>{{ __('portal.date_from') }}</span>
                <input type="date">
            </label>
            <label class="field">
                <span>{{ __('portal.date_to') }}</span>
                <input type="date">
            </label>
        </div>
    </details>

    <section class="admin-table-panel admin-table-panel-only">
        <div class="panel-heading">
            <h2>{{ $tableTitle ?? __('portal.overview') }}</h2>
            <a href="#">{{ __('portal.view_all') }}</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                        <th>{{ __('portal.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                            <td>
                                <div class="table-actions">
                                    <a href="#" class="table-action">{{ __('portal.view') }}</a>
                                    <a href="#" class="table-action table-action-dark">{{ __('portal.manage') }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</section>

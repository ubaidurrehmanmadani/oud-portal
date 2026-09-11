<form method="GET" class="workspace-filters landlord-filters" role="search">
    <div><label for="property">{{ __('workspace.property') }}</label><select id="property" name="property"><option value="">{{ __('workspace.all_properties') }}</option>@foreach($properties as $property)<option value="{{ $property->id }}" @selected($selectedProperty?->id === $property->id)>{{ $property->name }}</option>@endforeach</select></div>
    <div><label for="q">{{ __('workspace.search') }}</label><input id="q" name="q" type="search" maxlength="200" value="{{ request('q') }}" placeholder="{{ __('workspace.search_placeholder') }}"></div>
    @if($section !== 'properties')
        <div><label for="category">{{ __('workspace.category') }}</label><select id="category" name="category"><option value="">{{ __('workspace.all_categories') }}</option>@foreach($categories as $category)<option @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></div>
        @if($section === 'approvals')<div><label for="status">{{ __('workspace.status') }}</label><select id="status" name="status"><option value="">{{ __('workspace.all_statuses') }}</option>@foreach(['pending', 'approved', 'rejected'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __('workspace.'.$status) }}</option>@endforeach</select></div>@endif
        @if($section === 'reports')<div><label for="period">{{ __('workspace.period') }}</label><input id="period" name="period" value="{{ request('period') }}" maxlength="100"></div>@endif
        <div><label for="sort">{{ __('workspace.sort') }}</label><select id="sort" name="sort">@foreach(['newest', 'oldest', 'title'] as $sort)<option value="{{ $sort }}" @selected(request('sort', 'newest') === $sort)>{{ __('workspace.'.$sort) }}</option>@endforeach</select></div>
    @endif
    <button class="button button-primary">{{ __('workspace.apply') }}</button><a class="button button-secondary" href="{{ route('landlord.index', ['section' => $section]) }}">{{ __('workspace.reset_filters') }}</a>
</form>

@extends('layouts.workspace')
@section('content')
<section class="hero landlord-hero"><div><p class="eyebrow">{{ __('workspace.workspace') }}</p><h1>{{ __('workspace.properties_heading') }}</h1><p>{{ __('workspace.properties_intro') }}</p></div></section>
<section class="module-grid">
    @forelse ($properties->when($selectedProperty, fn ($rows) => $rows->where('id', $selectedProperty->id)) as $property)
        <article class="card property-card"><span class="card-kicker">{{ $property->type }}</span><h2>{{ $property->name }}</h2><dl class="property-details"><div><dt>{{ __('workspace.location') }}</dt><dd>{{ $property->location }}</dd></div><div><dt>{{ __('workspace.units') }}</dt><dd>{{ $property->total_units }}</dd></div></dl><p class="muted">{{ $property->description }}</p><a class="button button-secondary" href="{{ route('dashboard.landlord', ['property' => $property->id]) }}">{{ __('workspace.performance') }}</a></article>
    @empty<p class="card empty-state">{{ __('workspace.no_properties') }}</p>@endforelse
</section>
@endsection

@extends('layouts.workspace')
@section('content')
<section class="hero landlord-hero"><div><p class="eyebrow">{{ __('workspace.properties_eyebrow') }}</p><h1>{{ __('workspace.properties_page_title') }}</h1><p>{{ __('workspace.properties_page_intro') }}</p></div></section>
<section class="card landlord-record-list property-list">
    @include('workspace.landlord-filters')
    @forelse ($propertyItems as $property)
        <article class="landlord-record"><span><strong>{{ $property->name }}</strong><small>{{ $property->type }} · {{ $property->location }} · {{ $property->total_units }} {{ __('workspace.units_short') }} · {{ $property->description }}</small></span><a class="row-action" href="{{ route('landlord.financials', $property) }}">{{ __('workspace.view') }}</a></article>
    @empty<p class="card empty-state">{{ __('workspace.no_properties') }}</p>@endforelse
</section>
@endsection

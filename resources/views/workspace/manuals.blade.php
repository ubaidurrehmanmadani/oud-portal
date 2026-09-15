@extends('layouts.workspace')
@section('content')
<section class="hero"><div><h1>{{ __('manual.title') }}</h1><p>{{ __('manual.intro') }}</p></div></section>
<p>{{ __('manual.scope') }}</p>
<div class="two-col">
@foreach (['en' => 'English', 'ar' => 'العربية'] as $locale => $label)
<section class="card"><h2 lang="{{ $locale }}">{{ $label }}</h2>
<div class="workspace-filters">
<a class="button button-primary" href="{{ route('manuals.pdf', $locale) }}" target="_blank" rel="noopener">{{ __('manual.open') }}</a>
<a class="button button-secondary" href="{{ route('manuals.pdf', ['locale' => $locale, 'download' => 1]) }}" download>{{ __('manual.download') }}</a>
</div></section>
@endforeach
</div>
@endsection

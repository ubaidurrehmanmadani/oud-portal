@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="page-heading">
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        <p>This role dashboard is ready for the next portal modules from the requirements.</p>
    </div>

    <div class="dashboard-grid">
        @foreach ($items as $item)
            <div class="dashboard-card">
                <p>{{ $item }}</p>
                <span>Module placeholder</span>
            </div>
        @endforeach
    </div>
@endsection

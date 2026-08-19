@extends('layouts.app', ['title' => $formTitle ?? $title])

@section('content')
    @include('admin.partials.form')
@endsection

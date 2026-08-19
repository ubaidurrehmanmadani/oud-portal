@extends('layouts.auth', ['title' => 'Forgot Password | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">Password help</p>
        <h2>Forgot password</h2>
        <p>Enter your email address and the portal will send a secure reset link.</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus>
        </div>

        <button type="submit" class="button button-primary button-full">
            Send reset link
        </button>
    </form>

    <p class="form-footer">
        Remembered your password?
        <a href="{{ route('login') }}">Back to login</a>
    </p>
@endsection

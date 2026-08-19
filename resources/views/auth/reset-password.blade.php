@extends('layouts.auth', ['title' => 'Reset Password | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">Secure reset</p>
        <h2>Set a new password</h2>
        <p>Choose a new password with at least eight characters.</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email', $email) }}" required autofocus>
        </div>

        <div class="field">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="button button-primary button-full">
            Reset password
        </button>
    </form>
@endsection

@extends('layouts.auth', ['title' => 'Login | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">Welcome back</p>
        <h2>Login to your account</h2>
        <p>Use the same login page for Admin, Department Manager, Employee, and Landlord access.</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="field">
            <div class="field-row">
                <label for="password">Password</label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>

        <label class="check-field">
            <input type="checkbox" name="remember" value="1">
            Remember me
        </label>

        <button type="submit" class="button button-primary button-full">
            Login
        </button>
    </form>

    <p class="form-footer">
        Need an account?
        <a href="{{ route('register') }}">Sign up</a>
    </p>
@endsection

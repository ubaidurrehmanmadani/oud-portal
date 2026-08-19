@extends('layouts.auth', ['title' => 'Sign Up | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">Create access</p>
        <h2>Sign up</h2>
        <p>Create an account and choose the role assigned for portal access.</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
        </div>

        <div class="field">
            <label for="role">Account role</label>
            <select id="role" name="role" required>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', 'employee') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="button button-primary button-full">
            Create account
        </button>
    </form>

    <p class="form-footer">
        Already have an account?
        <a href="{{ route('login') }}">Login</a>
    </p>
@endsection

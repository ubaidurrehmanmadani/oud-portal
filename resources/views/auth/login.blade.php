@extends('layouts.auth', ['title' => 'Login | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">{{ __('portal.welcome_back') }}</p>
        <h2>{{ __('portal.login_title') }}</h2>
        <p>{{ __('portal.login_intro') }}</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="email">{{ __('portal.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="field">
            <div class="field-row">
                <label for="password">{{ __('portal.password') }}</label>
                <a href="{{ route('password.request') }}">{{ __('portal.forgot_password_link') }}</a>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>

        <label class="check-field">
            <input type="checkbox" name="remember" value="1">
            {{ __('portal.remember_me') }}
        </label>

        <button type="submit" class="button button-primary button-full">
            {{ __('portal.login_button') }}
        </button>
    </form>

    <p class="form-footer">
        {{ __('portal.need_account') }}
        <a href="{{ route('register') }}">{{ __('portal.sign_up') }}</a>
    </p>
@endsection

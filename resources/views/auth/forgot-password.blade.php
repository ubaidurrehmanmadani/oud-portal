@extends('layouts.auth', ['title' => 'Forgot Password | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">{{ __('portal.password_help') }}</p>
        <h2>{{ __('portal.forgot_password_title') }}</h2>
        <p>{{ __('portal.forgot_password_intro') }}</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="email">{{ __('portal.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus>
        </div>

        <button type="submit" class="button button-primary button-full">
            {{ __('portal.send_reset_link') }}
        </button>
    </form>

    <p class="form-footer">
        {{ __('portal.remembered_password') }}
        <a href="{{ route('login') }}">{{ __('portal.back_to_login') }}</a>
    </p>
@endsection

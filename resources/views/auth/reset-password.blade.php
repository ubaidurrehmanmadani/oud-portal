@extends('layouts.auth', ['title' => 'Reset Password | OUD Portal'])

@section('content')
    <div class="form-heading">
        <p class="eyebrow">{{ __('portal.secure_reset') }}</p>
        <h2>{{ __('portal.reset_password_title') }}</h2>
        <p>{{ __('portal.reset_password_intro') }}</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">{{ __('portal.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email', $email) }}" required autofocus>
        </div>

        <div class="field">
            <label for="password">{{ __('portal.new_password') }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('portal.confirm_new_password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="button button-primary button-full">
            {{ __('portal.reset_password_button') }}
        </button>
    </form>
@endsection

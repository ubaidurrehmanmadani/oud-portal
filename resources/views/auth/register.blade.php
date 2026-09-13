@extends('layouts.auth', ['title' => 'Sign Up | OUD Portal'])

@section('content')
    <div class="auth-heading">
        <p class="eyebrow">{{ __('portal.create_access') }}</p>
        <h2>{{ __('portal.sign_up_title') }}</h2>
        <p>{{ __('portal.sign_up_intro') }}</p>
    </div>

    @include('auth.partials.errors')

    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="name">{{ __('portal.full_name') }}</label>
            <input id="name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="field">
            <label for="email">{{ __('portal.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
        </div>

        <div class="field">
            <label for="role">{{ __('portal.account_role') }}</label>
            <select id="role" name="role" required>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', 'employee') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="muted">{{ __('portal.account_privileged_notice') }}</p>
        </div>

        <div class="field">
            <label for="password">{{ __('portal.password') }}</label>
            <div class="password-field"><input id="password" name="password" type="password" autocomplete="new-password" required><button type="button" class="password-toggle" aria-label="{{ __('workspace.show_password') }}" aria-pressed="false">{{ __('workspace.show_password') }}</button></div>
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('portal.confirm_password') }}</label>
            <div class="password-field"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><button type="button" class="password-toggle" aria-label="{{ __('workspace.show_password') }}" aria-pressed="false">{{ __('workspace.show_password') }}</button></div>
        </div>

        <button type="submit" class="button button-primary button-full">
            {{ __('portal.create_account') }}
        </button>
    </form>

    <p class="auth-footer">
        {{ __('portal.already_have_account') }}
        <a href="{{ route('login') }}">{{ __('portal.login_button') }}</a>
    </p>
@endsection

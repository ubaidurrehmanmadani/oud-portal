@php
    $user = auth()->user();
    $nameParts = collect(explode(' ', trim($user->name)))->filter();
    $initials = $nameParts
        ->take(2)
        ->map(fn (string $part): string => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<details class="profile-menu">
    <summary aria-label="{{ __('portal.open_profile_menu') }}">
        <span class="profile-avatar">{{ $initials ?: 'OU' }}</span>
        <span class="profile-copy">
            <strong>{{ $user->name }}</strong>
            <span>{{ $user->role->label() }}</span>
        </span>
        <span class="profile-caret" aria-hidden="true"></span>
    </summary>

    <div class="profile-dropdown">
        <div class="profile-dropdown-header">
            <span class="profile-avatar">{{ $initials ?: 'OU' }}</span>
            <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->email }}</span>
            </div>
        </div>

        <a href="#">{{ __('portal.profile_settings') }}</a>
        <a href="#">{{ __('portal.account_security') }}</a>
        <a href="#">{{ __('portal.notification_preferences') }}</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">{{ __('portal.logout') }}</button>
        </form>
    </div>
</details>

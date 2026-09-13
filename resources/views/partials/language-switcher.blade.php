@php($currentLocale = app()->getLocale())

<div class="language-switcher" role="group" aria-label="{{ __('portal.language_switcher') }}">
    @foreach (['en' => 'English', 'ar' => 'العربية'] as $locale => $label)
        <form method="POST" action="{{ route('language.update', $locale) }}">
            @csrf
            <button
                type="submit"
                lang="{{ $locale }}"
                class="language-button {{ $currentLocale === $locale ? 'is-active' : '' }}"
                aria-pressed="{{ $currentLocale === $locale ? 'true' : 'false' }}"
            >
                {{ $label }}
            </button>
        </form>
    @endforeach
</div>

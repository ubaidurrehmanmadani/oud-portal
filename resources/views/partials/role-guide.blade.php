<button type="button" class="oud-user-role" data-role-open aria-haspopup="dialog" aria-controls="oud-role-dialog">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg><span>{{ __('role-guide.title') }}</span>
</button>
<dialog id="oud-role-dialog" aria-labelledby="oud-role-title">
    <header class="oud-role-header"><h2 id="oud-role-title">{{ auth()->user()->role->label() }}</h2><button type="button" class="oud-role-close" data-role-close aria-label="{{ __('role-guide.close') }}">×</button></header>
    @foreach (['can', 'cannot'] as $permission)
        <section><h3>{{ __('role-guide.'.$permission) }}</h3><ul>@foreach (__('role-guide.'.auth()->user()->role->value.'.'.$permission) as $description)<li>{{ $description }}</li>@endforeach</ul></section>
    @endforeach
    <p>{{ __('role-guide.note') }}</p>
</dialog>

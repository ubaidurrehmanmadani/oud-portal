<section class="demo-access" aria-labelledby="demo-access-title" data-demo-access data-copy-success="{{ __('portal.demo_copied') }}" data-copy-failure="{{ __('portal.demo_copy_failed') }}">
    <h3 id="demo-access-title">{{ __('portal.demo_access') }}</h3>
    <p class="demo-access-hint">{{ __('portal.demo_hint') }}</p>
    <ul>
        @foreach ($demoAccounts as $account)
            <li><span class="demo-account-state" aria-label="{{ $account['ready'] ? __('portal.demo_ready') : __('portal.demo_unavailable') }}">{{ $account['ready'] ? '✓' : '×' }}</span><div><span class="demo-account-role">{{ $account['label'] }}</span><button type="button" data-copy-value="{{ $account['email'] }}" aria-label="{{ __('portal.demo_copy', ['value' => $account['email']]) }}"><span dir="ltr">{{ $account['email'] }}</span><span aria-hidden="true">⧉</span></button></div></li>
        @endforeach
    </ul>
    <div class="demo-access-password"><span>{{ __('portal.password') }}</span><button type="button" data-copy-value="{{ config('demo-access.password') }}" aria-label="{{ __('portal.demo_copy_password') }}"><span dir="ltr">{{ config('demo-access.password') }}</span><span aria-hidden="true">⧉</span></button></div>
    <span class="demo-copy-toast" role="status" aria-live="polite" aria-atomic="true"></span>
</section>

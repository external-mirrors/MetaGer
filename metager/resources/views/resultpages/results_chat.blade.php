@php
    $chatKeyUser = \Auth::guard('key')->user();
    $chatLoggedIn = $chatKeyUser !== null || app(\App\Models\Authorization\Authorization::class)->loggedIn;
@endphp

{{--
    Layout (fill the viewport exactly, no page-level scroll) lives in
    resources/less/metager/pages/resultpage/chat.less, scoped to body.chat — no inline
    <style>/<script>, this app's CSP doesn't allow 'unsafe-inline' and nothing here should rely on
    it anyway. Baseline layout only, not the full seamlessness treatment described in
    docs/llm/metager-integration/foki-integration.md's checklist — real auto-resize-to-*content*
    (as opposed to just viewport-fill) needs postMessage cooperation from the chat app's own
    frontend, which doesn't exist yet.
--}}

<div id="chat-container">
    @if(!$chatLoggedIn)
        {{-- Same "get a key" prompt as the startpage (index.blade.php #searchbar-replacement) --}}
        <div id="searchbar-replacement" class="chat-auth-gate">
            <div class="tagline">@lang('index.searchbar-replacement.tagline')</div>
            <div class="hook-line">@lang('index.searchbar-replacement.hook')</div>
            <div class="login-status">
                <img src="/img/svg-icons/key-empty.svg" alt="" aria-hidden="true">
                @lang('index.searchbar-replacement.not_logged_in')
            </div>
            <div class="helper-line">@lang('index.searchbar-replacement.message')</div>
            <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys') }}" class="btn btn-primary startpage-create-btn">
                @lang('index.searchbar-replacement.start')
            </a>
            <div class="divider-row"><span class="line"></span><span class="divider-label">@lang('index.searchbar-replacement.or')</span><span class="line"></span></div>
            <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter?redirect_success=' . urlencode(request()->fullUrl())) }}" class="btn btn-default startpage-login-btn">
                @lang('index.searchbar-replacement.have_key')
            </a>
        </div>
    @else
        @if($chatKeyUser !== null && in_array($chatKeyUser->getKeyState(), [\App\Authentication\KeyState::EMPTY, \App\Authentication\KeyState::LOW], true))
            {{-- Same wording as the header's key-balance tooltip (parts/searchbar.blade.php) --}}
            <div id="chat-balance-banner" class="chat-balance-banner {{ $chatKeyUser->getKeyState()->value }}">
                <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter') }}">
                    {{ $chatKeyUser->getKeyState() === \App\Authentication\KeyState::EMPTY ? __('index.key.tooltip.empty') : __('index.key.tooltip.low') }}
                </a>
            </div>
        @endif
        <iframe
            id="chat-iframe"
            title="@lang('index.foki.chat')"
            src="{{ LaravelLocalization::getLocalizedURL(null, '/chat') }}?eingabe={{ rawurlencode($eingabe) }}&locale={{ LaravelLocalization::getCurrentLocale() }}"
        ></iframe>
    @endif
</div>

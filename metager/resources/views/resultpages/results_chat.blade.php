@php
    $chatKeyUser = \Auth::guard('key')->user();
    $chatLoggedIn = $chatKeyUser !== null || app(\App\Models\Authorization\Authorization::class)->loggedIn;

    $chatBackend = app(\App\Services\ChatBackend::class);
    $chatAvailable = $chatBackend->isAvailable();

    // Transcript is passed in by ChatController on the no-JS POST path; a plain GET starts empty.
    $transcript = $chatTranscript ?? [];
    $models = $chatAvailable ? $chatBackend->models() : [];

    $selectedModelId = $chatModelId
        ?? (collect($models)->firstWhere('default', true)['id'] ?? ($models[0]['id'] ?? null));
    $selectedModelName = collect($models)->firstWhere('id', $selectedModelId)['display_name'] ?? '';
@endphp

{{--
    The chat interface, rendered by MetaGer itself — no iframe. Layout lives in
    resources/less/metager/pages/resultpage/chat.less, scoped to body.chat; no inline <style>/<script>,
    since this app's CSP doesn't allow 'unsafe-inline'.

    The viewport-fill model (single internal scrollbar, pinned composer) is unchanged from the iframe
    era — it just applies to real DOM now, which is also why the mobile virtual-keyboard and
    viewport-height quirks that came with iframing are simply gone.

    NOTE: Blade comments are `{{--` … `--}}`. An HTML formatter run over this file will split those
    markers apart, which silently turns the rest of the file into one long comment — the page then
    dies with "unexpected token endif". Exclude .blade.php from format-on-save.
--}}

<div id="chat-container">
    @if(!$chatAvailable)
        {{--
            The whole point of the health gate: never render a chat UI that cannot work. The focus
            is normally hidden from both Foki switchers when the backend is down
            (Searchengines::parse_available_foki()), so reaching this branch means someone
            navigated directly by URL.
        --}}
        <div class="chat-notice chat-notice--unavailable">
            <h1>@lang('chat.unavailable.title')</h1>
            <p>@lang('chat.unavailable.body')</p>
        </div>
    @elseif(!$chatLoggedIn)
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

        <div id="chat-messages" class="chat-messages">
            @if(count($transcript) === 0)
                <div class="chat-empty">
                    <img class="chat-empty-icon" src="/img/svg-icons/lock.svg" alt="" aria-hidden="true">
                    <div class="chat-empty-text">
                        <p>@lang('chat.empty')</p>
                        <p class="chat-empty-note">@lang('chat.empty_note')</p>
                    </div>
                </div>
            @endif

            @foreach($transcript as $message)
                @include('resultpages.parts.chat.message', ['message' => $message])
            @endforeach

            @isset($chatError)
                <div class="chat-error" role="alert">{{ $chatError }}</div>
            @endisset
        </div>

        @include('resultpages.parts.chat.composer', [
            'transcript' => $transcript,
            'models' => $models,
            'selectedModelId' => $selectedModelId,
            'selectedModelName' => $selectedModelName,
        ])

        {{--
            Strings the enhancement bundle needs, carried in data attributes.

            Not a JS-side dictionary (resources/js/translations.js style) and not an inline
            <script>: this keeps lang/{de,en}/chat.php the single translation source for the whole
            feature, and needs no CSP exception.
        --}}
        <div id="chat-strings" hidden
             data-copy="@lang('chat.action.copy')"
             data-copy-done="@lang('chat.action.copy_done')"
             data-regenerate="@lang('chat.action.regenerate')"
             data-download="@lang('chat.action.download')"
             data-attachment-choose="@lang('chat.attachment.choose')"
             data-attachment-remove="@lang('chat.attachment.remove')"
             data-error-generic="@lang('chat.error.generic')"></div>

        {{--
            Loaded only where it can do something, and only ever as an enhancement: everything above
            already works without it. `defer` so it runs after the markup it upgrades exists.
        --}}
        <script src="{{ mix('js/chat.js') }}" defer></script>
    @endif
</div>

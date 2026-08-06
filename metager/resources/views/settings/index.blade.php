@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div id="settings">
        <div class="masthead">
            <a class="backlink" href="{{ $url }}"><span class="arrow" aria-hidden="true">&larr;</span> @lang('settings.back')</a>
            <h1 class="page-title">@lang('settings.header.1')</h1>
            <p class="lede">@lang('settings.text.1', ['fokusName' => $foki[$fokus]['name']])</p>
        </div>

        {{-- ============ PRIMARY: the focus being configured ============ --}}
        <section>
            <h2 class="section-label">@lang('settings.section.focus')</h2>

            <div class="focus-config">
                <div class="focus-tabs">
                    @foreach ($foki as $fokusKey => $data)
                        <a href="{{ route('settings', ['focus' => $fokusKey, 'url' => $url]) }}"
                            @if ($fokusKey === $fokus) class="active" aria-current="page" @endif>
                            {{ $data['name'] }}
                            @if ($data['hasCustomSettings'])
                                <span class="tab-dot" aria-hidden="true"></span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="pane">
                    @include('settings.partials.fokus-section', ['fokus' => $fokus, 'data' => $foki[$fokus], 'url' => $url])
                </div>
            </div>
        </section>

        {{-- ============ Account / MetaGer key ============ --}}
        <section>
            <h2 class="section-label">@lang('settings.section.account')</h2>
            <div class="rows" id="metager-key">
                <div class="row row-stacked">
                    <div class="row-text">
                        <h3 class="block-title">@lang('settings.metager-key.header')</h3>
                        @if (!empty($authorization->key))
                            <p class="credit">@lang('settings.metager-key.charge', ['token' => max($authorization->availableTokens, 0)])</p>
                        @else
                            <p class="help">@lang('settings.metager-key.no-key')</p>
                        @endif
                    </div>
                </div>

                @if (!empty($authorization->key))
                    <div class="row row-stacked">
                        <div class="row-text">
                            <label class="field-label" for="key">@lang('settings.key')</label>
                            <div class="copyLink">
                                <input type="text" name="key" id="key" readonly value="{{ $authorization->key }}" size="30">
                                <button class="btn btn-sm">@lang('settings.copy')</button>
                            </div>
                        </div>
                    </div>
                    <div class="row row-stacked">
                        <div class="actions-inline">
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter') }}" class="btn btn-sm">@lang('settings.metager-key.manage')</a>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/remove?url=' . urlencode(url()->full())) }}" class="btn btn-sm" id="remove-key">@lang('settings.metager-key.logout')</a>
                        </div>
                    </div>
                @else
                    <div class="row row-stacked">
                        <div class="actions-inline">
                            <a class="btn btn-sm" href="{{ LaravelLocalization::getLocalizedURL(null, '/keys') }}">@lang('settings.metager-key.actions.info')</a>
                            <a class="btn btn-sm" href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter') }}">@lang('settings.metager-key.actions.login')</a>
                            <a class="btn btn-sm" href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/create') }}">@lang('settings.metager-key.actions.create')</a>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- ============ Preferences (suggestions + more settings) ============ --}}
        <section>
            <h2 class="section-label">@lang('settings.section.preferences')</h2>
            <p class="help">@lang('settings.hint.hint')</p>

            <div class="rows">
                <form action="{{ route('enableSetting') }}" method="post" class="form setting-form" id="suggest-settings">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">

                    <div class="row-head">
                        <h3 class="block-title">@lang('settings.suggestions.heading') <a href="{{ route("help-mainpages") . "#suggest" }}" target="_blank"><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"></a></h3>
                    </div>

                    <div class="row">
                        <div class="row-text"><label class="field-label" for="suggestion_provider">@lang('settings.suggestions.provider.label')</label></div>
                        <div class="row-control">
                            <select name="suggestion_provider" id="suggestion_provider">
                                @foreach ($globalSettings['suggestion_provider']['values'] as $option)
                                    <option value="{{ $option['value'] }}"
                                        {{ (app(App\SearchSettings::class)->suggestion_provider ?? 'off') === $option['value'] ? 'disabled selected' : '' }}>
                                        {{ $option['translate'] ? __($option['label']) : $option['label'] }}{{ $option['cost'] === null ? '' : ' (' . __('settings.suggestions.provider.cost', ['cost' => $option['cost']]) . ')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if(!in_array(app(App\SearchSettings::class)->suggestion_provider, [null, "off"]))
                        <div class="row">
                            <div class="row-text">
                                <label class="field-label" for="suggestion_delay">@lang('settings.suggestions.delay.label')</label>
                                <p class="help">@lang('settings.suggestions.delay.description')</p>
                            </div>
                            <div class="row-control">
                                <select name="suggestion_delay" id="suggestion_delay">
                                    @foreach ($globalSettings['suggestion_delay']['values'] as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ app(App\SearchSettings::class)->suggestion_delay === constant('App\SearchSettings::SUGGESTION_DELAY_' . strtoupper($option['value'])) ? 'disabled selected' : '' }}>
                                            @lang($option['label'])
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="row-text">
                                <label class="field-label" for="suggestion_addressbar">@lang('settings.suggestions.addressbar.label')</label>
                                <p class="help">@lang('settings.suggestions.addressbar.description')</p>
                                <p class="help">@lang('settings.suggestions.addressbar.hint')</p>
                            </div>
                            <div class="row-control">
                                <select name="suggestion_addressbar" id="suggestion_addressbar">
                                    @foreach ($globalSettings['suggestion_addressbar']['values'] as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ (app(App\SearchSettings::class)->suggestion_addressbar ? 'on' : 'off') === $option['value'] ? 'disabled selected' : '' }}>
                                            @lang($option['label'])
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </form>

                <form action="{{ route('enableSetting') }}" method="post" class="form setting-form" id="more-settings">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">

                    <div class="row-head">
                        <h3 class="block-title">@lang('settings.more')</h3>
                    </div>

                    @foreach (['tips', 'tiles_startpage', 'dark_mode', 'new_tab', 'zitate'] as $key)
                        @continue(!$globalSettings->has($key))
                        <div class="row">
                            <div class="row-text"><label class="field-label" for="{{ $key }}">@lang($globalSettings[$key]['label'])</label></div>
                            <div class="row-control">
                                <select name="{{ $key }}" id="{{ $key }}">
                                    @foreach ($globalSettings[$key]['values'] as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ $currentGlobalValues[$key] === $option['value'] ? 'disabled selected' : '' }}>
                                            {{ $option['translate'] ? __($option['label']) : $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach

                    <div class="form-actions"><button type="submit" class="btn btn-sm no-js">@lang('settings.save')</button></div>
                </form>
            </div>
        </section>

        {{-- ============ Backup and reset ============ --}}
        <section class="utility">
            <h2 class="section-label">@lang('settings.section.backup')</h2>

            <div class="rows">
                <div class="row row-stacked">
                    <div class="row-text">
                        <h3 class="block-title">@lang('settings.hint.header')</h3>
                        <p class="help">@lang('settings.hint.loadSettings')</p>
                        @if(empty($cookieLink))
                            <code>@lang('settings.hint.no-settings')</code>
                        @else
                            <div class="copyLink">
                                <input id="loadSettings" class="loadSettings" type="text" readonly value="{{ $cookieLink }}">
                                <button class="js-only btn btn-sm">@lang('settings.copy')</button>
                            </div>
                        @endif
                        @if($agent["browser_gecko_version"] > 0)
                            <p class="help">@lang('settings.hint.addon', ["link" => "https://addons.mozilla.org/firefox/addon/metager-suche/"])</p>
                        @elseif($agent["browser_name"] === "Edge")
                            <p class="help">@lang('settings.hint.addon', ["link" => "https://microsoftedge.microsoft.com/addons/detail/fdckbcmhkcoohciclcedgjmchbdeijog"])</p>
                        @elseif($agent["browser_chromium_version"] > 0 && $agent["device_type"] === "desktop" )
                            <p class="help">@lang('settings.hint.addon', ["link" => "https://chromewebstore.google.com/detail/metager-suche/gjfllojpkdnjaiaokblkmjlebiagbphd"])</p>
                        @endif
                    </div>
                </div>
            </div>

            @if ($settingActive)
                <div class="danger-row">
                    <p class="help">@lang('settings.resetDescription')</p>
                    <form action="{{ route('deleteSettings', ['fokus' => $fokus, 'url' => $url]) }}" method="post">
                        <input type="hidden" name="url" value="{{ $url }}">
                        <input type="hidden" name="focus" value="{{ $fokus }}">
                        <button type="submit" class="btn btn-sm btn-danger">@lang('settings.reset')</button>
                    </form>
                </div>
            @endif
        </section>

        <div id="plugin-btn" class="hidden"></div>
    </div>
@endsection

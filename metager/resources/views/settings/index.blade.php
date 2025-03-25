@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div id="settings">
        <h1 class="page-title">@lang('settings.header.1') ({{ $fokusName }})</h1>
        <div class="card">
            <p>@lang('settings.text.1', ['fokusName' => $fokusName])</p>
        </div>
        <div class="card" id="metager-key">
            <h1>@lang('settings.metager-key.header')</h1>
            @if (!empty($authorization->key))
                <h2 class="charge">
                    @lang('settings.metager-key.charge', ['token' => max($authorization->availableTokens, 0)])
                </h2>
                <div class="copyLink">
                    <input type="text" name="key" id="key" readonly value="{{ $authorization->key }}"
                        size="30">
                    <button class="btn btn-default">@lang('settings.copy')</button>
                </div>

                <div class="actions">
                    <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter') }}"
                        class="btn btn-default">@lang('settings.metager-key.manage')</a>
                    <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/remove?url=' . urlencode(url()->full())) }}"
                        class="btn btn-default" id="remove-key">@lang('settings.metager-key.logout')</a>
                </div>
            @else
                <p>@lang('settings.metager-key.no-key')</p>
                <div class="no-key-actions">
                    <a class="btn btn-default"
                        href="{{ LaravelLocalization::getLocalizedURL(null, '/keys') }}">@lang('settings.metager-key.actions.info')</a>
                    <a class="btn btn-default"
                        href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter') }}">@lang('settings.metager-key.actions.login')</a>
                    <a class="btn btn-default"
                        href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/create') }}">@lang('settings.metager-key.actions.create')</a>
                </div>
            @endif
        </div>
        @if ($fokus !== 'bilder' || app(App\SearchSettings::class)->external_image_search === 'metager')
            <div class="card" id="engines">
                <h1>@lang('settings.header.2')</h1>
                <p>@lang('settings.text.2')</p>
                <div class="sumas enabled-engines">
                    @foreach ($sumas as $name => $suma)
                        @if ($suma->configuration->disabled === false)
                            <div class="suma">
                                <form action="{{ route('disableEngine') }}" method="post" title="@lang('settings.disable-engine')">
                                    <input type="hidden" name="suma" value="{{ $name }}">
                                    <input type="hidden" name="focus" value="{{ $fokus }}">
                                    <input type="hidden" name="url" value="{{ $url }}">
                                    <button type="submit"
                                        aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.1')">{{ $suma->getDisplayName(true) }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                    <div class="no-engines">@lang('settings.no-engines')</div>
                </div>
                @if (in_array(\App\Models\DisabledReason::USER_CONFIGURATION, $disabledReasons) || in_array(\App\Models\DisabledReason::SUMAS_DEFAULT_CONFIGURATION, $disabledReasons))
                    <div class="sumas disabled-engines">
                        @foreach ($sumas as $name => $suma)
                            @if (
                                $suma->configuration->disabled &&
                                    (in_array(\App\Models\DisabledReason::USER_CONFIGURATION, $suma->configuration->disabledReasons) || in_array(\App\Models\DisabledReason::SUMAS_DEFAULT_CONFIGURATION, $suma->configuration->disabledReasons)) &&
                                    sizeof($suma->configuration->disabledReasons) === 1)
                                <div class="suma disabled-engine">
                                    <form action="{{ route('enableEngine') }}" method="post" title="@lang('settings.enable-engine')">
                                        <input type="hidden" name="suma" value="{{ $name }}">
                                        <input type="hidden" name="focus" value="{{ $fokus }}">
                                        <input type="hidden" name="url" value="{{ $url }}">
                                        <button type="submit"
                                            aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->getDisplayName(true) }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if (in_array(\App\Models\DisabledReason::INCOMPATIBLE_FILTER, $disabledReasons))
                    <h4>@lang('settings.disabledByFilter')</h4>
                    <div class="sumas filtered-engines">
                        @foreach ($sumas as $name => $suma)
                            @if (
                                $suma->configuration->disabled &&
                                    in_array(\App\Models\DisabledReason::INCOMPATIBLE_FILTER, $suma->configuration->disabledReasons))
                                <div class="suma disabled-engine not-available">
                                    <form action="" title="@lang('settings.filtered-engine')">
                                        <input type="hidden" name="suma" value="{{ $name }}">
                                        <input type="hidden" name="focus" value="{{ $fokus }}">
                                        <input type="hidden" name="url" value="{{ $url }}">
                                        <button type="submit"
                                            aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->getDisplayName(true) }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if (in_array(\App\Models\DisabledReason::PAYMENT_REQUIRED, $disabledReasons))
                    <h4>@lang('settings.disabledBecausePaymentRequired', ['link' => app(\App\Models\Authorization\Authorization::class)->getAdfreeLink()])</h4>
                    <div class="sumas payment-required-engines">
                        @foreach ($sumas as $name => $suma)
                            @if (
                                $suma->configuration->disabled &&
                                    in_array(\App\Models\DisabledReason::PAYMENT_REQUIRED, $suma->configuration->disabledReasons))
                                <div class="suma disabled-engine not-available">
                                    <form action="#engines" title="@lang('settings.payment-engine')">
                                        <input type="hidden" name="suma" value="{{ $name }}">
                                        <input type="hidden" name="focus" value="{{ $fokus }}">
                                        <input type="hidden" name="url" value="{{ $url }}">
                                        <button type="submit"
                                            aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->getDisplayName(true) }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if ($searchCost > 0)
                    <p>@lang('settings.cost', ['cost' => $searchCost])</p>
                @else
                    <p>@lang('settings.cost-free')</p>
                @endif
                @if(array_key_exists("yahoo", $sumas) && $sumas["yahoo"]->configuration->disabled === false)
                    <p>@lang('settings.hint.yahoo')</p>
                @endif
            </div>
        @endif
        @if ($fokus !== 'bilder' || app(App\SearchSettings::class)->external_image_search === 'metager')
            <div class="card" id="filter">
                <h1>@lang('settings.header.3')</h1>
                <p>@lang('settings.text.3')</p>
                <form id="filter-form" action="{{ route('enableFilter') }}" method="post" class="form">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <div id="filter-options">
                        @foreach ($filter as $name => $filterInfo)
                            @if (empty($filterInfo->hidden) || $filterInfo->hidden === false)
                                <div class="form-group">
                                    <label for="{{ $filterInfo->{"get-parameter"} }}">@lang($filterInfo->name)</label>
                                    <select name="{{ $filterInfo->{"get-parameter"} }}"
                                        id="{{ $filterInfo->{"get-parameter"} }}" class="form-control">
                                        @foreach ($filterInfo->values as $key => $value)
                                            @if (!empty($key))
                                                <option
                                                    value="@if ($key !== 'nofilter') {{ $key }} @endif"
                                                    @if (
                                                        (!empty($filterInfo->value) && $filterInfo->value === $key) ||
                                                            (empty($filterInfo->value) && $filterInfo->{"default-value"} === $key)) selected @endif
                                                    @if (array_key_exists($key, $filterInfo->{"disabled-values"}) && sizeof($filterInfo->{"disabled-values"}[$key]) > 0) disabled @endif>@lang($value)
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-default no-js">@lang('settings.save')</button>
                </form>
            </div>

            <div class="card" id="blacklist-container">
                <h1 id="bl">@lang('settings.header.4')</h1>
                <p>@lang('settings.text.4')</p>
                <form id="newentry" action="{{ route('newBlacklist', ['fokus' => $fokus, 'url' => $url]) }}"
                    method="post">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <label for="blacklist">@lang('settings.address') ({{ sizeof($blacklist) }}) </label>
                    <div id="create">
                        <textarea name="blacklist" id="blacklist" cols="30" rows="{{ max(min(sizeof($blacklist) + 1, 20), 4) }}"
                            maxlength="2048" placeholder="example.com&#10;example2.com&#10;*.example3.com" spellcheck="false">{{ implode("\r\n", $blacklist) }}</textarea>
                        <button type="submit" class="btn btn-default">@lang('settings.save')</button>
                    </div>
                </form>
            </div>
        @endif
        @if ($fokus === 'bilder')
            <div id="external-search-service" class="card">
                <h1>@lang('settings.externalservice.heading')</h1>
                <div>@lang('settings.externalservice.description')</div>
                <form action="{{ route('enableExternalProvider') }}" method="POST">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <select name="bilder_setting_external" id="bilder_setting_external" class="form-control">
                        <option value="metager" @if (app(App\SearchSettings::class)->external_image_search === 'metager') selected @endif>MetaGer</option>
                        <option value="google" @if (app(App\SearchSettings::class)->external_image_search === 'google') selected @endif>Google</option>
                        <option value="bing" @if (app(App\SearchSettings::class)->external_image_search === 'bing') selected @endif>Bing</option>
                    </select>
                    <button type="submit" class="btn btn-default no-js">@lang('settings.save')</button>
                </form>
            </div>
        @endif
        <div class="card" id="suggest-settings">
            <h1>@lang('settings.suggestions.heading')</h1>
            <p>@lang('settings.hint.hint')</p>
            <form id="setting-form" action="{{ route('enableSetting') }}" method="post" class="form">
                <input type="hidden" name="focus" value="{{ $fokus }}">
                <input type="hidden" name="url" value="{{ $url }}">
                <div class="form-group">
                    <label for="sg">@lang('settings.suggestions.provider.label')</label>
                    <select name="sg" id="sg" class="form-control">
                        <option value="off" {{ in_array(app(App\SearchSettings::class)->suggestion_provider, [null, "off"]) ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.off')</option>
                        @foreach(App\Suggestions::GET_AVAILABLE_PROVIDERS() as $name => $class)
                        <option value="{{ $name }}" {{ app(App\SearchSettings::class)->suggestion_provider === $name ? 'disabled selected' : '' }}>
                           {{ \Str::ucfirst($name) . " (" . $class::COST . " Token)"}}</option>
                        @endforeach
                    </select>
                </div>
                @if(!in_array(app(App\SearchSettings::class)->suggestion_provider, [null, "off"]))
                <div class="form-group">
                    <label for="sgd">@lang('settings.suggestions.delay.label')</label>
                    <div class="text-left">@lang('settings.suggestions.delay.description')</div>
                    <select name="sgd" id="sgd" class="form-control" {{ in_array(app(App\SearchSettings::class)->suggestion_provider, [null, "off"]) ? 'disabled' : '' }}>
                        <option value="short" {{ app(App\SearchSettings::class)->suggestion_delay === \App\SearchSettings::SUGGESTION_DELAY_SHORT ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.delay.short')</option>
                        <option value="medium" {{ app(App\SearchSettings::class)->suggestion_delay === \App\SearchSettings::SUGGESTION_DELAY_MEDIUM  ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.delay.medium')</option>
                        <option value="long" {{ app(App\SearchSettings::class)->suggestion_delay === \App\SearchSettings::SUGGESTION_DELAY_LONG  ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.delay.long')</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sga">@lang('settings.suggestions.addressbar.label')</label>
                    <div class="text-left">@lang('settings.suggestions.addressbar.description')</div>
                    <div class="text-left">@lang('settings.suggestions.addressbar.hint')</div>
                    <select name="sga" id="sga" class="form-control" {{ in_array(app(App\SearchSettings::class)->suggestion_provider, [null, "off"]) ? 'disabled' : '' }}>
                        <option value="off"
                            {{ app(App\SearchSettings::class)->suggestion_addressbar === false ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.off')</option>
                        <option value="on" {{ app(App\SearchSettings::class)->suggestion_addressbar === true ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.on')</option>
                    </select>
                </div>
                @endif
            </form>
        </div>
        <div class="card" id="more-settings">
            <h1>@lang('settings.more')</h1>
            <p>@lang('settings.hint.hint')</p>
            <form id="setting-form" action="{{ route('enableSetting') }}" method="post" class="form">
                <input type="hidden" name="focus" value="{{ $fokus }}">
                <input type="hidden" name="url" value="{{ $url }}">
                <div class="form-group">
                    <label for="self_advertisements">@lang('settings.self_advertisements.label')</label>
                    <select name="self_advertisements" id="self_advertisements" class="form-control">
                        <option value="off"
                            {{ app(App\SearchSettings::class)->self_advertisements === false ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.off')</option>
                        <option value="on" {{ app(App\SearchSettings::class)->self_advertisements === true ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.on')</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tiles_startpage">@lang('settings.tiles_startpage.label')</label>
                    <select name="tiles_startpage" id="tiles_startpage" class="form-control">
                        <option value="off"
                            {{ app(App\SearchSettings::class)->tiles_startpage === false ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.off')</option>
                        <option value="on" {{ app(App\SearchSettings::class)->tiles_startpage === true ? 'disabled selected' : '' }}>
                            @lang('settings.suggestions.on')</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dm">@lang('settings.darkmode')</label>
                    <select name="dm" id="dm" class="form-control">
                        <option value="system" {{ app(App\SearchSettings::class)->theme === "system" ? 'disabled selected' : '' }}>
                            @lang('settings.system')</option>
                        <option value="off" {{ app(App\SearchSettings::class)->theme === "light" ? 'disabled selected' : '' }}>
                            @lang('settings.light')</option>
                        <option value="on" {{ app(App\SearchSettings::class)->theme === "dark" ? 'disabled selected' : '' }}>
                            @lang('settings.dark')</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nt">@lang('settings.newTab')</label>
                    <select name="nt" id="nt" class="form-control">
                        <option value="off" {{ app(App\SearchSettings::class)->newtab === false ? 'disabled selected' : '' }}>@lang('settings.off')
                        </option>
                        <option value="on" {{ app(App\SearchSettings::class)->newtab === true ? 'disabled selected' : '' }}>
                            @lang('settings.on')</option>
                    </select>
                </div>
                @if (App\Localization::getLanguage() === 'de')
                    <div class="form-group">
                        <label for="zitate">Zitate</label>
                        <select name="zitate" id="zitate" class="form-control">
                            <option value="on" @if (app(App\SearchSettings::class)->zitate === true) disabled selected @endif>Anzeigen
                            </option>
                            <option value="off" {{ app(App\SearchSettings::class)->zitate === false ? 'disabled selected' : '' }}>Nicht
                                Anzeigen</option>
                        </select>
                    </div>
                @endif
                <button type="submit" class="btn btn-default no-js">@lang('settings.save')</button>
            </form>
        </div>
        <div class="card" id="actions">
            @if ($settingActive)
                <div id="reset">
                    <form action="{{ route('deleteSettings', ['fokus' => $fokus, 'url' => $url]) }}" method="post">
                        <input type="hidden" name="url" value="{{ $url }}">
                        <input type="hidden" name="focus" value="{{ $fokus }}">
                        <button type="submit" class="btn btn-sm btn-danger">@lang('settings.reset')</button>
                    </form>
                </div>
            @endif
            <div id="back">
                <a href="{{ $url }}" class="btn btn-sm btn-default">@lang('settings.back')</a>
            </div>
        </div>
        <div class="card">
            <h1>@lang('settings.hint.header')</h1>
            @if($agent["browser_gecko_version"] > 0)
            <p>@lang('settings.hint.addon', ["link" => "https://addons.mozilla.org/firefox/addon/metager-suche/"])</p>
            @elseif($agent["browser_name"] === "Edge")
            <p>@lang('settings.hint.addon', ["link" => "https://microsoftedge.microsoft.com/addons/detail/fdckbcmhkcoohciclcedgjmchbdeijog"])</p>
            @elseif($agent["browser_chromium_version"] > 0 && $agent["device_type"] === "desktop" )
            <p>@lang('settings.hint.addon', ["link" => "https://chromewebstore.google.com/detail/metager-suche/gjfllojpkdnjaiaokblkmjlebiagbphd"])</p>
            @endif
            <p>@lang('settings.hint.loadSettings')</p>
            @if(empty($cookieLink))
            <code>@lang('settings.hint.no-settings')</code>
            @else
            <div class="copyLink">
                <input id="loadSettings" class="loadSettings" type="text" value="{{ $cookieLink }}">
                <button class="js-only btn btn-default">@lang('settings.copy')</button>
            </div>
            @endif
        </div>
        <div id="plugin-btn" class="hidden"></div>
    </div>
@endsection

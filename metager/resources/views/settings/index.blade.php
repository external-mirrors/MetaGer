@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="settings">
    <h1 class="page-title">@lang('settings.header.1') ({{ $fokusName }})</h1>
    <div class="card">
        <p>@lang('settings.text.1', ["fokusName" => $fokusName])</p>
    </div>
    <div class="card" id="metager-key">
        <h1>@lang('settings.metager-key.header')</h1>
        @if(!empty($authorization->key))
        <h2 class="charge">
            @lang('settings.metager-key.charge', ["token" => $authorization->availableTokens])
        </h2>
        <div class="copyLink">
            <input type="text" name="key" id="key" readonly value="{{ $authorization->key }}" size="30">
            <button class="btn btn-default">@lang('settings.copy')</button>
        </div>
        
        <div class="actions">
            <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter')}}" class="btn btn-default">@lang("settings.metager-key.manage")</a>
            <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/remove?url=' . urlencode(url()->full()))}}" class="btn btn-default">@lang("settings.metager-key.logout")</a>
        </div>
        @else
        <p>@lang('settings.metager-key.no-key')</p>
        <div class="no-key-actions">
            <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(null, '/keys')}}">@lang('settings.metager-key.actions.info')</a>
            <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter')}}">@lang('settings.metager-key.actions.login')</a>
            <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/create')}}">@lang('settings.metager-key.actions.create')</a>
        </div>
        @endif
    </div>
    @if($fokus !== "bilder" || Cookie::get("bilder_setting_external", "metager") === "metager")
    <div class="card" id="engines">
        <h1>@lang('settings.header.2')</h1>
        <p>@lang('settings.text.2')</p>
        <div class="sumas enabled-engines">
            @foreach($sumas as $name => $suma)
            @if($suma->configuration->disabled === false)
            <div class="suma">
                <form action="{{ route('disableEngine') }}" method="post" title="@lang("settings.disable-engine")">
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.1')">{{ $suma->configuration->infos->displayName }} ({{ $suma->configuration->cost > 0 ? $suma->configuration->cost . " Token" : __('settings.free') }})</button>
                </form>
            </div>
            @endif
            @endforeach
            <div class="no-engines">@lang('settings.no-engines')</div>
        </div>
        @if(in_array(\App\Models\DisabledReason::USER_CONFIGURATION, $disabledReasons))
        <div class="sumas disabled-engines">
            @foreach($sumas as $name => $suma)
            @if( $suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::USER_CONFIGURATION)
            <div class="suma disabled-engine">
                <form action="{{ route('enableEngine') }}" method="post" title="@lang("settings.enable-engine")">
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->configuration->infos->displayName }} ({{ $suma->configuration->cost > 0 ? $suma->configuration->cost . " Token" : __('settings.free') }})</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
        @endif
        @if(in_array(\App\Models\DisabledReason::INCOMPATIBLE_FILTER, $disabledReasons))
        <h4>@lang('settings.disabledByFilter')</h4>
        <div class="sumas filtered-engines">
            @foreach($sumas as $name => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::INCOMPATIBLE_FILTER)
            <div class="suma disabled-engine not-available">
                <form action="" title="@lang("settings.filtered-engine")">
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->configuration->infos->displayName }} ({{ $suma->configuration->cost > 0 ? $suma->configuration->cost . " Token" : __('settings.free') }})</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
        @endif
        @if(in_array(\App\Models\DisabledReason::PAYMENT_REQUIRED, $disabledReasons))
        <h4>@lang('settings.disabledBecausePaymentRequired', ["link" => app(\App\Models\Authorization\Authorization::class)->getAdfreeLink()])</h4>
        <div class="sumas payment-required-engines">
            @foreach($sumas as $name => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::PAYMENT_REQUIRED)
            <div class="suma disabled-engine not-available">
                <form action="#engines" title="@lang("settings.payment-engine")">
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->configuration->infos->displayName }} ({{ $suma->configuration->cost > 0 ? $suma->configuration->cost . " Token" : __('settings.free') }})</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
        @endif
        @if($searchCost > 0)
        <p>@lang('settings.cost', ["cost" => $searchCost])</p>
        @else
        <p>@lang('settings.cost-free')</p>        
        @endif
    </div>
    @endif
    @if($fokus !== "bilder" || Cookie::get("bilder_setting_external", "metager") === "metager")
    <div class="card" id="filter">
        <h1>@lang('settings.header.3')</h1>
        <p>@lang('settings.text.3')</p>
        <form id="filter-form" action="{{ route('enableFilter') }}" method="post" class="form">
            <input type="hidden" name="focus" value="{{ $fokus }}">
            <input type="hidden" name="url" value="{{ $url }}">
            <div id="filter-options">
                @foreach($filter as $name => $filterInfo)
                @if(empty($filterInfo->hidden) || $filterInfo->hidden === false)
                <div class="form-group">
                    <label for="{{ $filterInfo->{"get-parameter"} }}">@lang($filterInfo->name)</label>
                    <select name="{{ $filterInfo->{"get-parameter"} }}" id="{{ $filterInfo->{"get-parameter"} }}" class="form-control">
                        @foreach($filterInfo->values as $key => $value)
                        @if(!empty($key))
                        <option 
                            value="@if($key !== "nofilter"){{ $key }}@endif" 
                            @if(!empty($filterInfo->value) && $filterInfo->value === $key ||
                                (empty($filterInfo->value) && $filterInfo->{"default-value"} === $key))
                                selected
                            @endif
                            @if(array_key_exists($key, $filterInfo->{"disabled-values"}) && sizeof($filterInfo->{"disabled-values"}[$key]) > 0)
                            disabled 
                            @endif
                        >@lang($value)</option>
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
        <form id="newentry" action="{{ route('newBlacklist', ["fokus" => $fokus, "url" => $url]) }}" method="post">
            <input type="hidden" name="url" value="{{ $url }}">
            <input type="hidden" name="focus" value="{{ $fokus }}">
            <label for="blacklist">@lang('settings.address') ({{ sizeof($blacklist) }}) </label>
            <div id="create">
                <textarea name="blacklist" id="blacklist" cols="30" rows="{{ max(min(sizeof($blacklist)+1, 20), 4) }}" maxlength="2048" placeholder="example.com&#10;example2.com&#10;*.example3.com" spellcheck="false">{{ implode("\r\n", $blacklist) }}</textarea>
                <button type="submit" class="btn btn-default">@lang('settings.save')</button>
            </div>
        </form>
    </div>
    @endif
    @if($fokus === "bilder")
    <div id="external-search-service" class="card">
        <h1>@lang('settings.externalservice.heading')</h1>
        <div>@lang('settings.externalservice.description')</div>
        <form action="{{ route('enableExternalProvider') }}" method="POST">
            <input type="hidden" name="focus" value="{{ $fokus }}">
            <input type="hidden" name="url" value="{{ $url }}">
            <select name="bilder_setting_external" id="bilder_setting_external" class="form-control">
                <option value="metager" @if(Cookie::get('bilder_setting_external', 'metager') === 'metager')selected @endif>MetaGer</option>
                <option value="google" @if(Cookie::get('bilder_setting_external', 'metager') === 'google')selected @endif>Google</option>
                <option value="bing" @if(Cookie::get('bilder_setting_external', 'metager') === 'bing')selected @endif>Bing</option>
            </select>
            <button type="submit" class="btn btn-default no-js">@lang('settings.save')</button>
        </form>
    </div>
    @endif
    <div class="card" id="more-settings">
        <h1>@lang('settings.more')</h1>
        <p>@lang('settings.hint.hint')</p>
        <form id="setting-form" action="{{ route('enableSetting') }}" method="post" class="form">
            <input type="hidden" name="focus" value="{{ $fokus }}">
            <input type="hidden" name="url" value="{{ $url }}">
            @if(config("metager.metager.admitad.suggestions_enabled"))
            <div class="form-group">
                <label for="sg">@lang('settings.suggestions.label')</label>
                <select name="sg" id="sg" class="form-control">
                    <option value="off" {{ Cookie::get('suggestions') === "off" ? "disabled selected" : "" }}>@lang('settings.suggestions.off')</option>
                    <option value="on" {{ !Cookie::has('suggestions') ? "disabled selected" : "" }}>@lang('settings.suggestions.on')</option>
                </select>
            </div>
            @endif
            <div class="form-group">
                <label for="self_advertisements">@lang('settings.self_advertisements.label')</label>
                <select name="self_advertisements" id="self_advertisements" class="form-control">
                    <option value="off" {{ Cookie::get('self_advertisements') === "off" ? "disabled selected" : "" }}>@lang('settings.suggestions.off')</option>
                    <option value="on" {{ !Cookie::has('self_advertisements') ? "disabled selected" : "" }}>@lang('settings.suggestions.on')</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dm">@lang('settings.darkmode')</label>
                <select name="dm" id="dm" class="form-control">
                    <option value="system" {{ !Cookie::has('dark_mode') ? "disabled selected" : "" }}>@lang('settings.system')</option>
                    <option value="off" {{ Cookie::get('dark_mode') === "1" ? "disabled selected" : "" }}>@lang('settings.light')</option>
                    <option value="on" {{ Cookie::get('dark_mode') === "2" ? "disabled selected" : "" }}>@lang('settings.dark')</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nt">@lang('settings.newTab')</label>
                <select name="nt" id="nt" class="form-control">
                    <option value="off" {{ !Cookie::has('new_tab') ? "disabled selected" : "" }}>@lang('settings.off')</option>
                    <option value="on" {{ Cookie::get('new_tab') === "on" ? "disabled selected" : "" }}>@lang('settings.on')</option>
                </select>
            </div>
            @if(App\Localization::getLanguage() === "de")
            <div class="form-group">
                <label for="zitate">Zitate</label>
                <select name="zitate" id="zitate" class="form-control">
                    <option value="on" @if(Cookie::get("zitate")===null)disabled selected @endif>Anzeigen</option>
                    <option value="off" {{ Cookie::get("zitate") === "off" ? "disabled selected" : "" }}>Nicht Anzeigen</option>
                </select>
            </div>
            @endif
            <button type="submit" class="btn btn-default no-js">@lang('settings.save')</button>
        </form>
    </div>
    <div class="card" id="actions">
        @if($settingActive)
        <div id="reset">
            <form action="{{ route('deleteSettings', ["fokus" => $fokus, "url" => $url]) }}" method="post">
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
        <p>@lang('settings.hint.loadSettings')</p>
        <div class="copyLink">
            <input id="loadSettings" class="loadSettings" type="text" value="{{$cookieLink}}">
            <button class="js-only btn btn-default">@lang('settings.copy')</button>
        </div>
    </div>
</div>
@endsection
@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="settings">
    <h1 class="page-title">@lang('settings.header.1') ({{ $fokusName }})</h1>
    <div class="card">
        <p>@lang('settings.text.1', ["fokusName" => $fokusName])</p>
    </div>
    <div class="card">
        <h1>@lang('settings.hint.header')</h1>
        <p>@lang('settings.hint.text', ["link" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('showAllSettings', ['url' => url()->full()])) ])</p>
        <p>@lang('settings.hint.loadSettings')</p>
        <div class="copyLink">
            <input id="loadSettings" class="loadSettings" type="text" value="{{$cookieLink}}">
            <button class="js-only btn btn-default" onclick="var copyText = document.getElementById('loadSettings');copyText.select();copyText.setSelectionRange(0, 99999);document.execCommand('copy');">@lang('settings.copy')</button>
        </div>
    </div>
    <div class="card">
        <h1>@lang('settings.header.2')</h1>
        <p>@lang('settings.text.2')</p>
        <p></p>
        <div class="sumas enabled-engines">
            @foreach($sumas as $name => $suma)
            @if($suma->configuration->disabled === false)
            <div class="suma">
                <form action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('disableEngine')) }}" method="post">
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="fokus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.1')">{{ $suma->configuration->infos->displayName }}</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
        <div class="sumas disabled-engines">
            @foreach($sumas as $name => $suma)
            @if( $suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::USER_CONFIGURATION)
            <div class="suma">
                <form action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('enableEngine')) }}" method="post">
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="fokus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->configuration->infos->displayName }}</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
        @if($filteredSumas)
        <h4>@lang('settings.disabledByFilter')</h4>
        <div class="sumas filtered-engines">
            @foreach($sumas as $name => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::INCOMPATIBLE_FILTER)
            <div class="suma">
                {{ $suma->configuration->infos->displayName }}
            </div>
            @endif
            @endforeach
        </div>
        @endif
        <h4>@lang('settings.disabledBecausePaymentRequired')</h4>
        <div class="sumas payment-required-engines">
            @foreach($sumas as $name => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::PAYMENT_REQUIRED)
            <div class="suma">
                {{ $suma->configuration->infos->displayName }}
            </div>
            @endif
            @endforeach
        </div>
    </div>
    <div class="card">
        <h1>@lang('settings.header.3')</h1>
        <p>@lang('settings.text.3')</p>
        <form id="filter-form" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('enableFilter')) }}" method="post" class="form">
            <input type="hidden" name="fokus" value="{{ $fokus }}">
            <input type="hidden" name="url" value="{{ $url }}">
            <div id="filter-options">
                @foreach($filter as $name => $filterInfo)
                @if(empty($filterInfo->hidden) || $filterInfo->hidden === false)
                <div class="form-group">
                    <label for="{{ $filterInfo->{"get-parameter"} }}">@lang($filterInfo->name)</label>
                    <select name="{{ $filterInfo->{"get-parameter"} }}" id="{{ $filterInfo->{"get-parameter"} }}" class="form-control">
                        <option value="" @if(Cookie::get($fokus . "_setting_" . $filterInfo->{"get-parameter"}) === null)disabled selected @endif>@if(property_exists($filterInfo->values, "nofilter"))@lang($filterInfo->values->nofilter)@else @lang('metaGer.filter.noFilter')@endif</option>
                        @foreach($filterInfo->values as $key => $value)
                        @if(!empty($key))
                        <option value="{{ $key }}" {{ Cookie::get($fokus . "_setting_" . $filterInfo->{"get-parameter"}) === $key ? "disabled selected" : "" }} @if(sizeof($filterInfo->{"disabled-values"}) > 0)disabled @endif>@lang($value)</option>
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
        <form id="newentry" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('newBlacklist', ["fokus" => $fokus, "url" => $url])) }}" method="post">
            <input type="hidden" name="url" value="{{ $url }}">
            <input type="hidden" name="fokus" value="{{ $fokus }}">
            <label for="blacklist">@lang('settings.address') ({{ sizeof($blacklist) }}) </label>
            <div id="create">
                <textarea name="blacklist" id="blacklist" cols="30" rows="{{ max(min(sizeof($blacklist)+1, 20), 4) }}" maxlength="2048" placeholder="example.com&#10;example2.com&#10;*.example3.com" spellcheck="false">{{ implode("\r\n", $blacklist) }}</textarea>
                <button type="submit" class="btn btn-default">@lang('settings.save')</button>
            </div>
        </form>
    </div>


    <div class="card">
        <h1>@lang('settings.more')</h1>
        <p>@lang('settings.hint.hint')</p>
        <form id="setting-form" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('enableSetting')) }}" method="post" class="form">
            <input type="hidden" name="fokus" value="{{ $fokus }}">
            <input type="hidden" name="url" value="{{ $url }}">
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
            <form action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('deleteSettings', ["fokus" => $fokus, "url" => $url])) }}" method="post">
                <input type="hidden" name="url" value="{{ $url }}">
                <input type="hidden" name="fokus" value="{{ $fokus }}">
                <button type="submit" class="btn btn-sm btn-danger">@lang('settings.reset')</button>
            </form>
        </div>
        @endif
        <div id="back">
            <a href="{{ $url }}" class="btn btn-sm btn-default">@lang('settings.back')</a>
        </div>
    </div>

    <script src="{{ mix('js/scriptSettings.js') }}"></script>
</div>
@endsection
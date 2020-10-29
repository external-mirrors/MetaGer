@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="settings">
    <div class="card-light">
        <h2>@lang('settings.header.1') ({{ $fokusName }})</h2>
        <p>@lang('settings.text.1', ["fokusName" => $fokusName])</p>
    </div>
    <div class="card-light">
        <h2>@lang('settings.hint.header')</h2>
        <p>@lang('settings.hint.text', ["link" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('showAllSettings', ['url' => url()->full()])) ])</p>
        <p>@lang('settings.hint.loadSettings')</p>
        <div id="cookieLink">
            <input id="loadSettings" type="text" value="{{$cookieLink}}">
            <button class="js-only btn btn-default" onclick="var copyText = document.getElementById('loadSettings');copyText.select();copyText.setSelectionRange(0, 99999);document.execCommand('copy');">@lang('settings.copy')</button>
        </div>
    </div>
    <div class="card-light">
        <h2>@lang('settings.header.2')</h2>
        <p>@lang('settings.text.2')</p>
        <p></p>
        <div class="sumas enabled-engines">
            @foreach($sumas as $suma => $sumaInfo)
            @if(! $sumaInfo["filtered"] && $sumaInfo["enabled"])
                <div class="suma">
                    <form action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('disableEngine')) }}" method="post">
                        <input type="hidden" name="suma" value="{{ $suma }}">
                        <input type="hidden" name="fokus" value="{{ $fokus }}">
                        <input type="hidden" name="url" value="{{ $url }}">
                        <button type="submit">{{ $sumaInfo["display-name"] }}</button>
                    </form>
                </div>
            @endif
            @endforeach
        </div>
        <div class="sumas disabled-engines">
            @foreach($sumas as $suma => $sumaInfo)
            @if( !$sumaInfo["filtered"] && !$sumaInfo["enabled"])
                <div class="suma">
                    <form action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('enableEngine')) }}" method="post">
                        <input type="hidden" name="suma" value="{{ $suma }}">
                        <input type="hidden" name="fokus" value="{{ $fokus }}">
                        <input type="hidden" name="url" value="{{ $url }}">
                        <button type="submit">{{ $sumaInfo["display-name"] }}</button>
                    </form>
                </div>
            @endif
            @endforeach
        </div>
        @if($filteredSumas)
        <h4>@lang('settings.disabledByFilter')</h4>
        <div class="sumas filtered-engines">
            @foreach($sumas as $suma => $sumaInfo)
            @if($sumaInfo["filtered"])
                <div class="suma">
                    {{ $sumaInfo["display-name"] }}
                </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>
    <div class="card-light">
        <h2>@lang('settings.header.3')</h2>
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
                        <option value="" @if(Cookie::get($fokus . "_setting_" . $filterInfo->{"get-parameter"}) === null)disabled selected @endif>@lang('metaGer.filter.noFilter')</option>
                        @foreach($filterInfo->values as $key => $value)
                        @if(!empty($key))
                        <option value="{{ $key }}" {{ Cookie::get($fokus . "_setting_" . $filterInfo->{"get-parameter"}) === $key ? "disabled selected" : "" }}>@lang($value)</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                @endif
                @endforeach
            </div>
            <button type="submit" class="btn btn-default">@lang('settings.save')</button>
        </form>
    </div>

    <div class="card-light" id="blacklist">
        <h2>@lang('settings.header.4')</h2>
        <p>@lang('settings.text.4')</p>
        <form id="newentry" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('newBlacklist', ["fokus" => $fokus, "url" => $url])) }}" method="post">
            <input type="hidden" name="url" value="{{ $url }}">
            <input type="hidden" name="fokus" value="{{ $fokus }}">
            <label for="blacklist">@lang('settings.address')</label>
            <div id="create">
                <input id="blacklist" name="blacklist" type="text" placeholder="example.com">
                <button type="submit" class="btn btn-default">@lang('settings.add')</button>
            </div>
        </form>
        @if(!empty($blacklist))
            <form id="deleteentry" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('deleteBlacklist', ["fokus" => $fokus, "url" => $url])) }}" method="post">
                <table>
                @foreach($blacklist as $key => $value)
                    <tr>
                        <td>
                            {{ $value }}
                        </td>
                        <td>
                            <button type="submit" name="cookieKey" value="{{ $key }}"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                @endforeach
                </table>
            </form>
            <form id="clearlist" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('clearBlacklist', ["fokus" => $fokus, "url" => $url])) }}" method="post">
                <button type="submit" name="clear" value="1">@lang('settings.clear')</button>
            </form>
        @endif
    </div>

    
        <div class="card-light">
            <h2>Weitere Einstellungen</h2>
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
                    <small>@lang('settings.darkmode-hint')</small>
                </div>
                @if(LaravelLocalization::getCurrentLocale() === "de")
                <div class="form-group">
                    <label for="zitate">Zitate</label>
                    <select name="zitate" id="zitate" class="form-control">
                        <option value="on" @if(Cookie::get($fokus . "_setting_zitate") === null)disabled selected @endif>Anzeigen</option>
                        <option value="off" {{ Cookie::get($fokus . "_setting_zitate") === "off" ? "disabled selected" : "" }}>Nicht Anzeigen</option>
                    </select>
                </div>
                @endif
                <button type="submit" class="btn btn-default">@lang('settings.save')</button>
            </form>
        </div>
    <div class="card-light" id="actions">
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

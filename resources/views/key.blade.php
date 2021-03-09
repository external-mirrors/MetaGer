@extends('layouts.subPages', ['page' => 'key'])

@section('title', $title )

@section('content')

<link type="text/css" rel="stylesheet" href="{{ mix('css/key.css') }}"/>
@if(Cookie::get('dark_mode') === "2")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/key-dark.css') }}"/>
@elseif(Cookie::get('dark_mode') === "1")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/key.css') }}"/>
@else
    <link type="text/css" rel="stylesheet" media="(prefers-color-scheme:dark)" href="{{ mix('css/key-dark.css') }}"/>
@endif
<div id="key-site">
    <div class="section">
        <h1>{{ trans('key.h1')}}</h1>
        <p>{!! trans('key.p1', ['url1' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/beitritt'), 'url2' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/spende')])!!}</p>
        <p>{{ trans('key.p2') }}</p>
        <p>{{ trans('key.p3') }}</p>
        <p>{{ trans('key.p4') }}</p>
        <p>{{ trans('key.p5') }}</p>
        <ol>
            <li>
                @lang ('key.li1')
                <div class="copyLink">
                    <input id="loadSettings" class="loadSettings" type="text" value="{{$cookieLink}}">
                    <button class="js-only btn btn-default" onclick="var copyText = document.getElementById('loadSettings');copyText.select();copyText.setSelectionRange(0, 99999);document.execCommand('copy');">@lang('settings.copy')</button>
                </div>
            </li>
            </br>
            <li>
                @lang('key.li2')
                <div class="copyLink">
                    <input id="searchString" class="loadSettings" type="text" value="{{route("resultpage", ["key" => $cookie]) . "&eingabe=%s"}}">
                    <button class="js-only btn btn-default" onclick="var copyText = document.getElementById('searchString');copyText.select();copyText.setSelectionRange(0, 99999);document.execCommand('copy');">@lang('settings.copy')</button>
                </div>
            </li>
        </ol>
    </div>
    @if(app('App\Models\Key')->canChange())
    <div class="section">
        <h3>@lang('key.custom.h3')</h3>
        <p>@lang('key.custom.p1')</p>
        <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('changeKeyOne')) }}">@lang('key.custom.a1')</a>
    </div>
    @endif
    <div class="section">
        <div id="form-wrapper">
            <form method="post">
                <input type="hidden" name="redirUrl" value="{{ Request::input('redirUrl', '') }}" />
                <input type="text" name="keyToSet" value="{{$cookie === 'enter_key_here' ? '' : $cookie}}" placeholder="@lang('key.placeholder1')" autofocus>
                <button type="submit" class="btn btn-success">OK</button>
            </form>
            @if($cookie !== 'enter_key_here')
            <form method="post" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('KeyController@removeKey', ['redirUrl' => url()->full()])) }}">
                <input type="hidden" name="redirUrl" value="{{ Request::input('redirUrl', '') }}" />
                <button type="submit" class="btn btn-default">@lang('key.removeKey')</button>
            </form>
            @endif
        </div>
        @if(isset($keyValid) && $keyValid === false)
        <p class="error">@lang('key.invalidKey')</p>
        @endif
        @if(Request::input('redirUrl', '') !== '')
        <div id="back-link"><a href="{{Request::input('redirUrl')}}">@lang('key.backLink')</a></div>
        @endif
    </div>
</div>
@endsection

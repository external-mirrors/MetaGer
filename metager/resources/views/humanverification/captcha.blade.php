@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1>@lang('captcha.1')</h1>
<p>@lang('captcha.2')</p>
<form method="post" action="{{ route('captcha_solve') }}" target="_top">
    <input type="hidden" name="id" value="{{ $id }}">
    <input type="hidden" name="url" value="{!! $url !!}">
    <input type="hidden" name="key" value="{{ $key }}">
    <input type="hidden" name="begin" value="{{ \microtime(true) }}">
    <input type="hidden" name="c" value="{{ $correct }}">
    <div id="captcha-container">
        <img src="{{ $image }}" />
        <div id="audio-captcha">
            <audio controls="controls" preload="none">
                <source src="{{$tts_url}}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
    </div>
    @if(Request::has('e'))
    <p id="error">{{ __('Fehler: Falsche Eingabe!') }}</p>
    @endif
    <p><input type="text" class="form-control" name="captcha" placeholder="@lang('captcha.3')" autofocus required></p>
    <div id="dnaa-container">
        <input type="checkbox" name="dnaa" id="dnaa" @if(Request::has("dnaa"))checked @endif>
        <label for="dnaa">@lang('captcha.4')</label>
    </div>
    <div id="submit-group">
        <button type="submit" class="btn btn-success" name="check">OK</button>
        <a href="{{ url()->full() }}">@lang('captcha.5')</a>
    </div>
</form>
@endsection
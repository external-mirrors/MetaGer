@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1>@lang('captcha.1')</h1>
<p>@lang('captcha.2')</p>
<form method="post" action="{{ route('captcha_solve') }}">
    <input type="hidden" name="url" value="{!! $url !!}">
    <input type="hidden" name="c" value="{{ $correct }}">
    <div id="captcha-container">
        <img src="{{ $image }}" />
    </div>
    @if(Request::has('e'))
    <p>
        <font color="red">{{ __('Fehler: Falsche Eingabe!') }}</font>
    </p>
    @endif
    <p><input type="text" class="form-control" name="captcha" placeholder="@lang('captcha.3')" autofocus required></p>
    <p><button type="submit" class="btn btn-success" name="check">OK</button></p>
</form>
@endsection
@extends('layouts.subPages', ['page' => 'key'])

@section('title', $title )

@section('content')
<div class="card-heavy" id="steps">
    <div class="step-one">Entfernen des aktuellen Schlüssels</div>
    <div class="step-two active">Generieren des neuen Schlüssels</div>
    <div class="step-three">Speichern des neuen Schlüssels</div>
</div>
<div class="card-heavy">
    <h1>@lang('keychange.h1')</h1>
    <p>@lang('keychange.p5', [
        'validUntil' => trim(str_replace(" später", "", $validUntil->longRelativeDiffForHumans(Carbon::now("Europe/London"), 1)))
    ])</p>
    <div class="copyLink">
        <input id="searchString" class="loadSettings" type="text" value="{{ url()->full() }}" readonly>
        <button class="js-only btn btn-default" onclick="var copyText = document.getElementById('searchString');copyText.select();copyText.setSelectionRange(0, 99999);document.execCommand('copy');">@lang('settings.copy')</button>
    </div>
    <br>
    <p>@lang('keychange.p6')</p>
    
    <form action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('changeKeyTwo')) }}" method="post">
        <input type="hidden" name="validUntil" value="{{ Request::input('validUntil', '') }}">
        <input type="hidden" name="password" value="{{ Request::input('password', '') }}">
        @if(Request::filled('newkey'))
        <p style="color: red">@lang('keychange.input1label')</p>
        @else
        <p><label for="mewkey">@lang('keychange.input1label')</label></p>
        @endif
        <input type="text" name="newkey" id="newkey" required placeholder="@lang('keychange.input1')" value="{{ Request::input('newkey', '') }}">
        <button type="submit" class="btn btn-default">@lang('keychange.button1')</button>
    </form>
</div>
@endsection

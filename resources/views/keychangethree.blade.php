@extends('layouts.subPages', ['page' => 'key'])

@section('title', $title )

@section('content')
<div class="card" id="steps">
    <div class="step-one">Entfernen des aktuellen Schlüssels</div>
    <div class="step-two">Generieren des neuen Schlüssels</div>
    <div class="step-three active">Speichern des neuen Schlüssels</div>
</div>
<div class="card">
    <h1>@lang('keychange.h1')</h1>
    <p>@lang('keychange.p7')</p>
    <p>@lang('keychange.p8')</p>
    <div class="copyLink">
        <input id="searchString" class="loadSettings" type="text" value="{{ Request::input('newkey', '') }}" readonly>
        <button class="js-only btn btn-default" onclick="var copyText = document.getElementById('searchString');copyText.select();copyText.setSelectionRange(0, 99999);document.execCommand('copy');">@lang('settings.copy')</button>
    </div>
</div>
@endsection
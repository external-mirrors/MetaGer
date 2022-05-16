@extends('layouts.subPages', ['page' => 'key'])

@section('title', $title )

@section('content')
<div class="card" id="steps">
    <div class="step-one active">Entfernen des aktuellen Schlüssels</div>
    <div class="step-two">Generieren des neuen Schlüssels</div>
    <div class="step-three">Speichern des neuen Schlüssels</div>
</div>
<div class="card">
    <h1>@lang('keychange.h1')</h1>
    <p>@lang('keychange.p1', ["key" => $key])</p>
    <p>@lang('keychange.p2', ["key" => $key])</p>
    <p>@lang('keychange.p3')</p>
    <p>@lang('keychange.p4')</p>
    <ol>
        <li>@lang('keychange.ol1.li1')</li>
        <li>@lang('keychange.ol1.li2')</li>
        <li>@lang('keychange.ol1.li3')</li>
        <li>@lang('keychange.ol1.li4')</li>
    </ol>
    <form action="" method="post">
        <button class="btn btn-default">@lang('keychange.a1')</button>
    </form>
</div>
@endsection
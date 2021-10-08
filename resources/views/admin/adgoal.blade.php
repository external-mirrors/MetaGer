@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="adgoal" , class="card">
    <h1>Adgoal Test Interface</h1>
    <p>Auf dieser Seite kannst du URLs eingeben, welche bei Adgoal auf Affliate Links überprüft werden sollen.</p>
    <p>Eine URL pro Zeile</p>
    <form action="{{route("adgoal-generate")}}" method="post">
        <textarea name="urls" id="urls" style="width: 100%;" rows="10">@if(!empty($urls)){{$urls}}@endif</textarea>
        <p style="padding-top: 16px;"><input type="submit" value="Generieren"></p>
    </form>
</div>
@if(!empty($answer))
<div class="card">
    <h1>Antwort</h1>
    <pre style="background-color: black; padding: 8px; color: white; border: 1px solid grey;">
    {{$answer}}
    </pre>
</div>
@endif
<div class="card">
    <h1>Generate URLs from search query</h1>
    <form action="{{route("adgoal-urls")}}" method="post">
        <input type="text" name="eingabe" placeholder="Sucheingabe">
        <input type="submit" value="OK">
    </form>
</div>
@endsection
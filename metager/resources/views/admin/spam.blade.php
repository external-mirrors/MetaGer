@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="block-requests">
    <form method="post">
        <input class="form-control" type="text" name="regexp" id="regexp" placeholder="Type in regexp to match queries...">
        <div id="ban-until">
            <label for="ban-time">Ban Until</label>
            <input type="date" name="ban-time" min="{{now()->format("Y-m-d")}}" id="ban-time">
        </div>
        <button type="submit" class="btn btn-default btn-sm">Sperren</button>
    </form>
</div>
<div id="bans">
    <h1>Current Bans</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <td>Regexp</td>
                <td>Banned until</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
            @foreach($bans as $ban)
            <tr>
                <td>{{ $ban["regexp"] }}</td>
                <td>{{ Carbon::createFromFormat("Y-m-d H:i:s", $ban["banned-until"])->format("d.m.Y H:i:s")}} ({{ Carbon::createFromFormat("Y-m-d H:i:s", $ban["banned-until"])->diffInDays(Carbon::now()) }} Days)</td>
                <td>
                    <form action="{{ url("admin/spam/deleteRegexp") }}" method="post">
                        <input type="hidden" name="regexp" value="{{ $ban["regexp"] }}">
                        <button type="submit">&#128465;</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div id="loadedbans">
    <h1>Loaded Bans</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <td>Regexp</td>
            </tr>
        </thead>
        <tbody>
            @foreach($loadedBans as $ban)
            <tr>
                <td>{{ $ban }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div id="head">
    <h1>Letzte Suchanfragen</h1>
    <button type="button" class="btn btn-success btn-sm">Alte Abfragen entfernen</button>
</div>
<input class="form-control" type="text" name="" id="check-against" placeholder="Match against...">
<table id="queries" class="table table-striped" data-latest="{{$latest->format("Y-m-d H:i:s")}}" data-api="{{ url('admin/spam/jsonQueries') }}">
    <thead>
        <tr>
            <td>Zeit</td>
            <td>Referer</td>
            <td>Abfragezeit</td>
            <td>Fokus</td>
            <td>Locale</td>
            <td>Abfrage</td>
        </tr>
    </thead>
    <tbody>
        @foreach($queries as $index => $query)
        <tr data-expiration="{{$query->expiration->timestamp}}" @if($index % 2 === 0) class="dark" @endif>
            <td>
                @if($query->time->isToday())
                {{$query->time->format("H:i:s")}}
                @else
                {{$query->time->format("d.m.Y H:i:s")}}
                @endif
            </td>
            <td class="referer" title="{{$query->referer}}">{{$query->referer}}</td>
            <td>{{$query->request_time}}</td>
            <td>{{$query->focus}}</td>
            <td>{{$query->locale}}</td>
            <td>{{$query->query}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

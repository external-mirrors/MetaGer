@extends('layouts.subPages')

@section('title', $title )

@section('content')
<style>

</style>
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
<table id="queries" class="table table-striped" data-latest="{{$latest->format("Y-m-d H:i:s")}}">
    <thead>
        <tr>
            <td>Zeit</td>
            <td>Referer</td>
            <td>Abfragezeit</td>
            <td>Fokus</td>
            <td>Interface</td>
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
            <td>{{$query->interface}}</td>
            <td>{{$query->query}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    var expirationTimer = window.setInterval(expireQueries, 1000);
    var expiration_enabled = true;

    var queryLoader = window.setInterval(loadQueries, 60000);

    document.getElementById("regexp").oninput = checkRegexp;
    document.getElementById("check-against").oninput = checkRegexp;
    window.addEventListener('load', function(){
        checkRegexp();
    });


    document.querySelector("#head > button").addEventListener('click', function() {
        let paused = this.classList.toggle("paused")
        if(paused){
            window.clearInterval(expirationTimer);
            this.classList.add("btn-danger");
            this.classList.remove("btn-success");
        }else{
            expirationTimer = window.setInterval(expireQueries, 1000);
            this.classList.add("btn-success");
            this.classList.remove("btn-danger");
        }
    });

    function expireQueries() {
        let now = Math.round(Date.now() / 1000);
        let queries = document.querySelectorAll("#queries tbody tr");
        queries.forEach((query, index) => {
            let expiration = query.dataset.expiration;
            if(now > expiration){
                query.remove();
            }
        });
    }

    function loadQueries() {
        let latest_update = document.getElementById("queries").dataset.latest
        let base_url = "{{ url('admin/spam/jsonQueries') }}";

        let url = base_url + "?since=" + encodeURI(latest_update);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                let latest = data.latest;
                document.getElementById("queries").dataset.latest = latest;
                let queries = data.queries;

                // Check if dark or not
                let current_queries = document.querySelectorAll("#queries tbody tr");
                let dark = current_queries.length > 0 ? !current_queries[current_queries.length -1].classList.contains("dark") : false;
                for(key in queries){
                    let tr_element = document.createElement("tr");                    
                    tr_element.dataset.expiration = queries[key].expiration_timestamp;
                    if(dark){
                        tr_element.classList.add("dark");
                        dark = false;
                    }else{
                        dark = true;
                    }

                    let time_element = document.createElement("td");
                    time_element.innerHTML = queries[key].time_string;
                    tr_element.append(time_element);

                    let referer_element = document.createElement("td");
                    referer_element.classList.add("referer");
                    referer_element.title = queries[key].referer;
                    referer_element.innerHTML = queries[key].referer;
                    tr_element.append(referer_element);

                    let request_time_element = document.createElement("td");
                    request_time_element.innerHTML = queries[key].request_time;
                    tr_element.append(request_time_element);

                    let focus_element = document.createElement("td");
                    focus_element.innerHTML = queries[key].focus;
                    tr_element.append(focus_element);

                    let interface_element = document.createElement("td");
                    interface_element.innerHTML = queries[key].interface;
                    tr_element.append(interface_element);

                    let query_element = document.createElement("td");
                    query_element.innerHTML = queries[key].query;
                    tr_element.append(query_element);

                    document.querySelector("#queries tbody").append(tr_element);
                }

                checkRegexp();
            });
    }

    function checkRegexp() {
        let banRegexps = [];

        document.querySelectorAll("#loadedbans tbody td").forEach((value, index) => {
            banRegexps.push(value.innerHTML);
        });
        if(document.getElementById("regexp").value.length > 0)
            banRegexps.push(document.getElementById("regexp").value);

        if(banRegexps.length == 0) return;

        let elements = document.querySelectorAll("#queries tbody tr");
        elements.forEach((query, index) => {
            let query_params = query.querySelectorAll("td");
            let query_string = query_params[query_params.length-1].innerHTML;

            let matches = false;

            banRegexps.forEach(regexp => {
                if(query_string.match(regexp)){
                    matches = true;
                    return false;
                }
            });
            if(matches){
                query.classList.add("matches");
            }else{
                query.classList.remove("matches");
            }
            
        });
    }


</script>
@endsection
